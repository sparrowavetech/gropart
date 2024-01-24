<?php

use Skillcraft\ContactManager\Enums\ContactTypeEnum;
use Skillcraft\ContactManager\Enums\SimpleStatusEnum;
use Skillcraft\ContactManager\Enums\ContactDataTypeEnum;

return [
    'contact_status' => [
        ContactDataTypeEnum::BILLING => 'Billing',
        ContactDataTypeEnum::HOME => 'Home',
        ContactDataTypeEnum::SHIPPING => 'Shipping',
        ContactDataTypeEnum::WORK => 'Work',
        ContactDataTypeEnum::OTHER => 'Other',
    ],
    'contact_type' => [
        ContactTypeEnum::CUSTOMER => 'Customer',
        ContactTypeEnum::LEAD => 'Lead',
    ],
    'simple_status' => [
        SimpleStatusEnum::ACTIVE => 'Active',
        SimpleStatusEnum::DEACTIVE => 'Deactive',
    ]
];
