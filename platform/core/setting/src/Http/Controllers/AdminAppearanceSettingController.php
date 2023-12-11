<?php

namespace Botble\Setting\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Forms\AdminAppearanceSettingForm;
use Botble\Setting\Http\Requests\AdminAppearanceRequest;
use Illuminate\Support\Arr;

class AdminAppearanceSettingController extends SettingController
{
    public function index()
    {
        $this->pageTitle(trans('core/setting::setting.admin_appearance.title'));

        return AdminAppearanceSettingForm::create()->renderForm();
    }

    public function update(AdminAppearanceRequest $request): BaseHttpResponse
    {
        $data = Arr::except($request->validated(), [
            'admin_locale_direction',
        ]);

        $isDemoModeEnabled = BaseHelper::hasDemoModeEnabled();

        $adminLocalDirection = $request->input('admin_locale_direction');
        if ($adminLocalDirection != setting('admin_locale_direction')) {
            session()->put('admin_locale_direction', $adminLocalDirection);
        }

        if (! $isDemoModeEnabled) {
            $data['admin_locale_direction'] = $adminLocalDirection;
        }

        return $this->performUpdate($data);
    }
}
