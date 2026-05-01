<?php

return [
    [
        'name' => 'Wholesale',
        'flag' => 'wholesale.index',
    ],
    [
        'name' => 'Wholesale Products',
        'flag' => 'wholesale.products.index',
        'parent_flag' => 'wholesale.index',
    ],
    [
        'name' => 'Customer Groups',
        'flag' => 'wholesale.customer-groups.index',
        'parent_flag' => 'wholesale.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'wholesale.customer-groups.create',
        'parent_flag' => 'wholesale.customer-groups.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'wholesale.customer-groups.edit',
        'parent_flag' => 'wholesale.customer-groups.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'wholesale.customer-groups.destroy',
        'parent_flag' => 'wholesale.customer-groups.index',
    ],
    [
        'name' => 'Wholesale Applications',
        'flag' => 'wholesale.applications.index',
        'parent_flag' => 'wholesale.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'wholesale.applications.edit',
        'parent_flag' => 'wholesale.applications.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'wholesale.applications.destroy',
        'parent_flag' => 'wholesale.applications.index',
    ],
    [
        'name' => 'Pricing Rules',
        'flag' => 'wholesale.pricing-rules.index',
        'parent_flag' => 'wholesale.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'wholesale.pricing-rules.create',
        'parent_flag' => 'wholesale.pricing-rules.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'wholesale.pricing-rules.edit',
        'parent_flag' => 'wholesale.pricing-rules.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'wholesale.pricing-rules.destroy',
        'parent_flag' => 'wholesale.pricing-rules.index',
    ],
    [
        'name' => 'Settings',
        'flag' => 'wholesale.settings',
        'parent_flag' => 'wholesale.index',
    ],
];
