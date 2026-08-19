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
        $sellerState = $store?->state ?: IndianGstHelper::getCompanyState();
        $customerState = $order?->address?->state_name ?: ($order?->address?->state ?: ($invoice->customer_state ?? ''));

        $isIgst = IndianGstHelper::isInterState($sellerState, $customerState);

        $sellerGstin = $store?->gstin ?: IndianGstHelper::getCompanyGstin();
        $sellerName = $store?->name ?: setting('ecommerce_store_name', 'Gropart Bharat');
        $sellerAddress = $store?->full_address ?: setting('ecommerce_store_address', 'Gol Chauraha, Jhala Manna Circle, Bus Stand, Badi Sadri, Rajasthan, Chittorgarh, 312403');
        $sellerPhone = $store?->phone ?: setting('ecommerce_store_phone', '9887972821');
        $sellerEmail = $store?->email ?: setting('ecommerce_store_email', 'contact@gropart.com');

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
                $totalCgst = round((float) $invoice->tax_amount / 2, 2);
                $totalSgst = round((float) $invoice->tax_amount / 2, 2);
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
            $itemsArray[] = $itemArr;

            $totalQuantity += (int) ($itemArr['qty'] ?? 1);
            $totalPrice += (float) ($itemArr['price'] ?? 0);
        }

        // Base Gropart Logo
        $mainLogo = theme_option('logo_in_invoices') ?: (theme_option('logo') ?: Theme::getLogo());
        $mainLogoPath = ($mainLogo && file_exists(RvMedia::getRealPath($mainLogo))) 
            ? RvMedia::getRealPath($mainLogo) 
            : RvMedia::getRealPath('logo-and-icons/admin-logo-1.png');

        // Vendor/Company Logo
        $companyLogoPath = ($sellerLogo && file_exists(RvMedia::getRealPath($sellerLogo)))
            ? RvMedia::getRealPath($sellerLogo)
            : $mainLogoPath;

        $invoiceData = $variables['invoice'] ?? $invoice->toArray();
        $invoiceData['items'] = $itemsArray;

        $variables['invoice'] = $invoiceData;
        $variables['isIgst'] = $isIgst;
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
