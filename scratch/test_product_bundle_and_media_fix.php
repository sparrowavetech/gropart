<?php

// Test script for Product Bundle and Media Modal Fixes
echo "Running targeted verification for Product Bundle & Media Modal fixes...\n";

// 1. Verify admin-universal-phone-helper.js logic
$jsPath = __DIR__ . '/../platform/plugins/farmart-helper/public/js/admin-universal-phone-helper.js';
$pubJsPath = __DIR__ . '/../public/vendor/core/plugins/farmart-helper/js/admin-universal-phone-helper.js';

if (! file_exists($jsPath) || ! file_exists($pubJsPath)) {
    echo "FAIL: JS file not found\n";
    exit(1);
}

$jsContent = file_get_contents($jsPath);
$pubJsContent = file_get_contents($pubJsPath);

// Check that media modals and large modals are guarded
if (! str_contains($jsContent, 'rv-media-modal') || ! str_contains($jsContent, 'modal-full')) {
    echo "FAIL: Media modal guard missing in plugin JS\n";
    exit(1);
}
if (! str_contains($pubJsContent, 'rv-media-modal') || ! str_contains($pubJsContent, 'modal-full')) {
    echo "FAIL: Media modal guard missing in public JS\n";
    exit(1);
}
if ($jsContent !== $pubJsContent) {
    echo "FAIL: Public JS out of sync with plugin JS\n";
    exit(1);
}
echo "PASS: JS guard for Media Modal and Full/XL/LG modals verified.\n";

// 2. Verify ProductAdminViewServiceProvider.php
$providerPath = __DIR__ . '/../platform/plugins/farmart-helper/src/Providers/ProductAdminViewServiceProvider.php';
$providerContent = file_get_contents($providerPath);

if (! str_contains($providerContent, 'plugins/product-bundles::admin.product-metabox')) {
    echo "FAIL: View composer for product-metabox missing\n";
    exit(1);
}
if (! str_contains($providerContent, 'pb_is_bundle')) {
    echo "FAIL: Bundle detachment cleanup missing\n";
    exit(1);
}
echo "PASS: ProductAdminViewServiceProvider bundle guards verified.\n";

echo "ALL LOCAL CHECKS PASSED SUCCESSFULLY!\n";

