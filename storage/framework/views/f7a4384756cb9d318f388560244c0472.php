<?php if($crumbs = Theme::breadcrumb()->getCrumbs()): ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <?php $__currentLoopData = $crumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $crumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(! $loop->last): ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo e($crumb['url']); ?>"><?php echo e($crumb['label']); ?></a>
                        <span class="extra-breadcrumb-name"></span>
                    </li>
                <?php else: ?>
                    <li
                        class="breadcrumb-item active"
                        aria-current="page"
                    >
                        <span><?php echo e($crumb['label']); ?></span>
                    </li>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    </nav>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/breadcrumbs.blade.php ENDPATH**/ ?>