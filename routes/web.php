<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ToolController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
Route::get('/tools/{slug}', [ToolController::class, 'show'])->name('tools.show');
Route::post('/tools/{slug}/visit', [ToolController::class, 'track'])
    ->middleware('throttle:30,1')
    ->name('tools.track');

Route::get('/knowledge', [ArticleController::class, 'index'])->name('knowledge.index');
Route::get('/knowledge/{category}', [ArticleController::class, 'category'])->name('knowledge.category');
Route::get('/knowledge/{category}/{slug}', [ArticleController::class, 'show'])->name('knowledge.show');

Route::get('/search', [SearchController::class, 'index'])->name('search');

foreach (['about', 'privacy', 'terms', 'accessibility'] as $page) {
    Route::get('/'.$page, [PageController::class, 'show'])
        ->defaults('page', $page)
        ->name('pages.'.$page);
}
