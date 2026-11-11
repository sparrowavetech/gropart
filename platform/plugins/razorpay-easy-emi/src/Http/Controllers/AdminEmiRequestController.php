<?php

namespace SparroWave\RazorpayEasyEmi\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SparroWave\RazorpayEasyEmi\Enums\EmiRequestStatusEnum;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiCachedOffer;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiProductConfig;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiVendorRequest;
use SparroWave\RazorpayEasyEmi\Tables\AdminEmiRequestTable;

class AdminEmiRequestController extends BaseController
{
    public function __construct()
    {
        $this->breadcrumb()
            ->add(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.admin_menu.marketplace'), route('marketplace.store.index'))
            ->add(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.admin_menu.emi_requests'), route('razorpay-easy-emi.requests.index'));
    }

    public function index(AdminEmiRequestTable $table)
    {
        $this->pageTitle(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.admin_menu.emi_requests'));

        return $table->renderTable();
    }

    public function review(int|string $id)
    {
        $requestItem = RazorpayEmiVendorRequest::query()
            ->with(['product', 'store', 'reviewer'])
            ->findOrFail($id);

        $this->pageTitle(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.review.page_title', ['id' => $id]));

        $this->breadcrumb()->add(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.review.breadcrumb'), route('razorpay-easy-emi.requests.review', $id));

        $cachedOffers = RazorpayEmiCachedOffer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('plugins/razorpay-easy-emi::admin.request-review', compact('requestItem', 'cachedOffers'));
    }

    public function approve(int|string $id, Request $request, BaseHttpResponse $response): BaseHttpResponse
    {
        $vendorRequest = RazorpayEmiVendorRequest::query()->findOrFail($id);

        if (! $vendorRequest->status->isPending()) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.request_already_processed'));
        }

        $offerId = $request->input('offer_id');
        $adminRemarks = $request->input('admin_remarks');

        $vendorRequest->status = EmiRequestStatusEnum::APPROVED;
        $vendorRequest->approved_offer_id = $offerId;
        $vendorRequest->admin_remarks = $adminRemarks;
        $vendorRequest->reviewed_by = auth()->id();
        $vendorRequest->reviewed_at = Carbon::now();
        $vendorRequest->save();

        // Automatically configure the product's EMI settings
        RazorpayEmiProductConfig::query()->updateOrCreate(
            ['product_id' => $vendorRequest->product_id],
            [
                'status' => 'enabled',
                'emi_type' => $vendorRequest->requested_scheme,
                'offer_mode' => ! empty($offerId) ? 'custom' : 'auto',
                'offer_id' => ! empty($offerId) ? json_encode([$offerId]) : null,
                'subvention_payer' => $vendorRequest->subvention_willingness,
            ]
        );

        return $response
            ->setNextUrl(route('razorpay-easy-emi.requests.index'))
            ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.request_approved_success'));
    }

    public function reject(int|string $id, Request $request, BaseHttpResponse $response): BaseHttpResponse
    {
        $vendorRequest = RazorpayEmiVendorRequest::query()->findOrFail($id);

        if (! $vendorRequest->status->isPending()) {
            return $response
                ->setError()
                ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.request_already_processed'));
        }

        $adminRemarks = $request->input('admin_remarks');

        $vendorRequest->status = EmiRequestStatusEnum::REJECTED;
        $vendorRequest->admin_remarks = $adminRemarks;
        $vendorRequest->reviewed_by = auth()->id();
        $vendorRequest->reviewed_at = Carbon::now();
        $vendorRequest->save();

        return $response
            ->setNextUrl(route('razorpay-easy-emi.requests.index'))
            ->setMessage(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.messages.request_rejected_success'));
    }

    public function destroy(int|string $id): DeleteResourceAction
    {
        $vendorRequest = RazorpayEmiVendorRequest::query()->findOrFail($id);

        return DeleteResourceAction::make($vendorRequest);
    }
}
