<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variant = $this->variants->first();
        $price = $variant?->prices->first();
        // $stock = $variant?->stocks->sum('quantity') ?? 0;

        $priceValue = $price->price->value ?? 0;
        $currency = $price->currency ?? null;

        $formattedPrice = $currency
            ? number_format(
                ($priceValue / (10 ** $currency->decimal_places)) * $currency->exchange_rate,
                $currency->decimal_places
            )
            : null;

        return [
            'id' => $this->id,
            'slug' => $this->urls->first()?->slug,
            'title' => $this->translateAttribute('name'),
            'description' => $this->translateAttribute('description'),
            'tags' => $this->tags->pluck('value'),
            'type' => $this->productType?->name,
            'brand' => $this->brand?->name,
            'stock' => $this->variants->first()?->stock ?? 0,
            'price' => $formattedPrice,
            'currency_code' => $currency?->code,
            'image' => $this->images->first()?->getUrl(),
            'images' => $this->images->map(fn($img) => $img->getUrl()),
        ];
    }
}
