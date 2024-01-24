<?php

namespace Skillcraft\ContactManager\Http\Requests;

use Skillcraft\ContactManager\Enums\ContactTypeEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ContactManagerRequest extends Request
{
    public function rules(): array
    {
        return [
            'first_name'    => 'required|string',
            'last_name'     => 'required|string',
            'business_name' => 'nullable|string',
            'emails'         => 'nullable|array',
            'phones'         => 'nullable|array',
            'addresses'       => 'nullable|array',
            'source'        => 'nullable|string',
            'type'          => Rule::in(ContactTypeEnum::values()),
            'group_id'      => 'nullable|integer',
        ];
    }
}
