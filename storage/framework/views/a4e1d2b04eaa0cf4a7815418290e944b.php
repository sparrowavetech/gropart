<div id="license-activation-form" data-action="<?php echo e(route('wholesale.license.activate')); ?>">
    <div class="mb-2">
        <label for="license_purchase_code" class="form-label small mb-1">
            <?php echo e(trans('plugins/ecommerce-wholesale::wholesale.license.purchase_code_label')); ?>

            <span class="text-danger">*</span>
            <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code" target="_blank" class="ms-1 text-muted">
                <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-help'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
            </a>
        </label>
        <input type="text"
               class="form-control form-control-sm"
               id="license_purchase_code"
               autocomplete="off"
               placeholder="<?php echo e(trans('plugins/ecommerce-wholesale::wholesale.license.purchase_code_placeholder')); ?>">
    </div>

    <div class="form-check mb-2">
        <input class="form-check-input"
               type="checkbox"
               id="license_rules_agreement">
        <label class="form-check-label small" for="license_rules_agreement">
            <?php echo e(trans('plugins/ecommerce-wholesale::wholesale.license.agreement_text')); ?>

        </label>
    </div>

    <button type="button" class="btn btn-primary btn-sm" id="activate-license-btn">
        <span class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
        <?php echo e(trans('plugins/ecommerce-wholesale::wholesale.license.activate')); ?>

    </button>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/ecommerce-wholesale/resources/views/settings/partials/license-form.blade.php ENDPATH**/ ?>