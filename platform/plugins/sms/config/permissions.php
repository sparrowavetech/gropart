<?php

return [
    [
        'name' => 'Sms',
        'flag' => 'sms.index',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'sms.create',
        'parent_flag' => 'sms.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'sms.edit',
        'parent_flag' => 'sms.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'sms.destroy',
        'parent_flag' => 'sms.index',
    ],
    [
        'name'        => 'Setting',
        'flag'        => 'sms.settings',
        'parent_flag' => 'sms.settings',
    ],
];
