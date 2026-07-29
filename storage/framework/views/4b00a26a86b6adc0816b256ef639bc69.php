<?php if (! $__env->hasRenderedOnce('cf37fda1-d270-4e61-a691-2558141f9232')): $__env->markAsRenderedOnce('cf37fda1-d270-4e61-a691-2558141f9232'); ?>
    <div
        class="offcanvas offcanvas-end"
        tabindex="-1"
        id="notification-sidebar"
        aria-labelledby="notification-sidebar-label"
        data-url="<?php echo e(route('notifications.index')); ?>"
        data-count-url="<?php echo e(route('notifications.count-unread')); ?>"
    >
        <button
            type="button"
            class="btn-close text-reset"
            data-bs-dismiss="offcanvas"
            aria-label="Close"
        ></button>

        <div class="notification-content"></div>
    </div>

    <script src="<?php echo e(asset('vendor/core/core/base/js/notification.js')); ?>"></script>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/notification/notification.blade.php ENDPATH**/ ?>