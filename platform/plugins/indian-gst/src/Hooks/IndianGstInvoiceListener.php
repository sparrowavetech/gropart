<?php

namespace SparroWave\IndianGst\Hooks;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Media\Facades\RvMedia;
use Botble\Theme\Facades\Theme;
use SparroWave\IndianGst\Supports\IndianGstHelper;

class IndianGstInvoiceListener
{
    public static function handleInvoiceVariables(array $variables, Invoice $invoice): array
    {
        if (! IndianGstHelper::isEnabled()) {
            return $variables;
        }

        $order = $invoice->reference;
        $store = ($order instanceof Order && is_plugin_active('marketplace')) ? $order->store : null;

        // Determine Seller State and Customer State
        // Ecommerce's LocationTrait resolves stored Location IDs to names when that
        // integration is enabled. Never compare a raw Location ID with a GST code.
        $storeTaxId = (string) ($store?->gstin ?: $store?->tax_id ?: '');
        $sellerGstin = $store ? (preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]$/i', $storeTaxId) ? $storeTaxId : '') : IndianGstHelper::getCompanyGstin();
        $sellerState = IndianGstHelper::getStateNameFromGstin($sellerGstin)
            ?: ($store ? ($store->state_name ?: '') : IndianGstHelper::getCompanyState());
        $address = $order?->address;
        if ($order && \Botble\Ecommerce\Facades\EcommerceHelper::isBillingAddressEnabled() && $order->billingAddress?->id) {
            $address = $order->billingAddress;
        }
        $customerState = $address?->state_name ?: ($invoice->customer_state ?? '');

        $jurisdictionKnown = IndianGstHelper::normalizeState($sellerState) !== ''
            && IndianGstHelper::normalizeState($customerState) !== '';
        $isIgst = $jurisdictionKnown && IndianGstHelper::isInterState($sellerState, $customerState);

        $sellerName = $store ? $store->name : get_ecommerce_setting('company_name_for_invoicing', get_ecommerce_setting('store_name', ''));
        $sellerAddress = $store ? $store->full_address : get_ecommerce_setting('company_address_for_invoicing', get_ecommerce_setting('store_address', ''));
        $sellerPhone = $store ? $store->phone : get_ecommerce_setting('company_phone_for_invoicing', get_ecommerce_setting('store_phone', ''));
        $sellerEmail = $store ? $store->email : get_ecommerce_setting('company_email_for_invoicing', get_ecommerce_setting('store_email', ''));

        $sellerLogo = null;
        if ($store && $store->logo) {
            $sellerLogo = $store->logo;
        }

        $totalCgst = 0.0;
        $totalSgst = 0.0;
        $totalIgst = 0.0;

        if ($invoice->tax_amount > 0) {
            if ($isIgst) {
                $totalIgst = (float) $invoice->tax_amount;
            } else {
                [$totalCgst, $totalSgst] = IndianGstHelper::splitTax((float) $invoice->tax_amount);
            }
        }

        // Enrich items with HSN Code & SKU if missing
        $itemsArray = [];
        $totalQuantity = 0;
        $totalPrice = 0.0;
        $totalTax = (float) $invoice->tax_amount;

        foreach ($invoice->items as $item) {
            $itemArr = is_array($item) ? $item : $item->toArray();
            $options = $itemArr['options'] ?? [];

            $product = null;
            if (isset($itemArr['reference_id']) && $itemArr['reference_id']) {
                $product = Product::query()->find($itemArr['reference_id']);
            }

            if (empty($options['barcode']) && $product && ! empty($product->hsn_code)) {
                $options['barcode'] = $product->hsn_code;
            }
            if (empty($options['sku']) && $product && ! empty($product->sku)) {
                $options['sku'] = $product->sku;
            }

            $itemArr['options'] = $options;
            [$itemArr['cgst_amount'], $itemArr['sgst_amount']] = IndianGstHelper::splitTax((float) ($itemArr['tax_amount'] ?? 0));
            $itemsArray[] = $itemArr;

            $totalQuantity += (int) ($itemArr['qty'] ?? 1);
            $totalPrice += (float) ($itemArr['price'] ?? 0);
        }

        $mainLogo = get_ecommerce_setting('company_logo_for_invoicing')
            ?: (theme_option('logo_in_invoices') ?: (theme_option('logo') ?: Theme::getLogo()));
        $mainLogoPath = ($mainLogo && file_exists(RvMedia::getRealPath($mainLogo))) 
            ? RvMedia::getRealPath($mainLogo) 
            : null;

        // Vendor/Company Logo
        $companyLogoPath = ($sellerLogo && file_exists(RvMedia::getRealPath($sellerLogo)))
            ? RvMedia::getRealPath($sellerLogo)
            : $mainLogoPath;

        $invoiceData = $variables['invoice'] ?? $invoice->toArray();
        $invoiceData['items'] = $itemsArray;

        $variables['invoice'] = $invoiceData;
        $variables['isIgst'] = $isIgst;
        $variables['gst_jurisdiction_known'] = $jurisdictionKnown;
        $variables['company_name'] = $sellerName;
        $variables['company_address'] = $sellerAddress;
        $variables['company_tax_id'] = $sellerGstin;
        $variables['company_phone'] = $sellerPhone;
        $variables['company_email'] = $sellerEmail;
        $variables['company_logo_full_path'] = $companyLogoPath;
        $variables['logo_full_path'] = $mainLogoPath;
        $variables['seller_name'] = $sellerName;
        $variables['seller_address'] = $sellerAddress;
        $variables['seller_gstin'] = $sellerGstin;
        $variables['seller_phone'] = $sellerPhone;
        $variables['seller_email'] = $sellerEmail;
        $variables['seller_logo'] = $sellerLogo;
        $variables['seller_state'] = $sellerState;
        $variables['customer_state'] = $customerState;
        $variables['total_cgst'] = $totalCgst;
        $variables['total_sgst'] = $totalSgst;
        $variables['total_igst'] = $totalIgst;
        $variables['total_quantity'] = $totalQuantity;
        $variables['total_price'] = $totalPrice;
        $variables['total_tax'] = $totalTax;

        return $variables;
    }
}
