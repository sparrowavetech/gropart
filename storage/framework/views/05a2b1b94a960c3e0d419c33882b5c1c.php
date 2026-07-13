<ul <?php echo $options; ?>>
    <?php $__currentLoopData = $menu_nodes->loadMissing('metadata'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li
            <?php if($row->css_class || $row->active): ?> class="<?php if($row->css_class): ?> <?php echo e($row->css_class); ?> <?php endif; ?>
            <?php if($row->active): ?> current <?php endif; ?>"
            <?php endif; ?>>
            <a
                href="<?php echo e(url($row->url)); ?>"
                <?php if($row->target !== '_self'): ?> target="<?php echo e($row->target); ?>" <?php endif; ?>
            >
                <?php echo $row->icon_html; ?><span><?php echo e($row->title); ?></span>
            </a>
            <?php if($row->has_child): ?>
                <?php echo Menu::generateMenu([
                    'menu' => $menu,
                    'menu_nodes' => $row->child,
                ]); ?>

            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/menu-default.blade.php ENDPATH**/ ?>