<?php

return [
    'base_url' => env('ASHIKUL_LICENSE_API_URL', 'https://license.ashikul.info/api/v1'),
    'product_id' => 'indian-sms-gateway',
    'product_secret' => 'ee2277d3e7f4f1fc0c631cbbc7fcd3defd2b1d6b6a3170f96e16b9275208dc7f',
    'timeout' => 12,
    'connect_timeout' => 5,
    'validation_interval_minutes' => 720,
    'offline_grace_hours' => 72,
];
