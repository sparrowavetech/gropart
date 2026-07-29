<?php if (isset($component)) { $__componentOriginalc4c45a5829bf328e150bca077cb9c1c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc4c45a5829bf328e150bca077cb9c1c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::form.color-picker','data' => ['name' => $name,'value' => $value ?: 'transparent','attributes' => new Illuminate\View\ComponentAttributeBag((array) $attributes)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::form.color-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value ?: 'transparent'),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(new Illuminate\View\ComponentAttributeBag((array) $attributes))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc4c45a5829bf328e150bca077cb9c1c1)): ?>
<?php $attributes = $__attributesOriginalc4c45a5829bf328e150bca077cb9c1c1; ?>
<?php unset($__attributesOriginalc4c45a5829bf328e150bca077cb9c1c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc4c45a5829bf328e150bca077cb9c1c1)): ?>
<?php $component = $__componentOriginalc4c45a5829bf328e150bca077cb9c1c1; ?>
<?php unset($__componentOriginalc4c45a5829bf328e150bca077cb9c1c1); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/forms/partials/color.blade.php ENDPATH**/ ?>