<?php if(setting('admin_logo') || config('core.base.general.logo')): ?>
    <a href="<?php echo e(route('dashboard.index')); ?>">
        <img
            src="<?php echo e(setting('admin_logo') ? RvMedia::getImageUrl(setting('admin_logo')) : url(config('core.base.general.logo'))); ?>"
            style="max-height: <?php echo e(setting('admin_logo_max_height', $defaultLogoHeight ?? 32)); ?>px; height: auto;"
            alt="<?php echo e(setting('admin_title', config('core.base.general.base_name'))); ?>"
            class="navbar-brand-image"
        >
    </a>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/partials/logo.blade.php ENDPATH**/ ?>