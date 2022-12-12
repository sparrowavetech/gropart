<?php

return [
    'name'   => 'Sms',
    'create' => 'New sms',
    'edit'   => 'Edit sms',
    'template_id' => 'Template Id',
    'template' => 'Template',
    'template_placeholder'=>'Template ',
    'template_id_placeholder' =>'Template Id',
    'settings' => [
        'title' => 'SMS',
        'description' => 'Settings for sms plugin',
        'sms_url' =>'SMS Api  Url',
        'sms_url_helper' => 'http://api.exmaple.in/api/mt/SendSMS?user=username&password=password&senderid=senderid&channel=chennel&DCS=0&flashsms=0&number={{mobile}}&text={{message}}&route=##&DLTTemplateId=approvded dlt templateid&PEID={{entityid}}'
    ],
    'actions'=>[
        'welcome'=>'Welcome',
        'order_confirmation'=>'Order confirmation',
        'order_cancellation'=>'Order cancellation',
        'delivering_confirmation'=>'Delivering confirmation',
        'admin_order_confirmation'=>'Admin Order confirmation',
        'payment_confirmation'=>'Payment confirmation',
        'incomplete_order'=>'Incomplete order',
        'order_return_request'=>'Order return request',
        'invoice_payment_detail'=>'Invoice Payment Detail',
        'enquiry_confirmation'=>'Enquiry confirmation',
        'email_send_to_user'=>'Email send to user',
        'vendor_new_order'=>'Notice about new order',
        'vendor_account_approved'=>'Vendor account approved',
        'product_approved'=>'Product approved',
        'withdrawal_approved'=>'Withdrawal approved',
        
    ]
];
