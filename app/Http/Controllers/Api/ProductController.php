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
            ->paginate(2);

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

        return new ProductDetailResource($product);
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
