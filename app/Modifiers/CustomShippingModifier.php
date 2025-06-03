<?php
namespace App\Modifiers;

use Lunar\Base\ShippingModifier;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\TaxClass;

class CustomShippingModifier extends ShippingModifier
{
    public function handle($cart, \Closure $next)
    {
        $taxClass = TaxClass::first();

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Envío estándar',
                description: 'Tarifa fija de envío 5€',
                identifier: 'fixed-rate',
                price: new Price(500, $cart->currency, 1),
                taxClass: $taxClass
            )
        );

        return $next($cart);
    }
}
