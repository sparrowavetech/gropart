<?php
namespace SparroWave\EnquiryProducts\Tables;

class VendorEnquiryTable extends EnquiryTable
{
    use \Botble\Marketplace\Tables\Traits\ForVendor;

    public function hasPermission(string $permission): bool
    {
        $customer = auth('customer')->user();
        return is_plugin_active('marketplace') && $customer && $customer->is_vendor && (bool) $customer->store?->id;
    }
}
