<?php

return [
    [
        'name' => 'Razorpay Easy EMI Settings',
        'flag' => 'razorpay-easy-emi.settings',
        'parent_flag' => 'ecommerce.settings',
    ],
    [
        'name' => 'Manage EMI Program Requests',
        'flag' => 'razorpay-easy-emi.requests.index',
        'parent_flag' => 'marketplace.index',
    ],
    [
        'name' => 'Approve or Reject EMI Requests',
        'flag' => 'razorpay-easy-emi.requests.action',
        'parent_flag' => 'razorpay-easy-emi.requests.index',
    ],
    [
        'name' => 'Submit EMI Activation Request',
        'flag' => 'marketplace.vendor.razorpay-easy-emi.request',
        'parent_flag' => 'marketplace.vendor.dashboard',
    ],
];

