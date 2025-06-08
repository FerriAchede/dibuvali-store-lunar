<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Lunar\Stripe\Http\Controllers\WebhookController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/csrf-token', function () {
//     return response()->json(['csrf_token' => csrf_token()]);
// });


Route::post('/stripe/webhook', [WebhookController::class, '__invoke']);
