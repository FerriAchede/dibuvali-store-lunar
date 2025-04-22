<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Lunar\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(
            Product::with(['variant.prices', 'media', 'brand', 'productType'])->get()
        );
    }
    
    public function show($id)
    {
        return new ProductResource(
            Product::with(['variant.prices', 'media', 'brand', 'productType'])->findOrFail($id)
        );
    }

}
