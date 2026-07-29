<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php $__currentLoopData = $breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $breadcrumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li
                class="<?php echo \Illuminate\Support\Arr::toCssClasses(['breadcrumb-item', 'active' => $loop->last]); ?>"
                <?php if($loop->last): ?> aria-current="page" <?php endif; ?>
            >
                <?php if($breadcrumb->url && !$loop->last): ?>
                    <a
                        class="mb-0 d-inline-block fs-6 lh-1"
                        href="<?php echo e($breadcrumb->url); ?>"
                    ><?php echo e($breadcrumb->title); ?></a>
                <?php else: ?>
                    <h1 class="mb-0 d-inline-block fs-6 lh-1"><?php echo e(Str::limit($breadcrumb->title, 60)); ?></h1>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</nav>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/layouts/partials/breadcrumbs.blade.php ENDPATH**/ ?>