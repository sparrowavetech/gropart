<?php if($showStart): ?>
    <?php echo Form::open(Arr::except($formOptions, ['template'])); ?>

<?php endif; ?>

<?php if (isset($component)) { $__componentOriginal0a39fdd16f40824788edeed4e460894f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0a39fdd16f40824788edeed4e460894f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'cbdd3a194c072fbc3f04e785bd9366eb::section','data' => ['title' => $formOptions['section_title'] ?? '','description' => $formOptions['section_description'] ?? '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core-setting::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($formOptions['section_title'] ?? ''),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($formOptions['section_description'] ?? '')]); ?>
    <?php if($showFields): ?>
        <?php echo e($form->getOpenWrapperFormColumns()); ?>


        <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(in_array($field->getName(), $exclude)) continue; ?>

            <?php echo $field->render(); ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php echo e($form->getCloseWrapperFormColumns()); ?>

    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0a39fdd16f40824788edeed4e460894f)): ?>
<?php $attributes = $__attributesOriginal0a39fdd16f40824788edeed4e460894f; ?>
<?php unset($__attributesOriginal0a39fdd16f40824788edeed4e460894f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0a39fdd16f40824788edeed4e460894f)): ?>
<?php $component = $__componentOriginal0a39fdd16f40824788edeed4e460894f; ?>
<?php unset($__componentOriginal0a39fdd16f40824788edeed4e460894f); ?>
<?php endif; ?>

<?php echo $form->getActionButtons(); ?>


<?php if($showEnd): ?>
    <?php echo Form::close(); ?>

<?php endif; ?>

<?php if($form->getValidatorClass()): $__env->startPush( 'footer'); ?>
<?php echo $form->renderValidatorJs(); ?>

<?php $__env->stopPush(); endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/setting/resources/views/forms/form-content-only.blade.php ENDPATH**/ ?>