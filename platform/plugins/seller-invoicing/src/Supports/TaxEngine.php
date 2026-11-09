<?php

namespace SparroWave\SellerInvoicing\Supports;

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Store;

class TaxEngine
{
    public static function isIndianBillingMode(): bool
    {
        $taxMode = (string) setting('seller_invoicing_tax_mode', 'auto');
        if ($taxMode === 'dual_gst') {
            return true;
        }
        if ($taxMode === 'flat_tax' || $taxMode === 'exempt') {
            return false;
        }

        $rawIssuerCountry = (string) (get_ecommerce_setting('company_country_for_invoicing') ?: get_ecommerce_setting('store_country', 'IN'));
        $issuerCountry = LocationResolver::resolveCountry($rawIssuerCountry);

        return in_array(strtoupper(trim($rawIssuerCountry)), ['IN', 'IND', 'INDIA'])
            || in_array(strtoupper(trim($issuerCountry)), ['INDIA', 'IN']);
    }

    public static function isGstReportEnabled(): bool
    {
        return function_exists('is_plugin_active')
            && is_plugin_active('marketplace')
            && static::isIndianBillingMode();
    }

    public function resolve(Order $order, Revenue $revenue, ?Store $store = null): array
    {
        if (! $store || (int) $store->customer_id !== (int) $revenue->customer_id) {
            $revenueStore = Store::query()->where('customer_id', $revenue->customer_id)->first();
            if ($revenueStore) {
                $store = $revenueStore;
            } elseif (! $store) {
                $store = $order->store;
            }
        }

        $taxMode = (string) setting('seller_invoicing_tax_mode', 'auto');
        $feeTaxType = (string) setting('seller_invoicing_fee_tax_type', 'exclusive'); // 'exclusive' or 'inclusive'
        $defaultTaxRate = (float) (
            setting('marketplace_default_fee_tax') ?:
            setting('default_fee_tax') ?:
            setting('seller_invoicing_tax_rate', 18.0)
        );

        if ($revenue->fee_tax_rate && (float) $revenue->fee_tax_rate > 0) {
            $taxRate = (float) $revenue->fee_tax_rate;
        } else {
            $taxRate = $defaultTaxRate;
        }

        if ($taxMode === 'exempt') {
            $taxRate = 0.0;
        }

        $rawIssuerCountry = (string) (get_ecommerce_setting('company_country_for_invoicing') ?: get_ecommerce_setting('store_country', 'IN'));
        $rawIssuerState = (string) (get_ecommerce_setting('company_state_for_invoicing') ?: get_ecommerce_setting('store_state', ''));
        $issuerCountry = LocationResolver::resolveCountry($rawIssuerCountry);
        $issuerState = LocationResolver::resolveState($rawIssuerState, $rawIssuerCountry);

        $rawSellerCountry = (string) ($store?->tax_country ?: ($store?->country ?: ($revenue->customer?->vendorInfo?->tax_info['country'] ?? 'IN')));
        $rawSellerState = (string) ($store?->tax_state ?: ($store?->state ?: ($revenue->customer?->vendorInfo?->tax_info['state'] ?? '')));
        $sellerCountry = LocationResolver::resolveCountry($rawSellerCountry);
        $sellerState = LocationResolver::resolveState($rawSellerState, $rawSellerCountry);

        $isIndia = false;
        if ($taxMode === 'dual_gst') {
            $isIndia = true;
        } elseif ($taxMode === 'flat_tax' || $taxMode === 'exempt') {
            $isIndia = false;
        } else {
            // Auto detect
            $isIndia = in_array(strtoupper(trim($rawIssuerCountry)), ['IN', 'IND', 'INDIA'])
                || in_array(strtoupper(trim($issuerCountry)), ['INDIA', 'IN']);
        }

        $isIgst = false;
        $taxLabel = (string) setting('seller_invoicing_tax_label', $isIndia ? 'GST' : 'VAT');
        $taxIdLabel = (string) setting('seller_invoicing_tax_id_label', $isIndia ? 'GSTIN' : 'Tax ID');

        if ($isIndia && $taxRate > 0) {
            $normalizedIssuer = $this->normalizeState($issuerState);
            $normalizedSeller = $this->normalizeState($sellerState);

            if ($normalizedIssuer && $normalizedSeller && $normalizedIssuer !== $normalizedSeller) {
                $isIgst = true;
            } else {
                $isIgst = false;
            }
        }

        $sacCommission = (string) setting('seller_invoicing_sac_commission', '998311');
        $sacShipping = (string) setting('seller_invoicing_sac_shipping', '9965');

        $lines = [];

        $platformFee = (float) ($revenue->platform_fee ?? 0.0);
        $commissionFee = (float) ($revenue->commission_fee ?? 0.0);
        $shippingCost = (float) ($revenue->shipping_cost ?? 0.0);
        $totalFee = (float) $revenue->fee;

        // If revenue does not have shipping_cost recorded, check order shipping_amount
        if ($shippingCost <= 0 && (float) $order->shipping_amount > 0) {
            $vendorManagesShipping = (bool) (
                ($store && ($store->is_manage_shipping || $store->vendor_managed_shipping))
                && (bool) setting('marketplace_allow_vendor_manage_shipping', false)
            );
            if (! $vendorManagesShipping) {
                $shippingCost = (float) $order->shipping_amount;
            }
        }

        if ($platformFee > 0 || $commissionFee > 0) {
            if ($platformFee > 0) {
                $lines[] = $this->buildLine(
                    trans('plugins/seller-invoicing::seller-invoicing.platform_fee'),
                    $sacCommission,
                    $platformFee,
                    $taxRate,
                    $feeTaxType,
                    $isIndia,
                    $isIgst
                );
            }

            if ($commissionFee > 0) {
                $lines[] = $this->buildLine(
                    trans('plugins/seller-invoicing::seller-invoicing.commission_fee'),
                    $sacCommission,
                    $commissionFee,
                    $taxRate,
                    $feeTaxType,
                    $isIndia,
                    $isIgst
                );
            }
        } else {
            // Fallback for orders without separate platform/commission fees in revenue
            $configuredPlatformFeeRate = (float) (
                setting('marketplace_default_platform_fee') ?:
                setting('default_platform_fee') ?:
                setting('seller_invoicing_platform_fee', 8.0)
            );
            $configuredCommissionRate = (float) (
                setting('marketplace_fee_per_order') ?:
                setting('fee_per_order', 2.0)
            );
            $totalConfiguredRate = $configuredPlatformFeeRate + $configuredCommissionRate;

            $baseAmount = (float) (
                $revenue->sub_amount > 0
                    ? $revenue->sub_amount
                    : ($order->sub_total > 0 ? $order->sub_total : max(0, $order->amount - $order->shipping_amount))
            );

            $expectedCommissionFee = round($baseAmount * ($configuredCommissionRate / 100), 2);
            $expectedCombinedFee = round($baseAmount * ($totalConfiguredRate / 100), 2);

            if ($totalFee > 0 && abs($totalFee - $expectedCombinedFee) < 0.10) {
                // $totalFee already contains both platform fee and commission fee combined
                $calcPlatformFee = round($totalFee * ($configuredPlatformFeeRate / $totalConfiguredRate), 2);
                $calcCommissionFee = round($totalFee - $calcPlatformFee, 2);
            } elseif ($totalFee > 0 && abs($totalFee - $expectedCommissionFee) < 0.10) {
                // $totalFee was ONLY the commission fee (e.g. Botble standard 2%), platform fee was omitted
                $calcCommissionFee = $totalFee;
                $calcPlatformFee = round($baseAmount * ($configuredPlatformFeeRate / 100), 2);
            } else {
                if ($totalConfiguredRate > 0 && $configuredPlatformFeeRate > 0 && $totalFee > $expectedCommissionFee) {
                    $calcPlatformFee = round($totalFee * ($configuredPlatformFeeRate / $totalConfiguredRate), 2);
                    $calcCommissionFee = round($totalFee - $calcPlatformFee, 2);
                } else {
                    $calcCommissionFee = $totalFee > 0 ? $totalFee : $expectedCommissionFee;
                    $calcPlatformFee = round($baseAmount * ($configuredPlatformFeeRate / 100), 2);
                }
            }

            if ($calcPlatformFee > 0) {
                $lines[] = $this->buildLine(
                    trans('plugins/seller-invoicing::seller-invoicing.platform_fee'),
                    $sacCommission,
                    $calcPlatformFee,
                    $taxRate,
                    $feeTaxType,
                    $isIndia,
                    $isIgst
                );
            }

            if ($calcCommissionFee > 0) {
                $lines[] = $this->buildLine(
                    trans('plugins/seller-invoicing::seller-invoicing.commission_fee'),
                    $sacCommission,
                    $calcCommissionFee,
                    $taxRate,
                    $feeTaxType,
                    $isIndia,
                    $isIgst
                );
            }
        }

        if ($shippingCost > 0) {
            $lines[] = $this->buildShippingLine(
                trans('plugins/seller-invoicing::seller-invoicing.shipping_fee'),
                $sacShipping,
                $shippingCost,
                $taxRate,
                $isIndia,
                $isIgst
            );
        }

        $subtotal = 0.0;
        $cgstTotal = 0.0;
        $sgstTotal = 0.0;
        $igstTotal = 0.0;
        $taxTotal = 0.0;
        $grandTotal = 0.0;

        foreach ($lines as $line) {
            $subtotal += $line['taxable_amount'];
            $cgstTotal += $line['cgst_amount'];
            $sgstTotal += $line['sgst_amount'];
            $igstTotal += $line['igst_amount'];
            $taxTotal += $line['tax_amount'];
            $grandTotal += $line['total_amount'];
        }

        $subtotal = round($subtotal, 2);
        $cgstTotal = round($cgstTotal, 2);
        $sgstTotal = round($sgstTotal, 2);
        $igstTotal = round($igstTotal, 2);
        $taxTotal = round($taxTotal, 2);
        $grandTotal = round($grandTotal, 2);

        $rateLabel = '0%';
        if ($taxRate > 0) {
            if ($isIndia) {
                if ($isIgst) {
                    $rateLabel = sprintf('IGST @ %s%%', $taxRate + 0);
                } else {
                    $half = ($taxRate / 2) + 0;
                    $rateLabel = sprintf('CGST @ %s%% + SGST @ %s%%', $half, $half);
                }
            } else {
                $rateLabel = sprintf('%s @ %s%%', $taxLabel, $taxRate + 0);
            }
        }

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'tax_amount' => $taxTotal,
            'grand_total' => $grandTotal,
            'taxes' => [
                'is_india' => $isIndia,
                'is_igst' => $isIgst,
                'tax_label' => $taxLabel,
                'tax_id_label' => $taxIdLabel,
                'tax_rate' => $taxRate,
                'rate_label' => $rateLabel,
                'cgst_amount' => $cgstTotal,
                'sgst_amount' => $sgstTotal,
                'igst_amount' => $igstTotal,
                'place_of_supply' => $sellerState ?: ($issuerState ?: '-'),
            ],
        ];
    }

    protected function buildLine(
        string $name,
        string $sac,
        float $amount,
        float $taxRate,
        string $feeTaxType,
        bool $isIndia,
        bool $isIgst
    ): array {
        if ($taxRate <= 0) {
            return [
                'name' => $name,
                'sac_code' => $sac,
                'taxable_amount' => round($amount, 2),
                'tax_rate' => 0.0,
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'igst_amount' => 0.0,
                'tax_amount' => 0.0,
                'total_amount' => round($amount, 2),
            ];
        }

        if ($feeTaxType === 'inclusive') {
            $taxable = round(($amount * 100) / (100 + $taxRate), 2);
            $taxAmount = round($amount - $taxable, 2);
            $total = round($amount, 2);
        } else {
            // Exclusive: amount is pre-tax taxable value
            $taxable = round($amount, 2);
            $taxAmount = round(($taxable * $taxRate) / 100, 2);
            $total = round($taxable + $taxAmount, 2);
        }

        $cgst = 0.0;
        $sgst = 0.0;
        $igst = 0.0;

        if ($isIndia) {
            if ($isIgst) {
                $igst = $taxAmount;
            } else {
                $half = round($taxAmount / 2, 2);
                $cgst = $half;
                $sgst = round($taxAmount - $half, 2);
            }
        }

        return [
            'name' => $name,
            'sac_code' => $sac,
            'taxable_amount' => $taxable,
            'tax_rate' => $taxRate,
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => $igst,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
        ];
    }

    protected function buildShippingLine(
        string $name,
        string $sac,
        float $shippingCost,
        float $taxRate,
        bool $isIndia,
        bool $isIgst
    ): array {
        if ($taxRate <= 0) {
            return [
                'name' => $name,
                'sac_code' => $sac,
                'taxable_amount' => round($shippingCost, 2),
                'tax_rate' => 0.0,
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'igst_amount' => 0.0,
                'tax_amount' => 0.0,
                'total_amount' => round($shippingCost, 2),
            ];
        }

        // Customer/courier shipping cost is tax inclusive in India
        $taxable = round(($shippingCost * 100) / (100 + $taxRate), 2);
        $taxAmount = round($shippingCost - $taxable, 2);
        $total = round($shippingCost, 2);

        $cgst = 0.0;
        $sgst = 0.0;
        $igst = 0.0;

        if ($isIndia) {
            if ($isIgst) {
                $igst = $taxAmount;
            } else {
                $half = round($taxAmount / 2, 2);
                $cgst = $half;
                $sgst = round($taxAmount - $half, 2);
            }
        }

        return [
            'name' => $name,
            'sac_code' => $sac,
            'taxable_amount' => $taxable,
            'tax_rate' => $taxRate,
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => $igst,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
        ];
    }

    protected function normalizeState(string $state): string
    {
        $trimmed = strtolower(trim($state));
        $cleaned = preg_replace('/\b(state|province|pradesh|territory)\b/i', '', $trimmed);

        return preg_replace('/[^a-z0-9]/', '', (string) $cleaned);
    }
}
