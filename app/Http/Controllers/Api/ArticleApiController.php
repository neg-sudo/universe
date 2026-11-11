<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()->published()->with('category');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($builder) => $builder->where('slug', $request->string('category')));
        }
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->string('difficulty'));
        }
        if ($request->filled('q')) {
            $like = '%'.mb_strtolower((string) $request->query('q')).'%';
            $query->where(fn ($builder) => $builder
                ->whereRaw('LOWER(title) LIKE ?', [$like])
                ->orWhereRaw('LOWER(summary) LIKE ?', [$like]));
        }

        $articles = $query->orderByDesc('published_at')->paginate(20)->withQueryString();

        return response()->json([
            'data' => $articles->getCollection()->map(fn (Article $article) => $this->resource($article, false)),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $article = Article::query()->published()->with('category')->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $this->resource($article, true)]);
    }

    private function resource(Article $article, bool $withContent): array
    {
        $data = [
            'slug' => $article->slug,
            'title' => $article->title,
            'summary' => $article->summary,
            'category' => $article->category?->slug,
            'category_name' => $article->category?->name,
            'subcategory' => $article->subcategory,
            'difficulty' => $article->difficulty,
            'reading_time' => $article->reading_time,
            'word_count' => $article->word_count,
            'tags' => $article->tags ?? [],
            'source' => [
                'name' => $article->source_name,
                'url' => $article->source_url,
                'license' => $article->license,
                'retrieved_at' => $article->retrieved_at?->toDateString(),
            ],
            'url' => route('knowledge.show', [$article->category->slug, $article->slug]),
        ];

        if ($withContent) {
            $data['content'] = $article->content;
            $data['paragraphs'] = $article->paragraphs();
        }

        return $data;
    }
}
