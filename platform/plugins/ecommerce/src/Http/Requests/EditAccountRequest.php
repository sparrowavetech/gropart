<?php

namespace Botble\Ecommerce\Http\Requests;

use BaseHelper;
use Botble\Support\Http\Requests\Request;

class EditAccountRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'  => 'required|max:255',
            'phone' => 'sometimes|min:10|max:10' . BaseHelper::getPhoneValidationRule(),
            'dob'   => 'max:20|sometimes',
        ];
    }
}
