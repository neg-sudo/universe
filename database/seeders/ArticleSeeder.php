<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $topics = require base_path('content/articles/topics.php');
        $dataFile = base_path('content/articles/data.json');

        if (! is_file($dataFile)) {
            $this->command?->warn('content/articles/data.json not found. Run: php scripts/fetch-wikipedia.php');
            return;
        }

        $articles = json_decode((string) file_get_contents($dataFile), true);
        if (! is_array($articles)) {
            $this->command?->error('content/articles/data.json is not valid JSON.');
            return;
        }

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

        $now = now();
        $rows = [];
        foreach ($articles as $article) {
            $categoryId = $categoryIds[$article['category']] ?? null;
            if (! $categoryId || empty($article['slug'])) {
                continue;
            }

            $content = trim((string) ($article['content'] ?? ''));
            $summary = trim((string) ($article['summary'] ?? ''));
            if ($summary === '') {
                $summary = mb_strimwidth($content, 0, 280, '…');
            }

            $rows[] = [
                'article_category_id' => $categoryId,
                'subcategory' => $article['subcategory'] ?? null,
                'subcategory_label' => $article['subcategory_label'] ?? null,
                'title' => $article['title'],
                'slug' => $article['slug'],
                'summary' => $summary,
                'content' => $content,
                'difficulty' => $article['difficulty'] ?? 'beginner',
                'status' => 'published',
                'language' => $article['language'] ?? 'en',
                'tags' => json_encode($article['tags'] ?? [], JSON_UNESCAPED_UNICODE),
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

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('articles')->upsert(
                $chunk,
                ['slug'],
                [
                    'article_category_id', 'subcategory', 'subcategory_label', 'title', 'summary',
                    'content', 'difficulty', 'status', 'language', 'tags', 'reading_time',
                    'word_count', 'source_name', 'source_url', 'license', 'retrieved_at',
                    'published_at', 'reviewed_at', 'updated_at',
                ],
            );
        }

        $this->command?->info('Imported '.count($rows).' articles from Wikipedia (CC BY-SA 4.0).');
    }
}
