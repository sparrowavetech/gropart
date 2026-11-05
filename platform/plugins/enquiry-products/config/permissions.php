<?php

return [
    [
        'name' => 'Enquiry Products',
        'flag' => 'enquiry-products.index',
    ],
    [
        'name' => 'Settings',
        'flag' => 'enquiry-products.settings',
        'parent_flag' => 'enquiry-products.index',
    ],
    [
        'name' => 'Update',
        'flag' => 'enquiry-products.edit',
        'parent_flag' => 'enquiry-products.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'enquiry-products.destroy',
        'parent_flag' => 'enquiry-products.index',
    ],
];
