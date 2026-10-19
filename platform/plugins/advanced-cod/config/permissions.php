<?php

return [
    ['name'=>'Confirm Advanced COD orders','flag'=>'advanced-cod.manual-confirm','parent_flag'=>'orders.index'],
    ['name'=>'Record manual COD advances','flag'=>'advanced-cod.manual-receipt','parent_flag'=>'orders.index'],
    ['name'=>'Override COD advance requirement','flag'=>'advanced-cod.override','parent_flag'=>'orders.index'],
    ['name'=>'Advanced COD settlement','flag'=>'advanced-cod.settle','parent_flag'=>'orders.index'],
    [
        'name' => 'Partial c o d prepayments',
        'flag' => 'partial-cod-prepayment.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'partial-cod-prepayment.create',
        'parent_flag' => 'partial-cod-prepayment.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'partial-cod-prepayment.edit',
        'parent_flag' => 'partial-cod-prepayment.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'partial-cod-prepayment.destroy',
        'parent_flag' => 'partial-cod-prepayment.index',
    ],
];
