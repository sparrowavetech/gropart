<?php if (! $__env->hasRenderedOnce('0b9b1aa0-114a-42b8-8ff9-55d329483393')): $__env->markAsRenderedOnce('0b9b1aa0-114a-42b8-8ff9-55d329483393'); ?>
    <?php
        $successIcon = theme_option('toast_success_icon');
        $errorIcon = theme_option('toast_error_icon');
    ?>
    <script>
        window.ThemeToastConfig = {
            position: <?php echo json_encode(theme_option('toast_position', 'bottom'), 512) ?>,
            alignment: <?php echo json_encode(theme_option('toast_alignment', 'right'), 512) ?>,
            offsetX: <?php echo json_encode((int) theme_option('toast_offset_x', 15), 512) ?>,
            offsetY: <?php echo json_encode((int) theme_option('toast_offset_y', 15), 512) ?>,
            timeout: <?php echo json_encode((int) theme_option('toast_timeout', 5000), 512) ?>,
            successIcon: <?php echo json_encode($successIcon ? BaseHelper::renderIcon($successIcon) : '', 15, 512) ?>,
            errorIcon: <?php echo json_encode($errorIcon ? BaseHelper::renderIcon($errorIcon) : '', 15, 512) ?>
        };
    </script>
    <script src="<?php echo e(asset('vendor/core/packages/theme/js/toast.js')); ?>?v=<?php echo e(get_cms_version()); ?>"></script>

    <?php if(session()->has('success_msg') ||
            session()->has('error_msg') ||
            (isset($errors) && $errors->count() > 0) ||
            isset($error_msg)): ?>
        <script type="text/javascript">
            window.addEventListener('load', function() {
                <?php if(session()->has('success_msg')): ?>
                Theme.showSuccess(<?php echo json_encode(session('success_msg'), 15, 512) ?>);
                <?php endif; ?>

                <?php if(session()->has('error_msg')): ?>
                Theme.showError(<?php echo json_encode(session('error_msg'), 15, 512) ?>);
                <?php endif; ?>

                <?php if(isset($error_msg)): ?>
                Theme.showError(<?php echo json_encode($error_msg, 15, 512) ?>);
                <?php endif; ?>

                <?php if(isset($errors)): ?>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                Theme.showError(<?php echo json_encode($error, 15, 512) ?>);
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/packages/theme/resources/views/fronts/toast-notification.blade.php ENDPATH**/ ?>