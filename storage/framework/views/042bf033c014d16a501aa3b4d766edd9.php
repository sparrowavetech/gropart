<?php if (isset($component)) { $__componentOriginal55fef500914e635eef22b52cddd65ca8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal55fef500914e635eef22b52cddd65ca8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::form.code-editor','data' => ['name' => $name,'attributes' => new Illuminate\View\ComponentAttributeBag((array) $attributes)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::form.code-editor'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(new Illuminate\View\ComponentAttributeBag((array) $attributes))]); ?>
    <?php echo e($value); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal55fef500914e635eef22b52cddd65ca8)): ?>
<?php $attributes = $__attributesOriginal55fef500914e635eef22b52cddd65ca8; ?>
<?php unset($__attributesOriginal55fef500914e635eef22b52cddd65ca8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal55fef500914e635eef22b52cddd65ca8)): ?>
<?php $component = $__componentOriginal55fef500914e635eef22b52cddd65ca8; ?>
<?php unset($__componentOriginal55fef500914e635eef22b52cddd65ca8); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/forms/partials/code-editor.blade.php ENDPATH**/ ?>