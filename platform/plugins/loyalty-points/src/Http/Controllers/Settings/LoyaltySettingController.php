<?php

namespace Botble\LoyaltyPoints\Http\Controllers\Settings;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LoyaltyPoints\Forms\Settings\LoyaltySettingForm;
use Botble\LoyaltyPoints\Http\Requests\Settings\LoyaltySettingRequest;
use Botble\Setting\Http\Controllers\Concerns\InteractsWithSettings;

class LoyaltySettingController extends BaseController
{
    use InteractsWithSettings;

    public function edit()
    {
        $this->pageTitle(trans('plugins/loyalty-points::loyalty-points.settings.title'));

        return LoyaltySettingForm::create()->renderForm();
    }

    public function update(LoyaltySettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
