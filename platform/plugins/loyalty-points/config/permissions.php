<?php

return [
    [
        'name' => 'Loyalty Points',
        'flag' => 'loyalty-points.index',
        'parent_flag' => 'plugins.ecommerce',
    ],
    [
        'name' => 'Create',
        'flag' => 'loyalty-points.create',
        'parent_flag' => 'loyalty-points.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'loyalty-points.edit',
        'parent_flag' => 'loyalty-points.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'loyalty-points.destroy',
        'parent_flag' => 'loyalty-points.index',
    ],
    [
        'name' => 'Settings',
        'flag' => 'loyalty-points.settings',
        'parent_flag' => 'loyalty-points.index',
    ],
    [
        'name' => 'Members',
        'flag' => 'loyalty-points.members.index',
        'parent_flag' => 'loyalty-points.index',
    ],
    [
        'name' => 'Adjust Points',
        'flag' => 'loyalty-points.members.adjust',
        'parent_flag' => 'loyalty-points.members.index',
    ],
    [
        'name' => 'Member Levels',
        'flag' => 'loyalty-points.levels.index',
        'parent_flag' => 'loyalty-points.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'loyalty-points.levels.create',
        'parent_flag' => 'loyalty-points.levels.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'loyalty-points.levels.edit',
        'parent_flag' => 'loyalty-points.levels.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'loyalty-points.levels.destroy',
        'parent_flag' => 'loyalty-points.levels.index',
    ],
    [
        'name' => 'License',
        'flag' => 'loyalty-points.license',
        'parent_flag' => 'loyalty-points.index',
    ],
];
