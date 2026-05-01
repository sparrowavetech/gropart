<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    >
    <meta
        http-equiv="X-UA-Compatible"
        content="ie=edge"
    >
    <title><?php echo e(PageTitle::getTitle()); ?></title>
    <?php if($csrfToken = csrf_token()): ?>
        <meta
            name="csrf-token"
            content="<?php echo e($csrfToken); ?>"
        >
    <?php endif; ?>

    <?php
        $faviconUrl = AdminHelper::getAdminFaviconUrl();
        $faviconType = setting('admin_favicon_type', 'image/x-icon');
    ?>
    <link
        href="<?php echo e($faviconUrl); ?>"
        rel="icon shortcut"
        type="<?php echo e($faviconType); ?>"
    >
    <meta
        property="og:image"
        content="<?php echo e($faviconUrl); ?>"
    >

    <meta
        name="description"
        content="<?php echo e($copyright = strip_tags(trans('core/base::layouts.copyright', ['year' => Carbon\Carbon::now()->year, 'company' => setting('admin_title', config('core.base.general.base_name')), 'version' => get_cms_version()]))); ?>"
    >
    <meta
        property="og:description"
        content="<?php echo e($copyright); ?>"
    >

    <?php echo $__env->make('core/base::components.layouts.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldContent('head'); ?>

    <script>
        window.siteUrl = "<?php echo e(url('')); ?>";

        <?php if(Auth::check()): ?>
            window.siteEditorLocale = "<?php echo e(apply_filters('cms_site_editor_locale', App::getLocale())); ?>";
            window.ckEditorFontFamilies = "<?php echo e(config('core.base.general.editor.ckeditor.font_families')); ?>";
            window.siteAuthorizedUrl = "<?php echo e(rescue(fn() => route('settings.license.verify.index'))); ?>";
            window.licenseCheckUrl = "<?php echo e(rescue(fn() => route('license.check'))); ?>";
            window.isAuthenticated = <?php echo e(Auth::check() ? 'true' : 'false'); ?>;
        <?php endif; ?>
    </script>

    <?php echo e($header ?? null); ?>


    <?php echo $__env->yieldPushContent('header'); ?>

    <?php echo AdminAppearance::getCustomCSS(); ?>


    <?php echo AdminAppearance::getCustomJs('header'); ?>


    <?php echo apply_filters(BASE_FILTER_HEAD_LAYOUT_TEMPLATE, null); ?>

</head>

<body
    class="<?php echo $__env->yieldContent('body-class', $bodyClass ?? 'page-sidebar-closed-hide-logo page-content-white page-container-bg-solid'); ?> <?php echo e(session()->get('sidebar-menu-toggle') ? 'page-sidebar-closed' : ''); ?>"
    style="<?php echo $__env->yieldContent('body-style', $bodyStyle ?? null); ?>"
    <?php if(BaseHelper::adminLanguageDirection() === 'rtl'): ?> dir="rtl" <?php endif; ?>
    <?php echo Html::attributes($bodyAttributes ?? []); ?>

    <?php if(AdminHelper::themeMode() === 'dark'): ?> data-bs-theme="dark" <?php endif; ?>
>
    <?php echo AdminAppearance::getCustomJs('body'); ?>


    <?php echo e($headerLayout ?? null); ?>


    <?php echo apply_filters(BASE_FILTER_HEADER_LAYOUT_TEMPLATE, null); ?>


    <div id="app">
        <?php echo e($slot); ?>

    </div>

    <?php echo $__env->make('core/base::elements.common', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo Assets::renderFooter(); ?>


    <?php echo $__env->yieldContent('javascript'); ?>

    <div id="stack-footer">
        <?php echo e($footer ?? null); ?>

        <?php echo $__env->yieldPushContent('footer'); ?>
    </div>

    <?php echo AdminAppearance::getCustomJs('footer'); ?>


    <?php echo apply_filters(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, null); ?>

</body>

</html>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/components/layouts/base.blade.php ENDPATH**/ ?>