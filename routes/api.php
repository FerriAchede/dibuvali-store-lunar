<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CheckoutController;
use Illuminate\Support\Facades\Route;

use Lunar\Facades\CartSession;
use Lunar\Stripe\Facades\Stripe as StripeFacade;
use Lunar\DataTransferObjects\Cart as CartData;

Route::middleware('frontend')->group(function () {
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


    Route::post('/checkout', [CheckoutController::class, 'checkout']);

    Route::post('/payment-intent', function () {
        $cart = CartSession::current();
        //$cartData = CartData::from($cart);

        if ($paymentIntent = $cartData->meta['payment_intent'] ?? false) {
            $intent = StripeFacade::fetchIntent($paymentIntent);
        } else {
            $intent = StripeFacade::createIntent($cart);
        }

        if ($intent->amount != $cart->total->value) {
            StripeFacade::syncIntent($cart);
        }

        return $intent;
    });
});
