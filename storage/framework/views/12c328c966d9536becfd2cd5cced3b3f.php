<div class="alert alert-warning">
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
            <strong><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.activation_required')); ?></strong>
            <p class="mb-0 mt-1"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.description')); ?></p>
        </div>
    </div>
</div>

<form id="license-activation-form" data-action="<?php echo e(route('loyalty-points.license.activate')); ?>">
    <div class="mb-4">
        <label for="purchase_code" class="form-label">
            <?php echo e(trans('plugins/loyalty-points::loyalty-points.license.purchase_code_label')); ?>

            <span class="text-danger">*</span>
        </label>
        <input type="text"
               class="form-control form-control-lg"
               id="purchase_code"
               name="purchase_code"
               placeholder="<?php echo e(trans('plugins/loyalty-points::loyalty-points.license.purchase_code_placeholder')); ?>"
               required>
        <div class="form-text">
            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-info-circle'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-1']); ?>
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
            <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code" target="_blank">
                <?php echo e(trans('plugins/loyalty-points::loyalty-points.license.purchase_code_helper')); ?>

            </a>
        </div>
    </div>

    <div class="mb-4">
        <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   id="license_rules_agreement"
                   name="license_rules_agreement"
                   required>
            <label class="form-check-label" for="license_rules_agreement">
                <?php echo e(trans('plugins/loyalty-points::loyalty-points.license.agreement_text')); ?>

                <a href="https://codecanyon.net/licenses/standard" target="_blank" rel="nofollow">
                    <?php echo e(trans('plugins/loyalty-points::loyalty-points.license.more_info')); ?>

                </a>.
            </label>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <button type="submit" class="btn btn-primary btn-lg" id="activate-license-btn">
            <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-key'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
            <?php echo e(trans('plugins/loyalty-points::loyalty-points.license.activate')); ?>

        </button>

        <div class="text-muted small">
            <?php echo e(trans('plugins/loyalty-points::loyalty-points.license.secure_activation')); ?>

        </div>
    </div>
</form>

<hr class="my-4">

<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.need_help')); ?></h6>
        <p class="small text-muted"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.need_help_description')); ?></p>
    </div>
    <div class="col-md-6">
        <h6 class="text-muted"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.reset_license')); ?></h6>
        <p class="small text-muted"><?php echo e(trans('plugins/loyalty-points::loyalty-points.license.need_reset')); ?></p>
    </div>
</div>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\plugins\loyalty-points\/resources/views/license/partials/form.blade.php ENDPATH**/ ?>