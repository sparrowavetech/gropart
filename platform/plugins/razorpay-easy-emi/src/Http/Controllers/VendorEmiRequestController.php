<?php

namespace SparroWave\RazorpayEasyEmi\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Product;
use SparroWave\RazorpayEasyEmi\Enums\EmiRequestStatusEnum;
use SparroWave\RazorpayEasyEmi\Http\Requests\VendorEmiRequestSubmissionRequest;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiVendorRequest;
use SparroWave\RazorpayEasyEmi\Services\EmiNotificationService;
use SparroWave\RazorpayEasyEmi\Tables\VendorEmiRequestTable;

class VendorEmiRequestController extends BaseController
{
    public function __construct()
    {
        $this->breadcrumb()
            ->add(trans('plugins/marketplace::marketplace.dashboard'), route('marketplace.vendor.dashboard'))
            ->add(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor_menu.emi_requests'), route('marketplace.vendor.razorpay-easy-emi.requests.index'));
    }

    public function index(VendorEmiRequestTable $table)
    {
        $this->pageTitle(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor_menu.emi_requests'));

        return $table->renderTable();
    }

    public function store(VendorEmiRequestSubmissionRequest $request, BaseHttpResponse $response): BaseHttpResponse
    {
        $store = auth('customer')->user()?->store;

        if (! $store) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.unauthorized_store'));
        }

        $productId = (int) $request->input('product_id');

        // Check product ownership
        $product = Product::query()
            ->where('id', $productId)
            ->where('store_id', $store->id)
            ->first();

        if (! $product) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.product_not_found'));
        }

        // Check if pending request exists
        $hasPending = RazorpayEmiVendorRequest::query()
            ->where('product_id', $productId)
            ->where('status', EmiRequestStatusEnum::PENDING)
            ->exists();

        if ($hasPending) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.request_already_pending'));
        }

        $vendorRequest = RazorpayEmiVendorRequest::query()->create([
            'product_id' => $productId,
            'store_id' => $store->id,
            'requested_scheme' => $request->input('requested_scheme'),
            'subvention_willingness' => $request->input('subvention_willingness', 'seller'),
            'notes' => $request->input('notes'),
            'status' => EmiRequestStatusEnum::PENDING,
        ]);

        app(EmiNotificationService::class)->sendNewRequestNotificationToAdmin($vendorRequest);

        return $response
            ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.request_submitted_success'));
    }

    public function cancel(int|string $id, BaseHttpResponse $response): BaseHttpResponse
    {
        $store = auth('customer')->user()?->store;

        if (! $store) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.unauthorized_store'));
        }

        $vendorRequest = RazorpayEmiVendorRequest::query()
            ->where('id', $id)
            ->where('store_id', $store->id)
            ->first();

        if (! $vendorRequest) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.request_not_found'));
        }

        if (! $vendorRequest->status->isPending()) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.cannot_cancel_processed_request'));
        }

        $vendorRequest->delete();

        return $response
            ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.request_cancelled_success'));
    }
}

