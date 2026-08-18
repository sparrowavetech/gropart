<?php
return [
    'variables' => [
        'customer_name' => 'Customer Name',
        'customer_phone' => 'Customer Phone',
        'customer_email' => 'Customer Email',
        'customer_address' => 'Customer Address',
        'shipping_method' => 'Shipping Method',
        'payment_method' => 'Payment Method',
        'order_id' => 'Order Id',
        'order_token' => 'Order Token',
        'return_reason' => 'Return Reason',
        'invoice_code' => 'Invoice Code',
        'invoice_link' => 'Invoice Link',
        'store_name' => 'Store Name',
        'store_phone' => 'Store Phone',
        'store_link' => 'Store Link',
        'product_name' => 'Product Name',
        'withdrawal_amount' => 'Withdrawal Amount',
        'product_url' => 'Product Url',
        'verify_link' => 'Verify Link',
        'reset_link' => 'Reset Link',
        'otp' => 'OTP',
    ],

    'template_variables' => [
        'welcome' => ['customer_name'],
        'otp' => ['customer_name', 'customer_phone', 'otp'],

        'order_confirmation' => ['customer_name', 'customer_phone', 'customer_email', 'customer_address', 'shipping_method', 'payment_method', 'order_id', 'order_token', 'store_name', 'store_phone', 'store_link'],
        'order_cancellation' => ['customer_name', 'customer_phone', 'customer_email', 'customer_address', 'order_id', 'order_token', 'store_name', 'store_phone', 'store_link'],
        'delivering_confirmation' => ['customer_name', 'customer_phone', 'customer_email', 'customer_address', 'shipping_method', 'order_id', 'order_token', 'store_name', 'store_phone', 'store_link'],
        'order_created_customer' => ['customer_name', 'customer_phone', 'customer_email', 'customer_address', 'shipping_method', 'payment_method', 'order_id', 'order_token', 'store_name', 'store_phone', 'store_link'],
        'order_created_admin' => ['customer_name', 'customer_phone', 'customer_email', 'customer_address', 'shipping_method', 'payment_method', 'order_id', 'order_token', 'store_name', 'store_phone', 'store_link'],
        'order_status_returned' => ['customer_name', 'customer_phone', 'customer_email', 'customer_address', 'order_id', 'order_token', 'return_reason', 'store_name', 'store_phone', 'store_link'],
        'order_payment_confirmed_customer' => ['customer_name', 'customer_phone', 'customer_email', 'order_id', 'order_token', 'payment_method', 'invoice_code', 'invoice_link', 'store_name', 'store_phone', 'store_link'],
        'shipping_status_changed_customer' => ['customer_name', 'customer_phone', 'customer_email', 'customer_address', 'shipping_method', 'order_id', 'order_token', 'store_name', 'store_phone', 'store_link'],

        'vendor_new_order' => ['store_name', 'store_phone', 'store_link', 'customer_name', 'customer_phone', 'order_id', 'order_token'],
        'vendor_account_approved' => ['store_name', 'store_phone', 'store_link'],
        'product_approved' => ['store_name', 'store_phone', 'store_link', 'product_name', 'product_url'],
        'withdrawal_approved' => ['store_name', 'store_phone', 'store_link', 'withdrawal_amount'],
    ],
];
