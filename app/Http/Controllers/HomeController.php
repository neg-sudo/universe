<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tool;
use App\Models\ToolCategory;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'tools' => Tool::published()->count(),
            'articles' => Article::published()->count(),
            'tool_categories' => ToolCategory::whereHas('publishedTools')->count(),
            'knowledge_categories' => ArticleCategory::whereHas('publishedArticles')->count(),
        ];

        $featuredTools = Tool::published()->featured()->orderBy('name')->limit(8)->get();
        $popularTools = Tool::published()
            ->orderByDesc('usage_count')
            ->orderBy('name')
            ->limit(8)
            ->get();

        $toolCategories = ToolCategory::query()
            ->withCount(['publishedTools as tools_count'])
            ->whereHas('publishedTools')
            ->orderBy('sort_order')
            ->get();

        $knowledgeCategories = ArticleCategory::query()
            ->withCount(['publishedArticles as articles_count'])
            ->whereHas('publishedArticles')
            ->orderBy('sort_order')
            ->get();

        $latestArticles = Article::published()
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('home', compact(
            'stats',
            'featuredTools',
            'popularTools',
            'toolCategories',
            'knowledgeCategories',
            'latestArticles',
        ));
    }
}
