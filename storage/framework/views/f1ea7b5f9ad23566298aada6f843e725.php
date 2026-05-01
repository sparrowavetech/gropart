<?php if (! $__env->hasRenderedOnce('8def7c66-1ae1-4db7-ac16-8b3f1e3383be')): $__env->markAsRenderedOnce('8def7c66-1ae1-4db7-ac16-8b3f1e3383be'); ?>
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