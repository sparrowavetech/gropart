<?php
namespace SparroWave\EnquiryProducts\Http\Controllers;
class SettingsController extends \Botble\Setting\Http\Controllers\SettingController
{
    public function edit()
    {
        $this->pageTitle(__('plugins/enquiry-products::messages.enquiry_settings'));
        return \SparroWave\EnquiryProducts\Forms\SettingsForm::create()->renderForm();
    }
    public function update(\SparroWave\EnquiryProducts\Http\Requests\SettingsRequest $request)
    {
        $data = $request->validated();
        $data['ep_catalog_subtitle'] ??= '';
        $data['ep_reference_prefix'] ??= '';
        $data['ep_reference_suffix'] ??= '';
        $old = setting('ep_catalog_slug', 'equipments-enquiry-products');
        if ($old !== $data['ep_catalog_slug']) {
            $data['ep_previous_slugs'] = json_encode(array_values(array_unique(array_merge(json_decode(setting('ep_previous_slugs', '[]'), true) ?: [], [$old]))));
        }
        $response = $this->performUpdate($data);
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        return $response;
    }
}
