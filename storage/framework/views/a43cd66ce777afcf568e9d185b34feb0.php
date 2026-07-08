<?php if (isset($component)) { $__componentOriginaldaa3077dcea8104eb7236c8018937633 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldaa3077dcea8104eb7236c8018937633 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::navbar.badge-count','data' => ['class' => $class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::navbar.badge-count'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($class)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldaa3077dcea8104eb7236c8018937633)): ?>
<?php $attributes = $__attributesOriginaldaa3077dcea8104eb7236c8018937633; ?>
<?php unset($__attributesOriginaldaa3077dcea8104eb7236c8018937633); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldaa3077dcea8104eb7236c8018937633)): ?>
<?php $component = $__componentOriginaldaa3077dcea8104eb7236c8018937633; ?>
<?php unset($__componentOriginaldaa3077dcea8104eb7236c8018937633); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/partials/navbar/badge-count.blade.php ENDPATH**/ ?>