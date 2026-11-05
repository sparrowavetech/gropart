<?php

namespace SparroWave\SellerInvoicing\Supports;

use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Services\CommissionFeeResolver;

final class MarketplacePolicyReader
{
    public function forSeller(?Customer $seller = null): array
    {
        $percentage = (string) MarketplaceHelper::getSetting('fee_per_order', 0);
        $fixed = (string) MarketplaceHelper::getSetting('fee_per_order_fixed', 0);

        if ($seller) {
            $rate = app(CommissionFeeResolver::class)->forVendor($seller);
            $percentage = (string) $rate->percentage;
            $fixed = (string) $rate->fixed;
        }

        return [
            'source' => 'marketplace_settings',
            'mode' => MarketplaceHelper::getMode(),
            'charges_commission' => MarketplaceHelper::shouldChargeCommission(),
            'global_percentage' => (string) MarketplaceHelper::getSetting('fee_per_order', 0),
            'global_fixed' => (string) MarketplaceHelper::getSetting('fee_per_order_fixed', 0),
            'effective_seller_percentage' => $percentage,
            'effective_seller_fixed' => $fixed,
            'category_commission_enabled' => MarketplaceHelper::isCommissionCategoryFeeBasedEnabled(),
            'vendor_shipping_allowed' => MarketplaceHelper::allowVendorManageShipping(),
            'shipping_per_vendor' => MarketplaceHelper::isChargeShippingPerVendor(),
        ];
    }
}
