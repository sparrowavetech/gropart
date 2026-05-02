<?php if (! $__env->hasRenderedOnce('a770fc18-c7ff-4292-baaf-574de3dbb1c8')): $__env->markAsRenderedOnce('a770fc18-c7ff-4292-baaf-574de3dbb1c8'); ?>
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
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/notification/notification.blade.php ENDPATH**/ ?>