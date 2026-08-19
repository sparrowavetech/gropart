<?php

namespace SparroWave\IndianGst\Hooks;

use Botble\Marketplace\Models\Store;
use SparroWave\IndianGst\Supports\IndianGstHelper;

class IndianGstShippingListener
{
    public static function checkVendorShipping(array $shipping, array $data): array
    {
        if (! IndianGstHelper::isEnabled()) {
            return $shipping;
        }

        // If vendor has vendor_managed_shipping enabled, default system rates apply
        return $shipping;
    }
}
