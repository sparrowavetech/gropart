<?php

namespace SparroWave\RazorpayEasyEmi\Services;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiVendorRequest;
use Throwable;

class EmiNotificationService
{
    public function sendNewRequestNotificationToAdmin(RazorpayEmiVendorRequest $request): bool
    {
        try {
            $request->loadMissing(['product', 'store.customer']);

            $product = $request->product;
            $store = $request->store;
            $customer = $store?->customer;

            $productPrice = $product ? (format_price($product->front_sale_price_with_taxes ?: $product->price)) : 'N/A';

            EmailHandler::setModule('razorpay-easy-emi')
                ->setVariableValues([
                    'store_name' => $store?->name ?? 'Unknown Store',
                    'vendor_name' => $customer?->name ?? 'Vendor',
                    'product_name' => $product?->name ?? 'Product #' . $request->product_id,
                    'product_price' => $productPrice,
                    'requested_scheme' => $request->requested_scheme ? $request->requested_scheme->label() : 'Standard',
                    'subvention_willingness' => $request->subvention_willingness ? $request->subvention_willingness->label() : 'Seller',
                    'notes' => $request->notes ?: '',
                    'request_url' => route('razorpay-easy-emi.requests.review', $request->id),
                    'created_at' => $request->created_at ? $request->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A'),
                ])
                ->sendUsingTemplate('admin_new_emi_request');

            return true;
        } catch (Throwable $e) {
            BaseHelper::logError($e);

            return false;
        }
    }

    public function sendStatusUpdateNotificationToVendor(RazorpayEmiVendorRequest $request): bool
    {
        try {
            $request->loadMissing(['product', 'store.customer']);

            $product = $request->product;
            $store = $request->store;
            $customer = $store?->customer;

            $vendorEmail = $customer?->email ?: $store?->email;

            if (empty($vendorEmail)) {
                return false;
            }

            $statusLabel = $request->status ? $request->status->label() : ucfirst($request->status->getValue());

            EmailHandler::setModule('razorpay-easy-emi')
                ->setVariableValues([
                    'vendor_name' => $customer?->name ?? 'Vendor',
                    'store_name' => $store?->name ?? 'Your Store',
                    'product_name' => $product?->name ?? 'Product #' . $request->product_id,
                    'requested_scheme' => $request->requested_scheme ? $request->requested_scheme->label() : 'Standard',
                    'status' => $statusLabel,
                    'admin_remarks' => $request->admin_remarks ?: '',
                    'approved_offer_id' => $request->approved_offer_id ?: '',
                    'product_edit_url' => route('marketplace.vendor.products.edit', $request->product_id),
                    'reviewed_at' => $request->reviewed_at ? $request->reviewed_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A'),
                ])
                ->sendUsingTemplate('vendor_emi_request_status_updated', $vendorEmail);

            return true;
        } catch (Throwable $e) {
            BaseHelper::logError($e);

            return false;
        }
    }
}

