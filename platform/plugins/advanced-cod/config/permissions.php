<?php

return [
    [
        'name' => 'Confirm Advanced COD orders',
        'flag' => 'advanced-cod.manual-confirm',
        'parent_flag' => 'orders.index',
    ],
    [
        'name' => 'Record manual COD advances',
        'flag' => 'advanced-cod.manual-receipt',
        'parent_flag' => 'orders.index',
    ],
    [
        'name' => 'Override COD advance requirement',
        'flag' => 'advanced-cod.override',
        'parent_flag' => 'orders.index',
    ],
    [
        'name' => 'Advanced COD settlement',
        'flag' => 'advanced-cod.settle',
        'parent_flag' => 'orders.index',
    ],
];
