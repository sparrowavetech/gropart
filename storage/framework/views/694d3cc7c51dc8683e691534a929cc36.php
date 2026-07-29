<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginal26a807ae0e75175dceaac1113c75830b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26a807ae0e75175dceaac1113c75830b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::panel-section','data' => ['id' => 'settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::panel-section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'settings']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26a807ae0e75175dceaac1113c75830b)): ?>
<?php $attributes = $__attributesOriginal26a807ae0e75175dceaac1113c75830b; ?>
<?php unset($__attributesOriginal26a807ae0e75175dceaac1113c75830b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26a807ae0e75175dceaac1113c75830b)): ?>
<?php $component = $__componentOriginal26a807ae0e75175dceaac1113c75830b; ?>
<?php unset($__componentOriginal26a807ae0e75175dceaac1113c75830b); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(BaseHelper::getAdminMasterLayoutTemplate(), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/setting/resources/views/index.blade.php ENDPATH**/ ?>