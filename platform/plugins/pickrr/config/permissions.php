<?php

return [
    [
        'name' => 'Pickrrs',
        'flag' => 'pickrr.index',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'pickrr.create',
        'parent_flag' => 'pickrr.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'pickrr.edit',
        'parent_flag' => 'pickrr.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'pickrr.destroy',
        'parent_flag' => 'pickrr.index',
    ],
];
