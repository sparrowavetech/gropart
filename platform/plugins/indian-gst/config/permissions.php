<?php

return [
    [
        'name' => 'Indian GST System',
        'flag' => 'indian-gst.index',
        'parent_flag' => 'plugins.ecommerce',
    ],
    [
        'name' => 'GST Settings',
        'flag' => 'ecommerce.settings.indian-gst',
        'parent_flag' => 'indian-gst.index',
    ],
    [
        'name' => 'Tax Slabs',
        'flag' => 'indian-gst.slabs.index',
        'parent_flag' => 'indian-gst.index',
    ],
    [
        'name' => 'Tax Slab Mapped Products',
        'flag' => 'indian-gst.slabs.products',
        'parent_flag' => 'indian-gst.slabs.index',
    ],
];
