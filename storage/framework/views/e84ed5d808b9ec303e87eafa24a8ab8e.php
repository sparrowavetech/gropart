<?php if (isset($component)) { $__componentOriginal70c2ea61e73539201e401788110d6a79 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal70c2ea61e73539201e401788110d6a79 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'cbdd3a194c072fbc3f04e785bd9366eb::section.action.save','data' => ['form' => $form]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core-setting::section.action.save'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['form' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($form)]); ?>
    <?php echo $__env->yieldContent('content'); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal70c2ea61e73539201e401788110d6a79)): ?>
<?php $attributes = $__attributesOriginal70c2ea61e73539201e401788110d6a79; ?>
<?php unset($__attributesOriginal70c2ea61e73539201e401788110d6a79); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal70c2ea61e73539201e401788110d6a79)): ?>
<?php $component = $__componentOriginal70c2ea61e73539201e401788110d6a79; ?>
<?php unset($__componentOriginal70c2ea61e73539201e401788110d6a79); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/setting/resources/views/forms/partials/action.blade.php ENDPATH**/ ?>