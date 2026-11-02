<?php

return [
    [
        'name' => 'plugins/sms::sms.title',
        'flag' => 'sms.index',
    ],
    [
        'name'        => 'plugins/sms::sms.permission_create',
        'flag'        => 'sms.create',
        'parent_flag' => 'sms.index',
    ],
    [
        'name'        => 'plugins/sms::sms.permission_edit',
        'flag'        => 'sms.edit',
        'parent_flag' => 'sms.index',
    ],
    [
        'name'        => 'plugins/sms::sms.permission_delete',
        'flag'        => 'sms.destroy',
        'parent_flag' => 'sms.index',
    ],
    [
        'name'        => 'plugins/sms::sms.setting',
        'flag'        => 'sms.settings',
        'parent_flag' => 'sms.index',
    ],
    [
        'name' => 'plugins/sms::sms.delivery_report.title',
        'flag' => 'sms.delivery-reports.index',
        'parent_flag' => 'sms.index',
    ],
    [
        'name' => 'plugins/sms::sms.logs.title',
        'flag' => 'sms.logs.index',
        'parent_flag' => 'sms.index',
    ],
    [
        'name' => 'plugins/sms::sms.logs.view',
        'flag' => 'sms.logs.show',
        'parent_flag' => 'sms.logs.index',
    ],
];
