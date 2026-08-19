<?php

namespace SparroWave\IndianGst\Supports;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IndianGstHelper
{
    public static function isEnabled(): bool
    {
        return (bool) setting('indian_gst_enabled', true);
    }

    public static function getTaxSlabTitle(float|int $taxRate): string
    {
        static $slabMap = null;
        if ($slabMap === null) {
            try {
                $slabMap = \Botble\Ecommerce\Models\Tax::query()
                    ->where('status', \Botble\Base\Enums\BaseStatusEnum::PUBLISHED)
                    ->pluck('title', 'percentage')
                    ->toArray();
            } catch (\Throwable $e) {
                $slabMap = [];
            }
        }

        $rateKey = (string) (float) $taxRate;
        return $slabMap[$rateKey] ?? ('GST@' . (float) $taxRate . '%');
    }

    public static function getFormattedCartTaxClassesName(): string
    {
        $taxTitles = [];
        try {
            foreach (\Botble\Ecommerce\Facades\Cart::instance('cart')->content() as $cartItem) {
                $taxRate = $cartItem->taxRate ?? 0;
                if ($taxRate > 0) {
                    $taxTitles[] = self::getTaxSlabTitle($taxRate);
                }
            }
        } catch (\Throwable $e) {
        }

        return implode(', ', array_unique($taxTitles));
    }

    public static function getCompanyState(): string
    {
        return self::getCompanyStateName();
    }

    public static function getCompanyStateName(): string
    {
        $val = (string) setting('indian_gst_default_company_state', '08');
        $states = self::getIndianStates();

        if (isset($states[$val])) {
            return $states[$val];
        }

        $normalized = self::normalizeState($val);
        foreach ($states as $code => $name) {
            if (self::normalizeState($name) === $normalized) {
                return $name;
            }
        }

        return $val ?: 'Rajasthan';
    }

    public static function getStateCode(mixed $state): ?string
    {
        if (empty($state)) {
            return null;
        }

        $states = self::getIndianStates();
        $str = (string) $state;

        if (isset($states[$str])) {
            return $str;
        }

        $normalized = self::normalizeState($str);
        foreach ($states as $code => $name) {
            if (self::normalizeState($name) === $normalized) {
                return $code;
            }
        }

        return null;
    }

    public static function getCompanyStateCode(): string
    {
        $val = (string) setting('indian_gst_default_company_state', '08');
        $states = self::getIndianStates();

        if (isset($states[$val])) {
            return $val;
        }

        $code = self::getStateCode($val);

        return $code ?: '08';
    }

    public static function getCompanyGstin(): string
    {
        return (string) setting('indian_gst_default_company_gstin', '08AUBPA5903F1Z5');
    }

    /**
     * Map of Indian State Codes to State Names
     */
    public static function getIndianStates(): array
    {
        return [
            '01' => 'Jammu and Kashmir',
            '02' => 'Himachal Pradesh',
            '03' => 'Punjab',
            '04' => 'Chandigarh',
            '05' => 'Uttarakhand',
            '06' => 'Haryana',
            '07' => 'Delhi',
            '08' => 'Rajasthan',
            '09' => 'Uttar Pradesh',
            '10' => 'Bihar',
            '11' => 'Sikkim',
            '12' => 'Arunachal Pradesh',
            '13' => 'Nagaland',
            '14' => 'Manipur',
            '15' => 'Mizoram',
            '16' => 'Tripura',
            '17' => 'Meghalaya',
            '18' => 'Assam',
            '19' => 'West Bengal',
            '20' => 'Jharkhand',
            '21' => 'Odisha',
            '22' => 'Chhattisgarh',
            '23' => 'Madhya Pradesh',
            '24' => 'Gujarat',
            '25' => 'Daman and Diu',
            '26' => 'Dadra and Nagar Haveli',
            '27' => 'Maharashtra',
            '28' => 'Andhra Pradesh',
            '29' => 'Karnataka',
            '30' => 'Goa',
            '31' => 'Lakshadweep',
            '32' => 'Kerala',
            '33' => 'Tamil Nadu',
            '34' => 'Puducherry',
            '35' => 'Andaman and Nicobar Islands',
            '36' => 'Telangana',
            '37' => 'Andhra Pradesh (New)',
            '38' => 'Ladakh',
        ];
    }

    /**
     * Standardize any state input (Name, Code, Abbreviation) to a clean lowercase identifier
     */
    public static function normalizeState(mixed $state): string
    {
        if (empty($state)) {
            return '';
        }

        if (is_numeric($state)) {
            // Might be a state ID or a 2-digit GST code
            $padded = str_pad((string)$state, 2, '0', STR_PAD_LEFT);
            $states = self::getIndianStates();
            if (isset($states[$padded])) {
                return strtolower(trim($states[$padded]));
            }
            if (is_plugin_active('location')) {
                $locState = \Botble\Location\Models\State::query()->find($state);
                if ($locState) {
                    return strtolower(trim($locState->name));
                }
            }
        }

        $str = strtolower(trim((string)$state));
        $str = preg_replace('/[^a-z0-9]/', '', $str);

        // Common abbreviations
        $abbrMap = [
            'rj' => 'rajasthan',
            'hr' => 'haryana',
            'dl' => 'delhi',
            'up' => 'uttarpradesh',
            'mp' => 'madhyapradesh',
            'gj' => 'gujarat',
            'mh' => 'maharashtra',
            'ka' => 'karnataka',
            'pb' => 'punjab',
            'ch' => 'chandigarh',
            'uk' => 'uttarakhand',
            'jh' => 'jharkhand',
            'wb' => 'westbengal',
            'tn' => 'tamilnadu',
            'kl' => 'kerala',
            'ts' => 'telangana',
            'ap' => 'andhrapradesh',
            'br' => 'bihar',
            'or' => 'odisha',
            'cg' => 'chhattisgarh',
            'hp' => 'himachalpradesh',
        ];

        return $abbrMap[$str] ?? $str;
    }

    /**
     * Determine if an order/invoice is Inter-State (IGST) or Intra-State (CGST+SGST)
     */
    public static function isInterState(mixed $sellerState, mixed $customerState): bool
    {
        $normSeller = self::normalizeState($sellerState ?: self::getCompanyState());
        $normCustomer = self::normalizeState($customerState);

        if (empty($normCustomer) || empty($normSeller)) {
            return false;
        }

        return $normSeller !== $normCustomer;
    }

    /**
     * Compute reverse tax from inclusive MRP
     */
    public static function calculateReverseTax(float $inclusivePrice, float $taxPercentage): array
    {
        if ($taxPercentage <= 0) {
            return [
                'base_price' => $inclusivePrice,
                'tax_amount' => 0.0,
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'igst_amount' => 0.0,
            ];
        }

        $basePrice = round($inclusivePrice / (1 + ($taxPercentage / 100)), 2);
        $taxAmount = round($inclusivePrice - $basePrice, 2);

        return [
            'base_price' => $basePrice,
            'tax_amount' => $taxAmount,
            'cgst_amount' => round($taxAmount / 2, 2),
            'sgst_amount' => round($taxAmount / 2, 2),
            'igst_amount' => $taxAmount,
        ];
    }
}
