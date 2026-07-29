<?php if (isset($component)) { $__componentOriginal76cffb64887e6cc5d66bf632725c28d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76cffb64887e6cc5d66bf632725c28d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::form.image','data' => ['name' => $name,'value' => $value,'action' => 'select-image','attributes' => new Illuminate\View\ComponentAttributeBag((array) Arr::get($attributes, 'attr', []))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::form.image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value),'action' => 'select-image','attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(new Illuminate\View\ComponentAttributeBag((array) Arr::get($attributes, 'attr', [])))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal76cffb64887e6cc5d66bf632725c28d8)): ?>
<?php $attributes = $__attributesOriginal76cffb64887e6cc5d66bf632725c28d8; ?>
<?php unset($__attributesOriginal76cffb64887e6cc5d66bf632725c28d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal76cffb64887e6cc5d66bf632725c28d8)): ?>
<?php $component = $__componentOriginal76cffb64887e6cc5d66bf632725c28d8; ?>
<?php unset($__componentOriginal76cffb64887e6cc5d66bf632725c28d8); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/forms/partials/image.blade.php ENDPATH**/ ?>