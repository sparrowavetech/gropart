<?php

namespace SparroWave\SellerInvoicing\Supports;

final class DependencyCheck
{
    public static function ready(): bool
    {
        return function_exists('is_plugin_active')
            && is_plugin_active('ecommerce')
            && is_plugin_active('marketplace')
            && class_exists(\Botble\Ecommerce\Models\Order::class)
            && class_exists(\Botble\Marketplace\Models\Revenue::class);
    }
}
