<?php $__env->startSection('content'); ?>
    <?php if(session('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-alert-triangle'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                <div>
                    <strong><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.access_denied')); ?></strong>
                    <p class="mb-0 mt-1"><?php echo e(session('warning')); ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.title')); ?></h4>
                </div>
                <div class="card-body">
                    <?php if($isLicenseVerified && $licenseData): ?>
                        <?php echo $__env->make('plugins/loyalty-points::license.partials.activated', ['licenseData' => $licenseData], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php else: ?>
                        <?php echo $__env->make('plugins/loyalty-points::license.partials.form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.help_title')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.what_is_purchase_code')); ?></h6>
                        <p class="text-muted small"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.purchase_code_description')); ?></p>
                    </div>

                    <div class="mb-3">
                        <h6><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.where_to_find')); ?></h6>
                        <p class="text-muted small"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.find_purchase_code_description')); ?></p>
                        <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code"
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <?php echo e(trans('plugins/loyalty-points::loyalty-points.license.learn_more')); ?>

                        </a>
                    </div>

                    <div class="mb-3">
                        <h6><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.license_terms')); ?></h6>
                        <p class="text-muted small"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.license_terms_description')); ?></p>
                        <a href="https://codecanyon.net/licenses/standard"
                           target="_blank" class="btn btn-sm btn-outline-secondary">
                            <?php echo e(trans('plugins/loyalty-points::loyalty-points.license.view_license_terms')); ?>

                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('footer'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('vendor/core/plugins/loyalty-points/css/license-activation.css')); ?>?v=<?php echo e(\Botble\LoyaltyPoints\Plugin::ASSETS_VERSION); ?>">

    <script>
    window.loyaltyTranslations = window.loyaltyTranslations || {};
    window.loyaltyTranslations = {
        somethingWentWrong: '<?php echo e(trans("plugins/loyalty-points::loyalty-points.js.something_went_wrong")); ?>',
        deactivateLicenseConfirm: '<?php echo e(trans("plugins/loyalty-points::loyalty-points.js.deactivate_license_confirm")); ?>',
        showPurchaseCode: '<?php echo e(trans("plugins/loyalty-points::loyalty-points.js.show_purchase_code")); ?>',
        hidePurchaseCode: '<?php echo e(trans("plugins/loyalty-points::loyalty-points.js.hide_purchase_code")); ?>'
    };
    </script>

    <script src="<?php echo e(asset('vendor/core/plugins/loyalty-points/js/license-activation.js')); ?>?v=<?php echo e(\Botble\LoyaltyPoints\Plugin::ASSETS_VERSION); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(BaseHelper::getAdminMasterLayoutTemplate(), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp82\htdocs\gropart\platform\plugins\loyalty-points\/resources/views/license/index.blade.php ENDPATH**/ ?>