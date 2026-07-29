<?php
    /** @var Botble\Table\Actions\Action $action */
?>

<<?php echo e($action->getType()); ?> <?php echo $__env->make('core/table::actions.includes.action-attributes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>>
    <?php echo $__env->make('core/table::actions.includes.action-icon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['sr-only' => $action->hasIcon() && $action->isIconOnly()]); ?>"><?php echo e($action->getLabel()); ?></span>
    </<?php echo e($action->getType()); ?>>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/table/resources/views/actions/action.blade.php ENDPATH**/ ?>