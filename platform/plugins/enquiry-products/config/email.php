<?php
return ['name' => 'Enquiry Products', 'description' => 'Equipment enquiry notifications', 'templates' => [
    'received' => ['title' => 'New equipment enquiry', 'description' => 'Sent to the admin, customer and product vendor.', 'subject' => 'Equipment enquiry {{ enquiry_code }}', 'can_off' => true, 'enabled' => true, 'variables' => ['enquiry_code' => 'Enquiry reference', 'product_name' => 'Equipment', 'customer_name' => 'Customer name', 'customer_email' => 'Customer email', 'customer_phone' => 'Customer phone']],
]];
