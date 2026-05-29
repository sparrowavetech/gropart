<?php if (isset($component)) { $__componentOriginal267ae10e99f5147c684b59e06e741a86 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal267ae10e99f5147c684b59e06e741a86 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::layouts.base','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::layouts.base'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('core/base::layouts.' . AdminAppearance::getCurrentLayout() . '.partials.before-content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'page-wrapper',
        'rv-media-integrate-wrapper' => Route::currentRouteName() === 'media.index',
    ]); ?>">
        <?php echo $__env->make('core/base::layouts.partials.page-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="page-body page-content">
            <div class="<?php echo e(AdminAppearance::getContainerWidth()); ?>">
                <?php echo apply_filters('core_layout_before_content', null); ?>


                <?php echo $__env->yieldContent('content'); ?>

                <?php echo apply_filters('core_layout_after_content', null); ?>

            </div>
        </main>

        <?php echo $__env->make('core/base::layouts.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php echo $__env->make('core/base::layouts.' . AdminAppearance::getCurrentLayout() . '.partials.after-content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

     <?php $__env->slot('headerLayout', null, []); ?> 
        <?php if(\Botble\Base\Supports\Core::make()->isSkippedLicenseReminder()): ?>
            <?php echo $__env->make('core/base::system.license-invalid', ['hidden' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

     <?php $__env->slot('footer', null, []); ?> 
        <?php echo $__env->make('core/base::global-search.form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('core/media::partials.media', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo rescue(fn() => app(Tighten\Ziggy\BladeRouteGenerator::class)->generate(), report: false); ?>


        <?php if(App::hasDebugModeEnabled()): ?>
            <?php if (isset($component)) { $__componentOriginalcb3f7f9971af62e57d3caf8f09dc093c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb3f7f9971af62e57d3caf8f09dc093c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::debug-badge','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::debug-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb3f7f9971af62e57d3caf8f09dc093c)): ?>
<?php $attributes = $__attributesOriginalcb3f7f9971af62e57d3caf8f09dc093c; ?>
<?php unset($__attributesOriginalcb3f7f9971af62e57d3caf8f09dc093c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb3f7f9971af62e57d3caf8f09dc093c)): ?>
<?php $component = $__componentOriginalcb3f7f9971af62e57d3caf8f09dc093c; ?>
<?php unset($__componentOriginalcb3f7f9971af62e57d3caf8f09dc093c); ?>
<?php endif; ?>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal267ae10e99f5147c684b59e06e741a86)): ?>
<?php $attributes = $__attributesOriginal267ae10e99f5147c684b59e06e741a86; ?>
<?php unset($__attributesOriginal267ae10e99f5147c684b59e06e741a86); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal267ae10e99f5147c684b59e06e741a86)): ?>
<?php $component = $__componentOriginal267ae10e99f5147c684b59e06e741a86; ?>
<?php unset($__componentOriginal267ae10e99f5147c684b59e06e741a86); ?>
<?php endif; ?>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/layouts/master.blade.php ENDPATH**/ ?>