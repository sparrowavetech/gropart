<?php

namespace ArchiElite\IpBlocker\Http\Requests;

use Botble\Support\Http\Requests\Request;

class AvailableCountriesRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $availableCountries = $this->input('available_countries', []);

        $this->merge([
            'available_countries' => $availableCountries,
        ]);
    }

    public function rules(): array
    {
        return [
            'available_countries' => ['array'],
        ];
    }
}
