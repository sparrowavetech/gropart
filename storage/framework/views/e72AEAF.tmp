<?php
    /** @var \Botble\Table\Abstracts\TableAbstract $table */
    /** @var \Botble\Table\Abstracts\TableActionAbstract[] $actions */
    /** @var \Illuminate\Database\Eloquent\Model $model */

    $renderedActions = collect($actions)
        ->map(fn ($action) => ['action' => $action, 'html' => (string) $action->setItem($model)])
        ->filter(fn ($item) => $item['html'] !== '');

    $visibleCount = $renderedActions->count();
    $showAsDropdown = $table->hasDisplayActionsAsDropdown()
        && $visibleCount > $table->getDisplayActionsAsDropdownWhenActionsMoresThan();
?>

<div class="table-actions">
    <?php if(!$showAsDropdown): ?>
        <?php $__currentLoopData = $renderedActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo $item['html']; ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <div class="dropdown">
            <button
                class="btn dropdown-toggle"
                type="button"
                id="<?php echo e($id = sprintf('dropdown-actions-%s-%s', md5($model::class), $model->getKey())); ?>"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
            >
                <?php echo e(trans('core/base::tables.action')); ?>

            </button>
            <div
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="<?php echo e($id); ?>"
            >
                <?php $__currentLoopData = $renderedActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($item['action']->setItem($model)->displayAsDropdownItem()); ?>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\table\/resources/views/row-actions.blade.php ENDPATH**/ ?>