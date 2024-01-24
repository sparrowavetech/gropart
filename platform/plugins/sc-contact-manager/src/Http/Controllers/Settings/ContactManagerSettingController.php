<?php

namespace Skillcraft\ContactManager\Http\Controllers\Settings;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Http\Controllers\SettingController;
use Skillcraft\ContactManager\Forms\Settings\ContactManagerSettingForm;
use Skillcraft\ContactManager\Http\Requests\Settings\ContactManagerSettingRequest;

class ContactManagerSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/sc-contact-manager::contact-manager.settings.title'));

        return ContactManagerSettingForm::create()->renderForm();
    }

    public function update(ContactManagerSettingRequest $request): BaseHttpResponse
    {
        return $this->performUpdate($request->validated());
    }
}
