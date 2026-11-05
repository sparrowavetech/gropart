<?php

return [
    [
        'name' => 'SMS Gateway',
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
        'name'        => 'Settings',
        'flag'        => 'sms.settings',
        'parent_flag' => 'sms.index',
    ],
    [
        'name' => 'Delivery Reports',
        'flag' => 'sms.delivery-reports.index',
        'parent_flag' => 'sms.index',
    ],
    [
        'name' => 'Logs',
        'flag' => 'sms.logs.index',
        'parent_flag' => 'sms.index',
    ],
    [
        'name' => 'View Log',
        'flag' => 'sms.logs.show',
        'parent_flag' => 'sms.logs.index',
    ],
];
