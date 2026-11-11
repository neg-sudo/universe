<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tool;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $categories = ArticleCategory::query()
            ->withCount(['publishedArticles as articles_count'])
            ->orderBy('sort_order')
            ->get();

        $latest = Article::query()
            ->published()
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $total = Article::published()->count();

        return view('knowledge.index', compact('categories', 'latest', 'total'));
    }

    public function category(Request $request, string $categorySlug)
    {
        $category = ArticleCategory::query()
            ->withCount(['publishedArticles as articles_count'])
            ->where('slug', $categorySlug)
            ->firstOrFail();

        $subcategories = Article::query()
            ->published()
            ->where('article_category_id', $category->id)
            ->whereNotNull('subcategory')
            ->select('subcategory', 'subcategory_label')
            ->distinct()
            ->orderBy('subcategory_label')
            ->get();

        $query = Article::query()
            ->published()
            ->with('category')
            ->where('article_category_id', $category->id);

        $activeSubcategory = null;
        if ($request->filled('topic')) {
            $activeSubcategory = $request->string('topic')->toString();
            $query->where('subcategory', $activeSubcategory);
        }

        $articles = $query->orderBy('title')->paginate(15)->withQueryString();

        return view('knowledge.category', compact('category', 'subcategories', 'articles', 'activeSubcategory'));
    }

    public function show(string $categorySlug, string $slug)
    {
        $article = Article::query()
            ->published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless($article->category->slug === $categorySlug, 404);

        $related = Article::query()
            ->published()
            ->with('category')
            ->where('article_category_id', $article->article_category_id)
            ->whereKeyNot($article->getKey())
            ->orderByRaw('CASE WHEN subcategory = ? THEN 0 ELSE 1 END', [$article->subcategory])
            ->orderBy('title')
            ->limit(4)
            ->get();

        $toolCategory = match ($article->category->slug) {
            'science' => 'science',
            'mathematics' => 'mathematics',
            'technology', 'engineering' => 'developer',
            'economics' => 'finance',
            'health' => 'science',
            'languages' => 'text',
            'life-skills' => 'everyday',
            default => null,
        };

        $relatedTools = $toolCategory
            ? Tool::query()
                ->published()
                ->with('category')
                ->whereHas('category', fn ($query) => $query->where('slug', $toolCategory))
                ->orderByDesc('featured')
                ->orderBy('name')
                ->limit(4)
                ->get()
            : Tool::query()->published()->with('category')->featured()->limit(4)->get();

        return view('knowledge.show', compact('article', 'related', 'relatedTools'));
    }
}
