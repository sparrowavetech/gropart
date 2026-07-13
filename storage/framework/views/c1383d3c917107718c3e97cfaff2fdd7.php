<?php
    if (!isset($groupedOptions)) {
        if (!$options instanceof \Illuminate\Support\Collection) {
            $options = collect($options);
        }

        $groupedOptions = $options->groupBy('parent_id');
    }

    $currentOptions = $groupedOptions->get($parentId = $parentId ?? 0);
?>

<?php if($currentOptions): ?>
    <?php $__currentLoopData = $currentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option
            value="<?php echo e($option->id); ?>"
            <?php if(is_array($selected) ? in_array($option->id, $selected) : $option->id == $selected): echo 'selected'; endif; ?>
        ><?php echo $indent; ?><?php echo e($option->name); ?></option>

        <?php if($groupedOptions->has($option->id)): ?>
            <?php echo $__env->make('core/base::forms.partials.nested-select-option', [
                'options' => $groupedOptions,
                'indent' => $indent . '&nbsp;&nbsp;',
                'parentId' => $option->id,
                'selected' => $selected,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/forms/partials/nested-select-option.blade.php ENDPATH**/ ?>