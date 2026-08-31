<?php

return [
    'name' => 'Vendor Verified & Type Badge',
    'verified' => 'Verified',
    'unverified' => 'Unverified',
    'verified_vendor' => 'Verified Vendor',
    'unverified_vendor' => 'Unverified Vendor',
    'shop_type' => 'Shop Type',
    'quick_verify' => 'Verify Store',
    'quick_unverify' => 'Revoke Verification',
    'quick_verify_success' => 'Store ":name" verified successfully!',
    'quick_unverify_success' => 'Verification revoked for store ":name"!',
    'types' => [
        'manufacture' => 'Manufacturer',
        'wholesaler' => 'Wholesaler',
        'retailer' => 'Retailer',
    ],
    'settings' => [
        'title' => 'Vendor Verification & Type Badge Settings',
        'description' => 'Configure verification badge icon, application links, profile completion threshold, and menu gating.',
        'badge_icon' => 'Verified Badge Icon',
        'badge_icon_helper' => 'Upload a custom verified icon image (PNG/SVG). If left empty, the default verified icon will be used.',
        'tooltip_text' => 'Badge Hover Tooltip Text',
        'tooltip_text_helper' => 'Text displayed to customers when hovering over the verified badge.',
        'application_url' => 'Verification Application URL',
        'application_url_helper' => 'URL where unverified vendors can apply for green tick verification.',
        'menu_gating_enabled' => 'Enable Dashboard Menu Gating',
        'menu_gating_enabled_helper' => 'When enabled, vendors with incomplete profiles (< threshold %) cannot access Products, Orders, and other core features.',
        'completion_threshold' => 'Profile Completion Threshold (%)',
        'completion_threshold_helper' => 'Percentage score required to unlock vendor dashboard menus (Default: 80%).',
    ],
];
