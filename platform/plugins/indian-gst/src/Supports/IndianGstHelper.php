<?php

namespace SparroWave\IndianGst\Supports;

use Botble\Base\Facades\BaseHelper;

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
        $configured = (string) setting('indian_gst_default_company_state', '');
        $val = $configured ?: (string) get_ecommerce_setting('company_state_for_invoicing', get_ecommerce_setting('store_state', ''));
        if (! $configured && is_numeric($val) && is_plugin_active('location')
            && \Botble\Ecommerce\Facades\EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            $val = \Botble\Location\Models\State::query()->find($val)?->name ?: $val;
        }
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

        return $val;
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

    public static function getCompanyStateCode(): ?string
    {
        $val = self::getCompanyStateName();
        $states = self::getIndianStates();

        if (isset($states[$val])) {
            return $val;
        }

        $code = self::getStateCode($val);

        return $code;
    }

    public static function getCompanyGstin(): string
    {
        return (string) (setting('indian_gst_default_company_gstin')
            ?: get_ecommerce_setting('company_tax_id_for_invoicing', get_ecommerce_setting('store_vat_number', '')));
    }

    public static function getStateNameFromGstin(mixed $gstin): ?string
    {
        $gstin = strtoupper(trim((string) $gstin));
        if (! preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]$/', $gstin)) {
            return null;
        }

        return self::getIndianStates()[substr($gstin, 0, 2)] ?? null;
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
            '26' => 'Dadra and Nagar Haveli and Daman and Diu',
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
            // Ecommerce Location fields persist IDs. Resolve them before GST codes.
            if (function_exists('is_plugin_active') && is_plugin_active('location')
                && \Botble\Ecommerce\Facades\EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
                $locState = \Botble\Location\Models\State::query()->find($state);
                if ($locState) {
                    return preg_replace('/[^a-z0-9]/', '', strtolower($locState->name));
                }
            }
            $padded = str_pad((string)$state, 2, '0', STR_PAD_LEFT);
            $states = self::getIndianStates();
            if (isset($states[$padded])) {
                return preg_replace('/[^a-z0-9]/', '', strtolower($states[$padded]));
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

    /** Allocate a saved tax amount without losing an odd paisa. */
    public static function splitTax(float $amount): array
    {
        $paise = (int) round($amount * 100);
        $cgst = intdiv($paise + 1, 2);

        return [$cgst / 100, ($paise - $cgst) / 100];
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
