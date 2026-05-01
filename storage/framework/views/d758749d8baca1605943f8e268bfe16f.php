<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li
                class="<?php echo \Illuminate\Support\Arr::toCssClasses(['breadcrumb-item', 'active' => $loop->last]); ?>"
                <?php if($loop->last): ?> aria-current="page" <?php endif; ?>
            >
                <?php if($item['url'] && !$loop->last): ?>
                    <a
                        class="mb-0 d-inline-block fs-6 lh-1"
                        href="<?php echo e($item['url']); ?>"
                    ><?php echo $item['label']; ?></a>
                <?php else: ?>
                    <h1 class="mb-0 d-inline-block fs-6 lh-1"><?php echo e(Str::limit($item['label'], 60)); ?></h1>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</nav>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/breadcrumb.blade.php ENDPATH**/ ?>