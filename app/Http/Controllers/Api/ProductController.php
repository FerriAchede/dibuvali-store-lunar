<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use Lunar\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 'published')
            ->with(['images', 'variant.prices', 'urls'])
            ->paginate(8);

        return ProductResource::collection($products);
    }

    public function show($slug)
    {
        $product = Product::whereHas('urls', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })
            ->with([
                'images',
                'urls',
                'tags',
                'productType',
                'brand',
                'variants.prices.currency',
            ])
            ->where('status', 'published')
            ->firstOrFail();

        $previous = Product::where('status', 'published')
            ->where('id', '<', $product->id)
            ->orderBy('id', 'desc')
            ->with('urls')
            ->first();

        $next = Product::where('status', 'published')
            ->where('id', '>', $product->id)
            ->orderBy('id', 'asc')
            ->with('urls')
            ->first();

        return response()->json([
            'data' => new ProductDetailResource($product),
            'previous' => $previous
                ? $previous->urls->first()?->slug
                : null,
            'next' => $next
                ? $next->urls->first()?->slug
                : null,
        ]);
    }

    public function newest()
    {
        $products = Product::where('status', 'published')
            ->with(['images', 'variant.prices.currency', 'urls'])
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        return ProductResource::collection($products);
    }
}
