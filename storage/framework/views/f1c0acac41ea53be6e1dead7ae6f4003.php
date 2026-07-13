<select
    <?php echo e($attributes->merge(['name' => 'categories[]'])); ?>

    data-bb-toggle="init-categories-dropdown"
    data-url="<?php echo e(route('public.ajax.categories-dropdown')); ?>"
    aria-label="<?php echo e(__('Product categories')); ?>"
>
    <option value=""><?php echo e(__('All Categories')); ?></option>
</select>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/ecommerce/resources/views/components/fronts/ajax-search/categories-dropdown.blade.php ENDPATH**/ ?>