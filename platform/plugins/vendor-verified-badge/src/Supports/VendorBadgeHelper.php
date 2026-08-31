<?php

namespace SparroWave\VendorVerifiedBadge\Supports;

use Botble\Base\Facades\Html;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\VendorInfo;
use Botble\Media\Facades\RvMedia;
use Illuminate\Support\HtmlString;
use SparroWave\VendorVerifiedBadge\Enums\ShopTypeEnum;

class VendorBadgeHelper
{
    public static function getBadgeIconUrl(): string
    {
        $customIcon = setting('vendor_verified_badge_icon');
        if ($customIcon) {
            return RvMedia::getImageUrl($customIcon);
        }

        if (file_exists(public_path('vendor/core/plugins/vendor-verified-badge/images/verified.png'))) {
            return asset('vendor/core/plugins/vendor-verified-badge/images/verified.png');
        }

        if (file_exists(public_path('storage/stores/verified.png'))) {
            return asset('storage/stores/verified.png');
        }

        return asset('themes/gropart/imgs/verified.png');
    }

    public static function getTooltipText(): string
    {
        return (string) setting(
            'vendor_verified_tooltip_text',
            __('✓ Gropart Verified Seller — Business & GST Authenticated')
        );
    }

    public static function getApplicationUrl(): string
    {
        return (string) setting(
            'vendor_verified_application_url',
            'https://forms.gle/41n8QhD2hA7q3cE38'
        );
    }

    public static function isMenuGatingEnabled(): bool
    {
        return (bool) setting('vendor_verified_menu_gating_enabled', true);
    }

    public static function getCompletionThreshold(): int
    {
        return (int) setting('vendor_verified_completion_threshold', 80);
    }

    public static function isVerified(mixed $store): bool
    {
        if (! $store) {
            return false;
        }

        if (is_numeric($store)) {
            $store = Store::find($store);
        }

        return (bool) ($store->is_verified ?? false);
    }

    public static function getShopType(mixed $store): ?ShopTypeEnum
    {
        if (! $store) {
            return null;
        }

        if (is_numeric($store)) {
            $store = Store::find($store);
        }

        $category = $store->shop_category ?? null;

        if ($category instanceof ShopTypeEnum) {
            return $category;
        }

        if (is_string($category) && ShopTypeEnum::isValid($category)) {
            return (new ShopTypeEnum())->make($category);
        }

        return null;
    }

    public static function renderVerifiedIcon(mixed $store, array $options = []): string|HtmlString
    {
        if (! static::isVerified($store)) {
            return '';
        }

        $size = $options['size'] ?? 16;
        $class = $options['class'] ?? 'verified-store-badge';
        $iconUrl = static::getBadgeIconUrl();
        $tooltip = static::getTooltipText();

        $img = sprintf(
            '<img class="%s" src="%s" alt="%s" title="%s" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="%s" data-bs-original-title="%s" style="max-height:%spx; height:%spx; width:auto; vertical-align:middle; display:inline-block; margin-left:3px; cursor:pointer;" />',
            e($class),
            e($iconUrl),
            e(__('Verified')),
            e($tooltip),
            e($tooltip),
            e($tooltip),
            (int) $size,
            (int) $size
        );

        return new HtmlString($img);
    }

    public static function renderShopTypeBadge(mixed $store, array $options = []): string|HtmlString
    {
        $shopType = static::getShopType($store);
        if (! $shopType) {
            return '';
        }

        $colorClass = match ($shopType->getValue()) {
            ShopTypeEnum::MANUFACTURE => 'bg-info text-dark',
            ShopTypeEnum::RETAILER => 'bg-primary text-white',
            default => 'bg-warning text-dark',
        };

        $class = $options['badge_class'] ?? "vendor-shop-type-badge badge {$colorClass} ms-1";
        $label = $shopType->label();

        $badge = sprintf(
            '<small class="%s" style="vertical-align:middle; display:inline-block;">%s</small>',
            e($class),
            e($label)
        );

        return new HtmlString($badge);
    }

    public static function renderBadges(mixed $store, array $options = []): string|HtmlString
    {
        $verifiedHtml = static::renderVerifiedIcon($store, $options);
        $typeHtml = static::renderShopTypeBadge($store, $options);

        if (! $verifiedHtml && ! $typeHtml) {
            return '';
        }

        return new HtmlString($verifiedHtml . ' ' . $typeHtml);
    }

    public static function isVendorProfileComplete(int $customerId): array
    {
        $store = Store::where('customer_id', $customerId)->first();
        $vendorTaxData = VendorInfo::where('customer_id', $customerId)->value('tax_info');

        $data = [
            'status' => 0,
            'completePercentage' => 10,
            'storeVerified' => (bool) ($store?->is_verified ?? false),
        ];

        if (! $store) {
            return $data;
        }

        $percentageIncrease = 5;

        if ($vendorTaxData && is_array($vendorTaxData)) {
            $taxVendorSignature = $vendorTaxData['signature_image'] ?? '';
            $taxVendorBusinessName = $vendorTaxData['business_name'] ?? '';
            $taxVendorAddress = $vendorTaxData['address'] ?? '';
            $taxVendorTaxNumber = $vendorTaxData['tax_id'] ?? '';

            if ($taxVendorSignature && $taxVendorBusinessName && $taxVendorAddress && $taxVendorTaxNumber) {
                $data['completePercentage'] = 80;
                $data['status'] = 1;
            } else {
                if ($taxVendorSignature) {
                    $data['completePercentage'] += $percentageIncrease;
                }
                if ($taxVendorBusinessName) {
                    $data['completePercentage'] += $percentageIncrease;
                }
                if ($taxVendorAddress) {
                    $data['completePercentage'] += $percentageIncrease;
                }
                if ($taxVendorTaxNumber) {
                    $data['completePercentage'] += $percentageIncrease;
                }
            }
        }

        $vendorDataFields = [
            'email',
            'company',
            'address',
            'state',
            'city',
            'zip_code',
            'logo',
        ];

        foreach ($vendorDataFields as $field) {
            if (! empty($store->{$field})) {
                $data['completePercentage'] += $percentageIncrease;
            }
        }

        $threshold = static::getCompletionThreshold();
        if ($data['completePercentage'] >= $threshold) {
            $data['status'] = 1;
        }

        $data['completePercentage'] = min((int) $data['completePercentage'], 100);

        return $data;
    }
}
