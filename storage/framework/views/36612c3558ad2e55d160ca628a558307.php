<?php
    $values = Arr::wrap($values ?? []);

    $attributes = (array) $attributes;

    $multiple = count($values) > 1;
?>

<div class="position-relative form-check-group">
    <?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            if ($multiple && isset($attributes['id'])) {
                $attributes['id'] = $attributes['id'] . '_' . $key;
            }
        ?>

        <?php if (isset($component)) { $__componentOriginal9166fadad4bef341ac183689b0de8726 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9166fadad4bef341ac183689b0de8726 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::form.radio','data' => ['name' => $name,'value' => $key,'checked' => $key == $selected,'attributes' => new Illuminate\View\ComponentAttributeBag($attributes)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::form.radio'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($key),'checked' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($key == $selected),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(new Illuminate\View\ComponentAttributeBag($attributes))]); ?>
            <?php echo e($option); ?>

         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9166fadad4bef341ac183689b0de8726)): ?>
<?php $attributes = $__attributesOriginal9166fadad4bef341ac183689b0de8726; ?>
<?php unset($__attributesOriginal9166fadad4bef341ac183689b0de8726); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9166fadad4bef341ac183689b0de8726)): ?>
<?php $component = $__componentOriginal9166fadad4bef341ac183689b0de8726; ?>
<?php unset($__componentOriginal9166fadad4bef341ac183689b0de8726); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/forms/partials/custom-radio.blade.php ENDPATH**/ ?>