<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Http\Requests\Concerns\HasPhoneFieldValidation;
use Botble\Ecommerce\Facades\EcommerceHelperFacade;
use Botble\Support\Http\Requests\Request;

class EnquiryRequest extends Request
{
    use HasPhoneFieldValidation;

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->preparePhoneForValidation();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'                  => 'required|max:120|min:2',
            'email'                 => 'required|max:60|min:6|email',
            'phone'                 => EcommerceHelperFacade::getPhoneValidationRule(),
            'state'                 => 'required',
            'city'                  => 'required',
            'address'               => 'required',
            'zip_code'              => 'required',
        ];
    }
}
