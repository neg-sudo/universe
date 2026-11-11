<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Models\ToolCategory;

class CategoryApiController extends Controller
{
    public function index()
    {
        $toolCategories = ToolCategory::query()
            ->withCount(['publishedTools as tools_count'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ToolCategory $category) => [
                'slug' => $category->slug,
                'name' => $category->name,
                'description' => $category->description,
                'icon' => $category->icon,
                'count' => $category->tools_count,
                'url' => route('tools.index', ['category' => $category->slug]),
            ]);

        $articleCategories = ArticleCategory::query()
            ->withCount(['publishedArticles as articles_count'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ArticleCategory $category) => [
                'slug' => $category->slug,
                'name' => $category->name,
                'description' => $category->description,
                'icon' => $category->icon,
                'count' => $category->articles_count,
                'url' => route('knowledge.category', $category->slug),
            ]);

        return response()->json([
            'data' => [
                'tool_categories' => $toolCategories,
                'article_categories' => $articleCategories,
            ],
        ]);
    }
}
