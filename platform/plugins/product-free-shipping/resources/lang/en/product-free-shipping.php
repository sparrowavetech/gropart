<?php

return [
    'name' => 'Product Free Shipping',
    'form_field_label' => 'Is Free Shipping?',
    'form_field_help' => 'When enabled, this product is delivered for free and its weight is deducted from courier shipping charges.',
    'free_delivery' => 'Free Delivery',
    'free_shipping_method_title' => 'Free Delivery',
    'settings' => [
        'title' => 'Product Free Shipping Settings',
        'description' => 'Configure product-level free shipping behavior, badge text, and courier weight calculations.',
        'enable_plugin' => 'Enable Product Free Shipping',
        'enable_plugin_help' => 'Allow individual products to be flagged with free shipping.',
        'badge_text' => 'Product Page Badge Text',
        'badge_text_help' => 'Text displayed inside the free shipping badge on product pages (e.g. Free Delivery).',
        'method_title' => 'Checkout Shipping Method Title',
        'method_title_help' => 'Name of the free delivery shipping option at checkout.',
        'hide_other_methods' => 'Hide Paid Shipping Methods When Free Shipping Applies',
        'hide_other_methods_help' => 'If checked, paid courier methods will be hidden when all items in the cart qualify for free delivery.',
    ],
];
