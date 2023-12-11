<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Ecommerce\Forms\Settings\CheckoutSettingForm;
use Botble\Ecommerce\Http\Requests\Settings\CheckoutSettingRequest;

class CheckoutSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/ecommerce::setting.checkout.name'));

        return CheckoutSettingForm::create()->renderForm();
    }

    public function update(CheckoutSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
