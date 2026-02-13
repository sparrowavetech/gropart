<?php

return [
    [
        'name' => 'Content Injectors',
        'flag' => 'contentinjector.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'contentinjector.create',
        'parent_flag' => 'contentinjector.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'contentinjector.edit',
        'parent_flag' => 'contentinjector.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'contentinjector.destroy',
        'parent_flag' => 'contentinjector.index',
    ],
];
