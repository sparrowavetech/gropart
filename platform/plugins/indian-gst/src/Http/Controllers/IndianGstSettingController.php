<?php

namespace SparroWave\IndianGst\Http\Controllers;

use Botble\Base\Facades\PageTitle;
use Botble\Setting\Http\Controllers\SettingController;
use SparroWave\IndianGst\Forms\Settings\IndianGstSettingForm;
use SparroWave\IndianGst\Http\Requests\IndianGstSettingRequest;

class IndianGstSettingController extends SettingController
{
    public function edit()
    {
        PageTitle::setTitle(trans('plugins/indian-gst::indian-gst.settings.title'));

        return IndianGstSettingForm::create()->renderForm();
    }

    public function update(IndianGstSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
