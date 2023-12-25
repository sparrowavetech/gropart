<?php

namespace ArchiElite\IpBlocker\Http\Requests;

use Botble\Support\Http\Requests\Request;

class UpdateSettingsRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $ipAddresses = $this->input('ip_addresses');
        $ipAddressesRange = $this->input('wildcard_ip_address');

        $this->merge([
            'ip_addresses' => $ipAddresses ? json_decode($ipAddresses, true) : [],
            'wildcard_ip_address' => $ipAddressesRange ? json_decode($ipAddressesRange, true) : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'ip_addresses' => ['sometimes', 'array'],
            'ip_addresses.*.value' => ['required', 'ip'],
            'wildcard_ip_address' => ['sometimes', 'array'],
            'wildcard_ip_address.*.value' => ['required', 'regex:/^(?:\d{1,2}|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d{1,2}|1\d{2}|2[0-4]\d|25[0-5])\.\*\z/'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ip_addresses.*.value' => trans('plugins/ip-blocker::ip-blocker.update_settings_ip_address'),
            'wildcard_ip_address.*.value' => trans('plugins/ip-blocker::ip-blocker.update_settings_ip_range'),
        ];
    }
}
