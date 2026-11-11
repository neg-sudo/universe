<?php

use App\Http\Controllers\Api\ArticleApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\SearchApiController;
use App\Http\Controllers\Api\ToolApiController;
use Illuminate\Support\Facades\Route;

Route::get('/tools', [ToolApiController::class, 'index']);
Route::get('/tools/{slug}', [ToolApiController::class, 'show']);

Route::get('/categories', [CategoryApiController::class, 'index']);

Route::get('/articles', [ArticleApiController::class, 'index']);
Route::get('/articles/{slug}', [ArticleApiController::class, 'show']);

Route::get('/search', [SearchApiController::class, 'index']);
