<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('core/setting::forms.form-content-only', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($layout ?? BaseHelper::getAdminMasterLayoutTemplate(), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp82\htdocs\gropart\platform\core\setting\/resources/views/forms/form.blade.php ENDPATH**/ ?>