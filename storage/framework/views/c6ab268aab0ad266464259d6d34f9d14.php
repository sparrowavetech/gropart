<footer class="footer position-sticky footer-transparent d-print-none">
    <div class="<?php echo e(AdminAppearance::getContainerWidth()); ?>">
        <div class="text-start">
            <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-between">
                <div class="order-2 order-lg-1">
                    <?php echo $__env->make('core/base::partials.copyright', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="order-1 order-lg-2">
                    <?php if(defined('LARAVEL_START')): ?>
                        <?php echo e(trans('core/base::layouts.page_loaded_in_time', ['time' => round(microtime(true) - LARAVEL_START, 2)])); ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/layouts/partials/footer.blade.php ENDPATH**/ ?>