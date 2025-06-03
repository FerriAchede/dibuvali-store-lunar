<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Lunar\Facades\CartSession;
use App\Http\Controllers\Controller;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;

class CartController extends Controller
{
    public function show()
    {
        $cart = CartSession::current();
    
        if (!$cart) {
            $currency = Currency::where('default', true)->firstOrFail();
            $channel = Channel::where('default', true)->firstOrFail();
    
            $cart = Cart::create([
                'currency_id' => $currency->id,
                'channel_id' => $channel->id,
            ]);
    
            CartSession::use($cart);
        }
    
        $cart->calculate();
    
        return response()->json([
            'data' => [
                'items' => $cart->lines->map(function ($line) {
                    $variant = $line->purchasable;
                    $product = $variant->product;
    
                    return [
                        'id' => $line->id ?? null,
                        'variant_id' => $variant->id ?? null,
                        'title' => $product->translateAttribute('name') ?? null,
                        'slug' => $product->urls->first()->slug ?? null,
                        'description' => $product->translateAttribute('description') ?: $product->description ?? null,
                        'price' => optional($line->purchasable->prices->first())->price->formatted ?? '0.00',
                        'stock' => $variant->stock ?? 0,
                        'quantity' => $line->quantity ?? 0,
                        'image' => optional($variant->product->thumbnail)->original_url ?? null,
                    ];
                }),
                'totals' => [
                    'subTotal' => $cart->subTotal->formatted ?? 0,
                    'discount_total' => $cart->discount_total->formatted ?? 0,
                    'shipping_total' => $cart->shipping_total->formatted ?? 0,
                    'tax' => $cart->taxTotal->formatted ?? 0,
                    'total' => $cart->total->formatted ?? 0,
                ],
            ]
        ]);
    }
    

    public function add(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:lunar_product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);

        $cart = CartSession::current();

        if (!$cart) {
            $currency = Currency::where('default', true)->firstOrFail();
            $channel = Channel::where('default', true)->firstOrFail();
            
            $cart = Cart::create([
                'currency_id' => $currency->id,
                'channel_id' => $channel->id,
            ]);
            CartSession::use($cart);
        }

        CartSession::add($variant, $request->quantity);

        return response()->json(['message' => 'Product added to cart.']);
    }

    public function update(Request $request, $lineId)
    {
        $quantity = $request->input('quantity');
    
        $cart = CartSession::current();
        $line = $cart->lines->find($lineId);
    
        if (!$line || !$line->purchasable) {
            return response()->json(['message' => 'Producto no disponible o inválido'], 404);
        }
    
        CartSession::updateLine($lineId, $quantity);
    
        return response()->json(['message' => 'Carrito actualizado']);
    }

    public function remove(string $id)
    {
        try {
            CartSession::remove((int) $id);
    
            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el producto del carrito',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function clear()
    {
        CartSession::clear();

        return response()->json(['message' => 'Cart cleared.']);
    }
}
