<?php

use App\Http\Controllers\Api;
use Illuminate\Http\Request;

\Route::middleware('auth:api')->get('user', function (Request $request) {
    return $request->user();
});

Route::apiResources([
    'categories' => Api\CategoryController::class,
    'genres'     => Api\GenreController::class,
]);

