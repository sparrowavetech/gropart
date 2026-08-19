<?php

namespace SparroWave\ProductFreeShipping\Http\Controllers\Settings;

use Botble\Base\Facades\PageTitle;
use Botble\Setting\Http\Controllers\SettingController;
use SparroWave\ProductFreeShipping\Forms\Settings\ProductFreeShippingSettingForm;
use SparroWave\ProductFreeShipping\Http\Requests\ProductFreeShippingSettingRequest;

class ProductFreeShippingSettingController extends SettingController
{
    public function edit()
    {
        PageTitle::setTitle(trans('plugins/product-free-shipping::product-free-shipping.settings.title'));

        return ProductFreeShippingSettingForm::create()->renderForm();
    }

    public function update(ProductFreeShippingSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
