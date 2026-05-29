<?php
    $menu = apply_filters(BASE_FILTER_DASHBOARD_MENU, $menu);

    if (in_array($menu['id'], ['cms-core-settings', 'cms-core-system'], true)) {
        $menu['children'] = [];
    }

    $hasChildren = array_key_exists('children', $menu) && ($childrenCount = count($menu['children']));
    $children = $hasChildren ? $menu['children'] : [];

    $autoClose ??= 'outside';
    $align ??= 'start';
?>

<li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'nav-item',
    'active' => $menu['active'],
    'dropdown' => $hasChildren,
    $menu['class'] ?? null,
]); ?>">
    <?php echo $__env->make('core/base::layouts.partials.navbar-nav-item-link', [
        'menu' => $menu,
        'hasChildren' => $hasChildren,
        'autoClose' => $autoClose,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $alignClass = match ($align) {
            'start' => 'dropdown-menu-start',
            'end' => 'dropdown-menu-end',
            default => null,
        };
    ?>

    <?php if($hasChildren): ?>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'dropdown-menu animate slideIn',
            $alignClass,
            'show' => $menu['active'] && $autoClose === 'false',
        ]); ?>">
            <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    if (
                        in_array(
                            $child['id'],
                            ['cms-core-settings', 'cms-core-system', 'cms-core-platform-administration'],
                            true,
                        )
                    ) {
                        $child['children'] = [];
                    }

                    $childHasChildren = array_key_exists('children', $child) && count($child['children']);
                ?>

                <?php if($childHasChildren): ?>
                    <div class="dropdown">
                <?php endif; ?>

                <?php echo $__env->make('core/base::layouts.partials.navbar-nav-item-link', [
                    'menu' => $child,
                    'hasChildren' => $childHasChildren,
                    'autoClose' => $autoClose,
                    'isNav' => false,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php if($childHasChildren): ?>
                    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'dropdown-menu animate slideIn',
                        'show' => $child['active'] && $autoClose === 'false',
                    ]); ?>">
                        <?php $__currentLoopData = $child['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('core/base::layouts.partials.navbar-nav-item-link', [
                                'menu' => $childItem,
                                'hasChildren' => false,
                                'autoClose' => $autoClose,
                                'isNav' => false,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
        </div>
    <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>
</li>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/layouts/partials/navbar-nav-item.blade.php ENDPATH**/ ?>