<?php
namespace SparroWave\EnquiryProducts\Support;

class StoreUrl
{
    public static function catalogue($store): ?string
    {
        if (! is_plugin_active('marketplace') || ! $store || ! $store->slugable?->key) {
            return null;
        }

        return route('public.enquiry-products.store', $store->slugable->key);
    }
}
