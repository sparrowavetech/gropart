<?php if($showStart): ?>
    <?php echo Form::open(Arr::except($formOptions, ['template'])); ?>

<?php endif; ?>

<?php if($showFields): ?>
    <?php echo e($form->getOpenWrapperFormColumns()); ?>


    <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(in_array($field->getName(), $exclude)) continue; ?>

        <?php echo $field->render(); ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php echo e($form->getCloseWrapperFormColumns()); ?>

<?php endif; ?>

<?php if($showEnd): ?>
    <?php echo Form::close(); ?>

<?php endif; ?>

<?php if($form->getValidatorClass()): ?>
    <?php $__env->startPush('footer'); ?>
        <?php echo $form->renderValidatorJs(); ?>

    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/forms/form-content-only.blade.php ENDPATH**/ ?>