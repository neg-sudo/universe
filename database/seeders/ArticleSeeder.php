<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    /**
     * Articles are stored as sharded JSONL so the seeder can stream a 10,000
     * article catalogue without loading it all into memory (Render's free
     * instance has 512 MB RAM). Set SEED_ARTICLE_LIMIT to cap a run.
     */
    public function run(): void
    {
        $topics = require base_path('content/articles/topics.php');
        $dataDir = base_path('content/articles/data');
        $manifestFile = $dataDir . '/manifest.json';
        $legacyFile = base_path('content/articles/data.json');
        $limit = max(0, (int) env('SEED_ARTICLE_LIMIT', 0));

        $categoryIds = [];
        $sort = 0;
        foreach ($topics as $slug => $category) {
            $sort += 10;
            $model = ArticleCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $category['name'],
                    'description' => $category['description'] ?? null,
                    'icon' => $category['icon'] ?? null,
                    'sort_order' => $sort,
                ],
            );
            $categoryIds[$slug] = $model->id;
        }

        $shards = [];
        $expected = null;

        if (is_file($manifestFile)) {
            $manifest = json_decode((string) file_get_contents($manifestFile), true);
            if (is_array($manifest)) {
                $expected = isset($manifest['count']) ? (int) $manifest['count'] : null;
                foreach ($manifest['shards'] ?? [] as $shard) {
                    if (!empty($shard['file'])) {
                        $shards[] = $dataDir . '/' . $shard['file'];
                    }
                }
            }
        }

        if ($shards === []) {
            $shards = glob($dataDir . '/part-*.jsonl') ?: [];
            sort($shards);
        }

        if ($shards === []) {
            if (!is_file($legacyFile)) {
                $this->command?->warn('No article shards found. Run: php scripts/harvest-wikipedia.php');
                return;
            }

            $articles = json_decode((string) file_get_contents($legacyFile), true);
            if (!is_array($articles)) {
                $this->command?->error('content/articles/data.json is not valid JSON.');
                return;
            }

            $this->importRecords($articles, $categoryIds, $limit);
            return;
        }

        $now = now();
        $buffer = [];
        $imported = 0;

        foreach ($shards as $shard) {
            if (!is_file($shard)) {
                $this->command?->warn('Missing shard: ' . $shard);
                continue;
            }

            $handle = fopen($shard, 'rb');
            if ($handle === false) {
                $this->command?->warn('Cannot read shard: ' . $shard);
                continue;
            }

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $article = json_decode($line, true);
                if (!is_array($article)) {
                    continue;
                }

                $row = $this->row($article, $categoryIds, $now);
                if ($row === null) {
                    continue;
                }

                $buffer[] = $row;
                $imported++;

                if (count($buffer) >= 100) {
                    $this->upsert($buffer);
                    $buffer = [];
                }

                if ($limit > 0 && $imported >= $limit) {
                    break 2;
                }
            }

            fclose($handle);
        }

        if ($buffer !== []) {
            $this->upsert($buffer);
        }

        $this->command?->info('Imported ' . $imported . ' articles from Wikipedia (CC BY-SA 4.0).');

        if ($expected !== null && $limit === 0 && $imported !== $expected) {
            $this->command?->warn('Manifest expected ' . $expected . ' articles but ' . $imported . ' were imported.');
        }
    }

    /**
     * Import a decoded JSON array (legacy data.json fallback).
     *
     * @param array<int,array<string,mixed>> $articles
     * @param array<string,int> $categoryIds
     */
    private function importRecords(array $articles, array $categoryIds, int $limit): void
    {
        $now = now();
        $rows = [];

        foreach ($articles as $article) {
            if ($limit > 0 && count($rows) >= $limit) {
                break;
            }
            $row = $this->row($article, $categoryIds, $now);
            if ($row !== null) {
                $rows[] = $row;
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            $this->upsert($chunk);
        }

        $this->command?->info('Imported ' . count($rows) . ' articles from Wikipedia (CC BY-SA 4.0).');
    }

    /**
     * Convert an article record to a database row.
     *
     * @param array<string,mixed> $article
     * @param array<string,int> $categoryIds
     * @return array<string,mixed>|null
     */
    private function row(array $article, array $categoryIds, $now): ?array
    {
        $categoryId = $categoryIds[$article['category'] ?? ''] ?? null;
        $slug = trim((string) ($article['slug'] ?? ''));
        if (! $categoryId || $slug === '') {
            return null;
        }

        $content = trim((string) ($article['content'] ?? ''));
        $summary = trim((string) ($article['summary'] ?? ''));
        if ($summary === '') {
            $summary = mb_strimwidth($content, 0, 280, '…');
        }

        $tags = $article['tags'] ?? [];
        if (! is_array($tags)) {
            $tags = [];
        }

        return [
            'article_category_id' => $categoryId,
            'subcategory' => $article['subcategory'] ?? null,
            'subcategory_label' => $article['subcategory_label'] ?? null,
            'title' => $article['title'] ?? $slug,
            'slug' => $slug,
            'summary' => $summary,
            'content' => $content,
            'difficulty' => $article['difficulty'] ?? 'beginner',
            'status' => 'published',
            'language' => $article['language'] ?? 'en',
            'tags' => json_encode($tags, JSON_UNESCAPED_UNICODE),
            'reading_time' => (int) ($article['reading_time'] ?? 1),
            'word_count' => (int) ($article['word_count'] ?? str_word_count($content)),
            'source_name' => $article['source_name'] ?? 'Wikipedia',
            'source_url' => $article['source_url'] ?? null,
            'license' => $article['license'] ?? 'CC BY-SA 4.0',
            'retrieved_at' => $article['retrieved_at'] ?? $now->toDateString(),
            'published_at' => $now,
            'reviewed_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     */
    private function upsert(array $rows): void
    {
        DB::table('articles')->upsert(
            $rows,
            ['slug'],
            [
                'article_category_id', 'subcategory', 'subcategory_label', 'title', 'summary',
                'content', 'difficulty', 'status', 'language', 'tags', 'reading_time',
                'word_count', 'source_name', 'source_url', 'license', 'retrieved_at',
                'published_at', 'reviewed_at', 'updated_at',
            ],
        );
    }
}
