<?php
    $menu = Menu::generateMenu([
        'slug' => $config['menu_id'],
        'options' => ['class' => 'ps-0'],
        'view' => 'menu-default',
    ]);
?>

<?php if($menu): ?>
    <div class="col-xl-2">
        <div class="col mb-5">
            <div class="widget widget-custom-menu">
                <p class="h5 fw-bold widget-title mb-4"><?php echo e($config['name']); ?></p>
                <?php echo $menu; ?>

            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/////widgets/custom-menu/templates/frontend.blade.php ENDPATH**/ ?>