<?php

return [
    [
        'name' => 'Seller Fee Invoices',
        'flag' => 'seller-invoicing.index',
        'parent_flag' => 'marketplace.index',
    ],
    [
        'name' => 'View, Download and Print',
        'flag' => 'seller-invoicing.show',
        'parent_flag' => 'seller-invoicing.index',
    ],
    [
        'name' => 'Generate Invoices',
        'flag' => 'seller-invoicing.generate',
        'parent_flag' => 'seller-invoicing.index',
    ],
    [
        'name' => 'Settings',
        'flag' => 'seller-invoicing.settings',
        'parent_flag' => 'seller-invoicing.index',
    ],
    [
        'name' => 'Seller GST Reports',
        'flag' => 'seller-invoicing.gst-reports',
        'parent_flag' => 'seller-invoicing.index',
    ],
];
