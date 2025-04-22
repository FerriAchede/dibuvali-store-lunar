<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use Lunar\Models\Product;

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    /*     Route::get('/published', [ProductController::class, 'published']);
    Route::get('/search', [ProductController::class, 'search']); */
    Route::get('/{id}', [ProductController::class, 'show']);
});
