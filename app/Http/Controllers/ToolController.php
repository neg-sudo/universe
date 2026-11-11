<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\ToolCategory;
use App\Models\ToolUsageEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ToolController extends Controller
{
    public function index(Request $request)
    {
        $categories = ToolCategory::query()
            ->withCount(['publishedTools as tools_count'])
            ->orderBy('sort_order')
            ->get();

        $activeCategory = null;
        $query = Tool::query()->published()->with('category');

        if ($request->filled('category')) {
            $activeCategory = $categories->firstWhere('slug', $request->string('category')->toString());
            if ($activeCategory) {
                $query->where('tool_category_id', $activeCategory->id);
            }
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $like = '%'.mb_strtolower($search).'%';
            $query->where(function ($builder) use ($like) {
                $builder->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$like]);
            });
        }

        $tools = $query->orderBy('name')->paginate(24)->withQueryString();

        return view('tools.index', compact('categories', 'tools', 'activeCategory', 'search'));
    }

    public function show(string $slug)
    {
        $tool = Tool::query()->published()->with('category')->where('slug', $slug)->firstOrFail();

        $related = Tool::query()
            ->published()
            ->with('category')
            ->where('tool_category_id', $tool->tool_category_id)
            ->whereKeyNot($tool->getKey())
            ->orderBy('name')
            ->limit(4)
            ->get();

        if ($related->count() < 4) {
            $related = $related->merge(
                Tool::query()
                    ->published()
                    ->with('category')
                    ->whereKeyNot($tool->getKey())
                    ->whereNotIn('id', $related->pluck('id'))
                    ->orderByDesc('featured')
                    ->orderBy('name')
                    ->limit(4 - $related->count())
                    ->get()
            );
        }

        return view('tools.show', compact('tool', 'related'));
    }

    /** Record a privacy-friendly usage event once per session per 30 minutes. */
    public function track(Request $request, string $slug)
    {
        $tool = Tool::query()->published()->where('slug', $slug)->firstOrFail();
        $key = 'tool-visit:'.$tool->id.':'.hash('sha256', $request->session()->getId());

        if (! Cache::has($key)) {
            Cache::put($key, true, now()->addMinutes(30));
            $tool->increment('usage_count');
            ToolUsageEvent::create([
                'tool_id' => $tool->id,
                'session_hash' => hash('sha256', $request->session()->getId()),
                'created_at' => now(),
            ]);
        }

        return response()->noContent();
    }
}
