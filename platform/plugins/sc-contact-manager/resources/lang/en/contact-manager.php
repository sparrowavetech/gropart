<?php

return [
    'name' => 'Contact Manager',
    'create' => 'New Contact',
    'settings' => [
        'title' => 'Contact Manager',
        'description' => 'Contact Manager Plugin Settings',
        'contact-form' => 'Enable Contact Form Import',
    ],
    'models' => [
        'phone' => [
            'name' => 'Contact Phone',
            'create' => 'New Phone',
        ],
        'tag' => [
            'name' => 'Contact Tag',
            'create' => 'New Tag',
        ],
        'group' => [
            'name' => 'Contact Group',
            'create' => 'New Group',
        ],
        'email' => [
            'name' => 'Contact Email',
            'create' => 'New Email',
        ],
        'address' => [
            'name' => 'Contact Address',
            'create' => 'New Address',
        ],
    ],
    'forms' => [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'business_name' => 'Business Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'address' => 'Address',
        'group' => 'Group',
        'tag' => 'Tag',
        'address2' => 'Address 2',
        'city' => 'City',
        'state' => 'State',
        'postalcode' => 'Postal Code',
        'country' => 'Country',
        'source' => 'Source',
        'type' => 'Type',
        'contact_type' => 'Contact Type',
        'addresses' => 'Addresses',
        'emails' => 'Emails',
        'phones' => 'Phones',
    ],
];
