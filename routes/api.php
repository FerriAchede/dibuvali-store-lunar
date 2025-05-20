<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;


Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    // Route::get('/newest', [ProductController::class, 'newest']);
    // Route::get('/brand/{id}', [ProductController::class, 'byBrand']);
    // Route::get('/type/{id}', [ProductController::class, 'byType']);
    // Route::get('/popular', [ProductController::class, 'popular']);
    Route::get('/{slug}', [ProductController::class, 'show']);
});

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'show']);
    Route::post('/add', [CartController::class, 'add']);
    Route::put('/update/{id}', [CartController::class, 'update']);
    Route::delete('/remove/{id}', [CartController::class, 'remove']);
    Route::post('/clear', [CartController::class, 'clear']);
});

