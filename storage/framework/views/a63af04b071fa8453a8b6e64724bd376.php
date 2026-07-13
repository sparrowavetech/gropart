<?php echo Theme::partial('header'); ?>


<div id="main-content">
    <?php echo Theme::partial('page-header', [
        'size' => Theme::get('containerSize', 'xl'),
        'withTitle' => Theme::get('withTitle', true),
    ]); ?>

    <div class="container-<?php echo e(Theme::get('containerSize', 'xl')); ?>">
        <div class="mb-5">
            <?php echo Theme::content(); ?>

        </div>
    </div>
</div>

<?php echo Theme::partial('footer'); ?>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/layouts/default.blade.php ENDPATH**/ ?>