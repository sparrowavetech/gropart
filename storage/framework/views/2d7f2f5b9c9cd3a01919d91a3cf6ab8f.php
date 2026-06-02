<?php if($action->getType() === 'a'): ?>
    href="<?php echo e($action->hasUrl() ? $action->getUrl() : 'javascript:void(0);'); ?>"
<?php elseif($action->hasUrl()): ?>
    type="<?php echo e($action->getType()); ?>"
    data-url="<?php echo e($action->getUrl()); ?>"
<?php endif; ?>

<?php if($bsToggle = $action->getAttribute('data-bs-toggle')): ?>
    data-bs-toggle="<?php echo e($bsToggle); ?>"
<?php endif; ?>
<?php if($bsTarget = $action->getAttribute('data-bs-target')): ?>
    data-bs-target="<?php echo e($bsTarget); ?>"
<?php endif; ?>

<?php if($action->isAction()): ?>
    data-dt-single-action
    data-method="<?php echo e($action->getActionMethod()); ?>"
    <?php if($action->isConfirmation()): ?>
        data-confirmation-modal="<?php echo e($action->isConfirmation() ? 'true' : 'false'); ?>"
        data-confirmation-modal-title="<?php echo e($action->getConfirmationModalTitle()); ?>"
        data-confirmation-modal-message="<?php echo e($action->getConfirmationModalMessage()); ?>"
        data-confirmation-modal-button="<?php echo e($action->getConfirmationModalButton()); ?>"
        data-confirmation-modal-cancel-button="<?php echo e($action->getConfirmationModalCancelButton()); ?>"
    <?php endif; ?>
<?php elseif($action->shouldOpenUrlInNewTable()): ?>
    target="_blank"
<?php endif; ?>

<?php echo Html::attributes($attributes ?? $action->getAttributes()); ?>

<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\table\/resources/views/actions/includes/action-attributes.blade.php ENDPATH**/ ?>