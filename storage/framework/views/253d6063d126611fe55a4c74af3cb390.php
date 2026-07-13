<?php
    $storedCategories = $storedCategories ?? [];
    $marketingGranted = ! empty($storedCategories['marketing']);
    $analyticsGranted = ! empty($storedCategories['analytics']);
    $hasStoredConsent = ! empty($storedCategories);
?>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('consent', 'default', {
        'ad_storage': 'denied',
        'analytics_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'functionality_storage': 'denied',
        'personalization_storage': 'denied',
        'security_storage': 'granted',
        'wait_for_update': 500
    });
    <?php if($hasStoredConsent): ?>
    gtag('consent', 'update', {
        'ad_storage': '<?php echo e($marketingGranted ? 'granted' : 'denied'); ?>',
        'analytics_storage': '<?php echo e($analyticsGranted ? 'granted' : 'denied'); ?>',
        'ad_user_data': '<?php echo e($marketingGranted ? 'granted' : 'denied'); ?>',
        'ad_personalization': '<?php echo e($marketingGranted ? 'granted' : 'denied'); ?>'
    });
    <?php endif; ?>
</script>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/cookie-consent/resources/views/partials/head-scripts.blade.php ENDPATH**/ ?>