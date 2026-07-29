<div
    id="panel-section-item-<?php echo e($sectionId); ?>-<?php echo e($id); ?>"
    data-priority="<?php echo e($priority); ?>"
    data-id="<?php echo e($id); ?>"
    data-group-id="<?php echo e($sectionId); ?>"
    class="col-12 col-sm-6 col-md-4 panel-section-item panel-section-item-<?php echo e($id); ?> panel-section-item-priority-<?php echo e($priority); ?>"
>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'row g-3',
        'align-items-start' => $description,
        'align-items-center' => !$description,
    ]); ?>">
        <div class="col-auto">
            <div class="d-flex align-items-center justify-content-center panel-section-item-icon">
                <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => $icon ?: 'ti ti-box'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
            </div>
        </div>
        <div class="col">
            <div class="d-block mb-1 panel-section-item-title">
                <?php if($url): ?>
                    <a
                        class="text-decoration-none text-primary fw-bold"
                        href="<?php echo e($url); ?>"
                        <?php if($urlShouldOpenNewTab): ?> target="_blank" <?php endif; ?>
                    >
                <?php endif; ?>

                <?php echo e($title); ?>


                <?php if($url): ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if($description): ?>
                <div class="text-secondary mt-n1"><?php echo e($description); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/sections/item.blade.php ENDPATH**/ ?>