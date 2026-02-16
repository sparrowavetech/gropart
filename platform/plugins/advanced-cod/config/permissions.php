<?php

return [
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
