<?php

namespace Botble\EcommerceWholesale\Services;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Models\CustomerGroup;

class ProductWholesaleBoxRenderer
{
    public function __construct(protected PricingRuleService $pricingRuleService)
    {
    }

    /**
     * Build the wholesale pricing box HTML for a product, honoring guest/customer
     * eligibility and group resolution. Returns an empty string when the box must
     * not be shown. Shared by the product-page hook and the fallback box endpoint.
     */
    public function render(Product $product, ?Customer $customer = null): string
    {
        if (! WholesaleHelper::isEnabled()) {
            return '';
        }

        $customer ??= auth('customer')->user();
        $isGuestEnabled = WholesaleHelper::isEnabledForGuests();

        if (! $customer && ! $isGuestEnabled) {
            return '';
        }

        if ($customer && ! WholesaleHelper::isWholesaleCustomer($customer) && ! $isGuestEnabled) {
            return '';
        }

        $groupIds = $this->resolveGroupIds($customer);

        if (empty($groupIds) && ! $isGuestEnabled) {
            return '';
        }

        $originalProduct = $product->is_variation ? $product->original_product : $product;

        // Convert the product's own currency price to the store default currency before applying wholesale discounts.
        $basePrice = $product->isOnSale() ? $product->front_sale_price : $product->getConvertedPrice();
        $pricingTiers = $this->pricingRuleService->getTieredPricesForCustomer($originalProduct, $groupIds, $basePrice);

        if ($pricingTiers->isEmpty()) {
            return '';
        }

        if (WholesaleHelper::showPricingTable()) {
            return view('plugins/ecommerce-wholesale::themes.partials.pricing-table', [
                'pricingTiers' => $pricingTiers,
                'basePrice' => $basePrice,
                'product' => $product,
            ])->render();
        }

        return view('plugins/ecommerce-wholesale::themes.partials.pricing-script', [
            'pricingTiers' => $pricingTiers,
            'basePrice' => $basePrice,
        ])->render();
    }

    /**
     * Resolve the customer-group ids whose pricing rules apply to the current viewer.
     * Wholesale customers use their published groups; guests use the configured
     * default group (or the highest-priority published group as a fallback).
     */
    public function resolveGroupIds(?Customer $customer): array
    {
        if ($customer && WholesaleHelper::isWholesaleCustomer($customer)) {
            return $customer->wholesaleGroups()
                ->where('status', CustomerGroupStatusEnum::PUBLISHED)
                ->pluck('ws_customer_groups.id')
                ->all();
        }

        if (WholesaleHelper::isEnabledForGuests()) {
            $defaultGroupId = WholesaleHelper::getDefaultGroupId();

            if ($defaultGroupId) {
                return [$defaultGroupId];
            }

            $firstGroup = CustomerGroup::query()
                ->where('status', CustomerGroupStatusEnum::PUBLISHED)
                ->orderBy('priority')
                ->first();

            return $firstGroup ? [$firstGroup->id] : [];
        }

        return [];
    }
}
