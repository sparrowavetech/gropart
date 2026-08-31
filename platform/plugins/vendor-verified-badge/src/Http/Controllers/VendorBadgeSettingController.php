<?php

namespace SparroWave\VendorVerifiedBadge\Http\Controllers;

use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Marketplace\Models\Store;
use Botble\Setting\Http\Controllers\SettingController;
use Illuminate\Http\Request;
use SparroWave\VendorVerifiedBadge\Forms\Settings\VendorBadgeSettingForm;
use SparroWave\VendorVerifiedBadge\Http\Requests\VendorBadgeSettingRequest;

class VendorBadgeSettingController extends SettingController
{
    public function edit()
    {
        PageTitle::setTitle(trans('plugins/vendor-verified-badge::vendor-badge.settings.title'));

        return VendorBadgeSettingForm::create()->renderForm();
    }

    public function update(VendorBadgeSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }

    public function quickVerify(Store $store, BaseHttpResponse $response): BaseHttpResponse
    {
        $store->is_verified = 1;
        $store->verified_at = now();
        $store->verified_by = auth()->id();
        $store->save();

        return $response
            ->setMessage(trans('plugins/vendor-verified-badge::vendor-badge.quick_verify_success', ['name' => $store->name]));
    }

    public function quickUnverify(Store $store, BaseHttpResponse $response): BaseHttpResponse
    {
        $store->is_verified = 0;
        $store->verified_at = null;
        $store->verified_by = null;
        $store->save();

        return $response
            ->setMessage(trans('plugins/vendor-verified-badge::vendor-badge.quick_unverify_success', ['name' => $store->name]));
    }
}
