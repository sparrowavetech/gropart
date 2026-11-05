<?php

namespace SparroWave\SellerInvoicing\Supports;

use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Currency;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use SparroWave\SellerInvoicing\Models\OrderSnapshot;
use Illuminate\Support\Facades\DB;

final class SnapshotBuilder
{
    public function prepare(Order $order, string $captureSource = 'manual_review'): OrderSnapshot
    {
        return DB::transaction(function () use ($order, $captureSource): OrderSnapshot {
            $existing = OrderSnapshot::query()->where('order_id', $order->getKey())->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }

            $revenue = Revenue::query()
                ->where('order_id', $order->getKey())
                ->where('type', RevenueTypeEnum::ADD_AMOUNT)
                ->first();
            if (! $revenue) {
                throw new SnapshotPreparationException(trans('plugins/seller-invoicing::seller-invoicing.errors.no_revenue'));
            }

            // Resolve store gracefully: prefer store matching the revenue's customer ID
            $store = $order->store;
            if (! $store || (int) $store->customer_id !== (int) $revenue->customer_id) {
                $revenueStore = Store::query()
                    ->where('customer_id', $revenue->customer_id)
                    ->first();
                if ($revenueStore) {
                    $store = $revenueStore;
                }
            }

            $sellerCustomer = $revenue->customer ?: $store?->customer;
            $taxInfo = $sellerCustomer?->vendorInfo?->tax_info ?: ($store?->customer?->vendorInfo?->tax_info ?: []);

            $rawIssuerCountry = get_ecommerce_setting('company_country_for_invoicing') ?: get_ecommerce_setting('store_country', 'IN');
            $rawIssuerState = get_ecommerce_setting('company_state_for_invoicing') ?: get_ecommerce_setting('store_state', '');
            $rawIssuerCity = get_ecommerce_setting('company_city_for_invoicing') ?: get_ecommerce_setting('store_city', '');
            $issuerCountry = LocationResolver::resolveCountry($rawIssuerCountry);
            $issuerState = LocationResolver::resolveState($rawIssuerState, $rawIssuerCountry);
            $issuerCity = LocationResolver::resolveCity($rawIssuerCity);

            $rawRecipientCountry = $store?->tax_country ?: ($store?->country ?: ($taxInfo['country'] ?? 'IN'));
            $rawRecipientState = $store?->tax_state ?: ($store?->state ?: ($taxInfo['state'] ?? ''));
            $rawRecipientCity = $store?->city_name ?: ($store?->city ?: ($taxInfo['city'] ?? ''));
            $recipientCountry = LocationResolver::resolveCountry($rawRecipientCountry);
            $recipientState = LocationResolver::resolveState($rawRecipientState, $rawRecipientCountry);
            $recipientCity = LocationResolver::resolveCity($rawRecipientCity);

            $issuer = [
                'name' => get_ecommerce_setting('company_name_for_invoicing') ?: get_ecommerce_setting('store_name'),
                'address' => get_ecommerce_setting('company_address_for_invoicing') ?: get_ecommerce_setting('store_address'),
                'city' => $issuerCity,
                'country' => $issuerCountry,
                'state' => $issuerState,
                'raw_country' => (string) $rawIssuerCountry,
                'raw_state' => (string) $rawIssuerState,
                'raw_city' => (string) $rawIssuerCity,
                'tax_id' => get_ecommerce_setting('company_tax_id_for_invoicing') ?: get_ecommerce_setting('store_vat_number'),
            ];

            $recipient = [
                'name' => $taxInfo['business_name'] ?? ($store?->name ?: ($sellerCustomer?->name ?: 'Vendor #' . $revenue->customer_id)),
                'address' => $taxInfo['address'] ?? ($store?->address ?: ''),
                'city' => $recipientCity,
                'country' => $recipientCountry,
                'state' => $recipientState,
                'raw_country' => (string) $rawRecipientCountry,
                'raw_state' => (string) $rawRecipientState,
                'raw_city' => (string) $rawRecipientCity,
                'tax_id' => $taxInfo['tax_id'] ?? ($store?->tax_id ?: ''),
            ];

            $policy = app(MarketplacePolicyReader::class)->forSeller($sellerCustomer ?: $store?->customer);
            $currencyDigits = Currency::query()->where('title', $revenue->currency)->value('decimals');
            $currencyResolved = $currencyDigits !== null && (int) $currencyDigits >= 0 && (int) $currencyDigits <= 4;
            $digits = $currencyResolved ? (int) $currencyDigits : 2;
            $amounts = [
                'seller_merchandise_base' => (string) $revenue->sub_amount,
                'existing_marketplace_fee' => (string) $revenue->fee,
                'existing_seller_net' => (string) $revenue->amount,
                'customer_shipping_recorded' => (string) $order->shipping_amount,
                'customer_shipping_tax' => (string) ($order->shipping_tax_amount ?? 0),
            ];
            $reconciliation = $currencyResolved
                ? app(RevenueReconciler::class)->assess($amounts, $digits)
                : ['status' => 'needs_review', 'difference_minor' => null, 'reason' => 'currency_precision_unknown'];

            return OrderSnapshot::query()->create([
                'order_id' => $order->getKey(),
                'seller_id' => $revenue->customer_id,
                'revenue_id' => $revenue->getKey(),
                'currency' => (string) $revenue->currency,
                'currency_digits' => $digits,
                'amounts' => $amounts,
                'issuer' => $issuer,
                'recipient' => $recipient,
                'evidence' => [
                    'source' => 'mp_customer_revenues',
                    'shipping_source' => 'ec_orders.shipping_amount',
                    'shipping_allocation' => 'unresolved',
                    'fee_classification' => 'combined_unknown',
                    'tax_treatment' => 'unresolved',
                    'policy_capture_source' => $captureSource,
                    'policy_observed_at' => now()->toIso8601String(),
                    'policy' => $policy,
                    'revenue_reconciliation' => $reconciliation,
                    'currency_precision_source' => $currencyResolved ? 'ec_currencies.decimals' : 'unresolved',
                ],
                'status' => 'needs_review',
            ]);
        });
    }
}
