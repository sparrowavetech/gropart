<?php

return [
    'name' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.name',
    'description' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.description',
    'templates' => [
        'admin_new_emi_request' => [
            'title' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.admin_new_emi_request_title',
            'description' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.admin_new_emi_request_desc',
            'subject' => 'New EMI Program Request from {{ store_name }} - {{ product_name }}',
            'can_off' => true,
            'enabled' => true,
            'variables' => [
                'store_name' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.store_name',
                'vendor_name' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.vendor_name',
                'product_name' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.product_name',
                'product_price' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.product_price',
                'requested_scheme' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.requested_scheme',
                'subvention_willingness' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.subvention_willingness',
                'notes' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.notes',
                'request_url' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.request_url',
                'created_at' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.created_at',
            ],
        ],

        'vendor_emi_request_status_updated' => [
            'title' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vendor_status_updated_title',
            'description' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vendor_status_updated_desc',
            'subject' => 'EMI Program Request Update for {{ product_name }}: {{ status }}',
            'can_off' => true,
            'enabled' => true,
            'variables' => [
                'vendor_name' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.vendor_name',
                'store_name' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.store_name',
                'product_name' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.product_name',
                'requested_scheme' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.requested_scheme',
                'status' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.status',
                'admin_remarks' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.admin_remarks',
                'approved_offer_id' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.approved_offer_id',
                'product_edit_url' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.product_edit_url',
                'reviewed_at' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vars.reviewed_at',
            ],
        ],
    ],
];

