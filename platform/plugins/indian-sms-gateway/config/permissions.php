<?php

return [
    ['name' => 'Indian SMS', 'flag' => 'india-sms.index'],
    ['name' => 'Delivery logs', 'flag' => 'india-sms.logs.index', 'parent_flag' => 'india-sms.index'],
    ['name' => 'Delete delivery logs', 'flag' => 'india-sms.logs.destroy', 'parent_flag' => 'india-sms.logs.index'],
    ['name' => 'SMS templates', 'flag' => 'india-sms.templates.index', 'parent_flag' => 'india-sms.index'],
    ['name' => 'Create templates', 'flag' => 'india-sms.templates.create', 'parent_flag' => 'india-sms.templates.index'],
    ['name' => 'Edit templates', 'flag' => 'india-sms.templates.edit', 'parent_flag' => 'india-sms.templates.index'],
    ['name' => 'Delete templates', 'flag' => 'india-sms.templates.destroy', 'parent_flag' => 'india-sms.templates.index'],
    ['name' => 'Gateways', 'flag' => 'india-sms.gateways.index', 'parent_flag' => 'india-sms.index'],
    ['name' => 'Configure gateways', 'flag' => 'india-sms.gateways.edit', 'parent_flag' => 'india-sms.gateways.index'],
    ['name' => 'Send test SMS', 'flag' => 'india-sms.gateways.test', 'parent_flag' => 'india-sms.gateways.index'],
    ['name' => 'OTP logs', 'flag' => 'india-sms.otps.index', 'parent_flag' => 'india-sms.index'],
    ['name' => 'Settings', 'flag' => 'india-sms.settings', 'parent_flag' => 'india-sms.index'],
];
