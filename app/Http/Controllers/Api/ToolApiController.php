<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use Illuminate\Http\Request;

class ToolApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Tool::query()->published()->with('category');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($builder) => $builder->where('slug', $request->string('category')));
        }

        if ($request->filled('q')) {
            $like = '%'.mb_strtolower((string) $request->query('q')).'%';
            $query->where(fn ($builder) => $builder
                ->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(description) LIKE ?', [$like]));
        }

        $tools = $query->orderBy('name')->paginate(50)->withQueryString();

        return response()->json([
            'data' => $tools->getCollection()->map(fn (Tool $tool) => $this->resource($tool, false)),
            'meta' => [
                'current_page' => $tools->currentPage(),
                'last_page' => $tools->lastPage(),
                'per_page' => $tools->perPage(),
                'total' => $tools->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $tool = Tool::query()->published()->with('category')->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $this->resource($tool, true)]);
    }

    private function resource(Tool $tool, bool $withInputs): array
    {
        $data = [
            'slug' => $tool->slug,
            'name' => $tool->name,
            'description' => $tool->description,
            'category' => $tool->category?->slug,
            'category_name' => $tool->category?->name,
            'icon' => $tool->icon,
            'keywords' => $tool->keywords ?? [],
            'version' => $tool->version,
            'featured' => $tool->featured,
            'url' => route('tools.show', $tool->slug),
        ];

        if ($withInputs) {
            $data['inputs'] = $tool->inputs;
            $data['run_in_browser'] = true;
            $data['engine'] = asset('assets/js/tools.generated.js');
        }

        return $data;
    }
}
