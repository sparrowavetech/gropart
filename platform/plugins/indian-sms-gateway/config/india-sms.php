<?php

return [
    'settings_prefix' => 'india_sms_',
    'default_gateway' => 'msg91',
    'supported_gateways' => ['msg91', 'fast2sms', 'firebasesms', 'twofactor', 'smscountry', 'kaleyra', 'generic'],
    'phone_regex' => '/^91[6-9]\d{9}$/',
    'otp' => [
        'length' => 6,
        'ttl' => 300,
        'max_attempts' => 5,
        'resend_cooldown' => 60,
        'requests_per_phone_hour' => 5,
        'requests_per_ip_hour' => 20,
    ],
];
