<?php
    $defaultLocale = Language::getDefaultLocale();
    $xDefaultUrl = collect($hreflangUrls)->first(function ($url, $code) use ($defaultLocale) {
        return str_starts_with($code, $defaultLocale);
    }) ?? rtrim(Language::getLocalizedURL($defaultLocale, url()->current(), [], false), '/');
?>

<link
    href="<?php echo e(rtrim($xDefaultUrl, '/')); ?>"
    hreflang="x-default"
    rel="alternate"
/>

<?php $__currentLoopData = $hreflangUrls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hreflangCode => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <link
        href="<?php echo e($url); ?>"
        hreflang="<?php echo e($hreflangCode); ?>"
        rel="alternate"
    />
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/language/resources/views/partials/hreflang.blade.php ENDPATH**/ ?>