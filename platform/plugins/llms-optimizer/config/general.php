<?php

return [
    'prefix' => 'llms_optimizer_',

    'output_modes' => [
        'dynamic' => 'Dynamic Route',
        'static' => 'Static File',
        'both' => 'Both',
    ],

    'sorting_orders' => [
        'newest' => 'Newest First',
        'alphabetical' => 'Alphabetical',
    ],

    'link_formats' => [
        'markdown' => 'Markdown',
        'plain' => 'Plain URL',
    ],

    'defaults' => [
        'enabled' => true,
        'enable_pages' => true,
        'enable_blog_posts' => true,
        'enable_products' => true,
        'site_description' => '',
        'include_site_tagline' => true,
        'include_descriptions' => true,
        'include_sitemap_reference' => true,
        'include_optional_section' => false,
        'optional_links' => '',
        'output_mode' => 'dynamic',
        'sorting_order' => 'newest',
        'max_items_per_type' => 100,
        'link_format' => 'markdown',
        'include_token_count' => false,
        'cache_duration' => 60,
    ],

    'cache_key' => 'llms_optimizer_content',
];

