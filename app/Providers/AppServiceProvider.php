<?php

namespace App\Providers;

use App\Modifiers\CustomShippingModifier;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\LunarPanel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        LunarPanel::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(\Lunar\Base\ShippingModifiers $shippingModifiers)
    {
        $shippingModifiers->add(
            CustomShippingModifier::class
        );
    }
}
