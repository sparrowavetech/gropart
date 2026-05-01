<ul class="<?php echo \Illuminate\Support\Arr::toCssClasses(['navbar-nav', $navbarClass ?? null]); ?>">
    <?php $__currentLoopData = DashboardMenu::getAll(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('core/base::layouts.partials.navbar-nav-item', [
            'menu' => $menu,
            'autoClose' => $autoClose,
            'isNav' => true,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/layouts/partials/navbar-nav.blade.php ENDPATH**/ ?>