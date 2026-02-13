<?php

return [
    [
        'name' => 'Product bundles',
        'flag' => 'product-bundles.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'product-bundles.create',
        'parent_flag' => 'product-bundles.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'product-bundles.edit',
        'parent_flag' => 'product-bundles.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'product-bundles.destroy',
        'parent_flag' => 'product-bundles.index',
    ],
    [
        'name' => 'Export Bundles',
        'flag' => 'product-bundles.export',
        'parent_flag' => 'tools.data-synchronize',
    ],
    [
        'name' => 'Import Bundles',
        'flag' => 'product-bundles.import',
        'parent_flag' => 'tools.data-synchronize',
    ],
];
