<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use Lunar\Models\Product;


Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    // Route::get('/newest', [ProductController::class, 'newest']);
    // Route::get('/brand/{id}', [ProductController::class, 'byBrand']);
    // Route::get('/type/{id}', [ProductController::class, 'byType']);
    // Route::get('/popular', [ProductController::class, 'popular']);
    // Route::get('/{slug}', [ProductController::class, 'show']);
});