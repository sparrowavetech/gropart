<?php

namespace SparroWave\RazorpayEasyEmi\Http\Controllers;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Http\Controllers\SettingController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SparroWave\RazorpayEasyEmi\Forms\RazorpayEasyEmiSettingForm;
use SparroWave\RazorpayEasyEmi\Http\Requests\RazorpayEasyEmiSettingRequest;
use SparroWave\RazorpayEasyEmi\Services\RazorpayOfferSyncService;

class RazorpayEasyEmiSettingController extends SettingController
{
    public function __construct(protected RazorpayOfferSyncService $syncService)
    {
    }

    public function edit(): View|string
    {
        PageTitle::setTitle(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.title'));

        $form = RazorpayEasyEmiSettingForm::create();
        $diagnostics = $this->syncService->getDiagnostics();

        return view('plugins/razorpay-easy-emi::admin.settings', compact('form', 'diagnostics'));
    }

    public function update(RazorpayEasyEmiSettingRequest $request): BaseHttpResponse
    {
        $data = $request->validated();
        $data['razorpay_easy_emi_enabled'] = $request->boolean('razorpay_easy_emi_enabled') ? '1' : '0';
        $data['razorpay_easy_emi_auto_detect_key'] = $request->boolean('razorpay_easy_emi_auto_detect_key') ? '1' : '0';
        $data['razorpay_easy_emi_enable_catalog_badge'] = $request->boolean('razorpay_easy_emi_enable_catalog_badge') ? '1' : '0';
        $data['razorpay_easy_emi_enable_cart_banner'] = $request->boolean('razorpay_easy_emi_enable_cart_banner') ? '1' : '0';
        $data['razorpay_easy_emi_auto_select_checkout'] = $request->boolean('razorpay_easy_emi_auto_select_checkout') ? '1' : '0';

        return $this->performUpdate($data);
    }

    public function syncOffers(Request $request, BaseHttpResponse $response): JsonResponse|BaseHttpResponse
    {
        $result = $this->syncService->syncOffers();

        if (! $result['success']) {
            return $response
                ->setError()
                ->setMessage($result['message']);
        }

        return $response
            ->setData($result)
            ->setMessage($result['message']);
    }
}

