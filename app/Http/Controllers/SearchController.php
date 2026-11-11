<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Modules\Search\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $term = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', 'all');
        $results = [];
        $counts = ['all' => 0, 'tool' => 0, 'article' => 0];

        if (mb_strlen($term) >= 2) {
            $results = $search->search($term);
            $search->log($term, count($results));

            $counts = [
                'all' => count($results),
                'tool' => count(array_filter($results, fn (array $result) => $result['type'] === 'tool')),
                'article' => count(array_filter($results, fn (array $result) => $result['type'] === 'article')),
            ];

            if (in_array($type, ['tool', 'article'], true)) {
                $results = array_values(array_filter($results, fn (array $result) => $result['type'] === $type));
            }
        }

        $suggestedTools = Tool::query()->published()->featured()->limit(6)->get();

        return view('search.index', compact('term', 'results', 'type', 'suggestedTools', 'counts'));
    }
}
