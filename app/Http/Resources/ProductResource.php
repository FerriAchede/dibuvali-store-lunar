<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = $this->variant?->prices->first()?->price;

        $value = $price->value;
        $currency = $price->currency;

        $formattedPrice = number_format(
            ($value / (10 ** $currency['decimal_places'])) * $currency['exchange_rate'],
            $currency['decimal_places']
        );
        $currencyCode = $currency['code'];


        $defaultImage = $this->images->first()?->getUrl();
        $hoverImage = $this->images->get(1)?->getUrl();


        return [
            'id' => $this->id,
            'slug' => $this->urls->first()?->slug,
            'title' => $this->translateAttribute('name'),
            'price' => $formattedPrice,
            'currency_code' => $currencyCode,
            'image' => $defaultImage,
            'hover_image' => $hoverImage,            
        ];
    }
}
