<?php

namespace SparroWave\RazorpayEasyEmi;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('razorpay_emi_cached_offers');
        Schema::dropIfExists('razorpay_emi_vendor_requests');
        Schema::dropIfExists('razorpay_emi_product_configs');
    }
}

