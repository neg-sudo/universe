<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Search\SearchService;
use Illuminate\Http\Request;

class SearchApiController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $term = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', 'all');

        if (mb_strlen($term) < 2) {
            return response()->json(['data' => [], 'meta' => ['term' => $term, 'total' => 0]]);
        }

        $results = $search->search($term, 50);
        if (in_array($type, ['tool', 'article'], true)) {
            $results = array_values(array_filter($results, fn (array $result) => $result['type'] === $type));
        }

        return response()->json([
            'data' => $results,
            'meta' => ['term' => $term, 'total' => count($results)],
        ]);
    }
}
