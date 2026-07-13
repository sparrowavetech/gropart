<?php echo SeoHelper::render(); ?>

<link
    rel="sitemap"
    title="Sitemap"
    href="<?php echo e(rescue(fn() => route('public.sitemap'), report: false)); ?>"
    type="application/xml"
>

<?php if($favicon = theme_option('favicon')): ?>
    <?php echo e(Html::favicon(RvMedia::getImageUrl($favicon), ['type' => theme_option('favicon_type', 'image/x-icon')])); ?>

<?php endif; ?>

<?php if(Theme::has('headerMeta')): ?>
    <?php echo Theme::get('headerMeta'); ?>

<?php endif; ?>

<?php echo apply_filters('theme_front_meta', null); ?>


<?php echo Theme::typography()->renderCssVariables(); ?>


<?php echo Theme::asset()->container('before_header')->styles(); ?>

<?php echo Theme::asset()->styles(); ?>

<?php echo Theme::asset()->container('after_header')->styles(); ?>



<style>@layer base{img[data-dims-auto]{max-width:100%;width:auto;height:auto}}</style>

<?php echo Theme::asset()->container('header')->scripts(); ?>


<?php echo apply_filters(THEME_FRONT_HEADER, null); ?>


<?php echo SeoHelper::meta()->getAnalytics()->render(); ?>


<script>
    window.siteUrl = "<?php echo e(rescue(fn() => BaseHelper::getHomepageUrl())); ?>";
</script>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/packages/theme/resources/views/partials/header.blade.php ENDPATH**/ ?>