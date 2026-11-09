<?php

namespace SparroWave\SellerInvoicing\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Setting\Http\Controllers\SettingController;
use Illuminate\Http\Request;
use SparroWave\SellerInvoicing\Forms\Settings\SellerInvoicingSettingForm;
use SparroWave\SellerInvoicing\Http\Requests\SellerInvoicingSettingRequest;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;

class SellerInvoicingSettingController extends SettingController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/marketplace::marketplace.name'))
            ->add(trans('plugins/seller-invoicing::seller-invoicing.library'), route('seller-invoicing.index'))
            ->add(trans('plugins/seller-invoicing::seller-invoicing.settings.title'), route('seller-invoicing.settings'));
    }

    public function edit()
    {
        $this->pageTitle(trans('plugins/seller-invoicing::seller-invoicing.settings.title'));

        return SellerInvoicingSettingForm::create()->renderForm();
    }

    public function update(
        SellerInvoicingSettingRequest $request,
        InvoiceGeneratorService $generator
    ) {
        $validated = $request->validated();
        $regenerate = (bool) $request->input('regenerate_existing_invoices', false);

        unset($validated['regenerate_existing_invoices']);

        if (isset($validated['seller_invoicing_platform_fee'])) {
            $fee = (string) (float) $validated['seller_invoicing_platform_fee'];
            \Botble\Setting\Facades\Setting::set('marketplace_default_platform_fee', $fee);
            \Botble\Setting\Facades\Setting::set('default_platform_fee', $fee);
            \Botble\Setting\Facades\Setting::set('seller_invoicing_platform_fee', $fee);
            \Botble\Setting\Facades\Setting::save();
        }

        $response = $this->performUpdate($validated);

        if ($regenerate) {
            $count = $generator->regenerateAllInvoices();

            return $response->setMessage(
                trans('plugins/seller-invoicing::seller-invoicing.settings.saved_and_regenerated', ['count' => $count])
            );
        }

        return $response;
    }

    public function regenerate(
        InvoiceGeneratorService $generator,
        BaseHttpResponse $response
    ) {
        $count = $generator->regenerateAllInvoices();

        return $response->setMessage(
            trans('plugins/seller-invoicing::seller-invoicing.settings.regenerated_success', ['count' => $count])
        );
    }

    public function regenerateBatch(
        Request $request,
        InvoiceGeneratorService $generator,
        BaseHttpResponse $response
    ) {
        $offset = (int) $request->input('offset', 0);
        $limit = (int) $request->input('limit', 25);
        $result = $generator->regenerateBatch($offset, $limit);

        return $response->setData($result);
    }
}
