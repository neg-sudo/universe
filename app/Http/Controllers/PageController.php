<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function show(string $page)
    {
        $pages = require base_path('content/pages.php');

        if (! isset($pages[$page])) {
            abort(404);
        }

        return view('pages.show', ['page' => $pages[$page]]);
    }
}
