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
        $products = Product::where('status', 'published')->with(['images', 'variant.prices', 'urls'])->get();
        return ProductResource::collection($products);
    }


}