<?php

return [
    'prefix' => 'wholesale_',

    'enabled' => env('WHOLESALE_ENABLED', true),

    'require_approval' => env('WHOLESALE_REQUIRE_APPROVAL', true),

    'show_prices_to_guests' => env('WHOLESALE_SHOW_PRICES_GUESTS', false),

    'allow_multiple_groups' => env('WHOLESALE_MULTIPLE_GROUPS', true),

    'enable_for_guests' => env('WHOLESALE_ENABLE_FOR_GUESTS', false),

    'discount_resolution' => env('WHOLESALE_DISCOUNT_RESOLUTION', 'highest'),
];
