<?php

namespace App\Modules\Search;

use App\Models\Article;
use App\Models\SearchLog;
use App\Models\Tool;
use Illuminate\Support\Collection;

/**
 * Universal search across UtilityWorld and KnowledgeWorld.
 *
 * Uses portable LOWER(...) LIKE matching so the same code runs on SQLite in
 * development and PostgreSQL in production. Relevance is scored in PHP; the
 * dataset is small enough that this stays fast and keeps ranking logic readable.
 * Swap this service for PostgreSQL full-text search when traffic requires it.
 */
final class SearchService
{
    public function search(string $term, int $limit = 40): array
    {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return [];
        }

        $like = '%'.mb_strtolower($term).'%';
        $needle = mb_strtolower($term);

        $tools = Tool::query()
            ->published()
            ->with('category')
            ->where(function ($query) use ($like) {
                $query->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(keywords) LIKE ?', [$like]);
            })
            ->limit(150)
            ->get()
            ->map(fn (Tool $tool) => $this->toolResult($tool, $needle));

        $articles = Article::query()
            ->published()
            ->with('category')
            ->where(function ($query) use ($like) {
                $query->whereRaw('LOWER(title) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(summary) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(tags) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(content) LIKE ?', [$like]);
            })
            ->limit(200)
            ->get()
            ->map(fn (Article $article) => $this->articleResult($article, $needle));

        return $tools->merge($articles)
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->all();
    }

    public function log(string $term, int $count): void
    {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return;
        }

        SearchLog::create([
            'term' => mb_substr($term, 0, 200),
            'result_count' => $count,
            'created_at' => now(),
        ]);
    }

    private function toolResult(Tool $tool, string $needle): array
    {
        $name = mb_strtolower($tool->name);
        $score = 0;
        if ($name === $needle) {
            $score += 300;
        } elseif (str_starts_with($name, $needle)) {
            $score += 180;
        } elseif (str_contains($name, $needle)) {
            $score += 120;
        }

        $keywords = mb_strtolower(implode(' ', $tool->keywords ?? []));
        if (str_contains($keywords, $needle)) {
            $score += 60;
        }
        if (str_contains(mb_strtolower($tool->description), $needle)) {
            $score += 30;
        }

        return [
            'type' => 'tool',
            'title' => $tool->name,
            'description' => $tool->description,
            'url' => route('tools.show', $tool->slug),
            'category' => $tool->category?->name,
            'type_label' => 'Tool',
            'icon' => $tool->icon,
            'score' => $score,
        ];
    }

    private function articleResult(Article $article, string $needle): array
    {
        $title = mb_strtolower($article->title);
        $score = 0;
        if ($title === $needle) {
            $score += 280;
        } elseif (str_starts_with($title, $needle)) {
            $score += 160;
        } elseif (str_contains($title, $needle)) {
            $score += 110;
        }

        $tags = mb_strtolower(implode(' ', $article->tags ?? []));
        if (str_contains($tags, $needle)) {
            $score += 55;
        }
        if (str_contains(mb_strtolower($article->summary), $needle)) {
            $score += 35;
        }
        if (str_contains(mb_strtolower($article->content), $needle)) {
            $score += 8;
        }

        return [
            'type' => 'article',
            'title' => $article->title,
            'description' => $this->snippet($article, $needle),
            'url' => route('knowledge.show', [$article->category->slug, $article->slug]),
            'category' => $article->category?->name,
            'type_label' => 'Article',
            'icon' => 'book-open',
            'score' => $score,
        ];
    }

    private function snippet(Article $article, string $needle): string
    {
        $text = preg_replace('/\s+/', ' ', $article->content) ?? '';
        $position = mb_stripos($text, $needle);
        if ($position === false || $position < 80) {
            return $article->summary;
        }

        $start = max(0, $position - 80);
        $snippet = mb_substr($text, $start, 220);
        if ($start > 0) {
            $snippet = '…'.$snippet;
        }

        return trim($snippet).'…';
    }
}
