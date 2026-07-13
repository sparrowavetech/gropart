<?php if (isset($component)) { $__componentOriginal8a03368ec6e49e00ad030dd0f1968073 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a03368ec6e49e00ad030dd0f1968073 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '766be9a31b02d85b4410065d426a1ede::fronts.ajax-search.index','data' => ['class' => 'form--quick-search']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('plugins-ecommerce::fronts.ajax-search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form--quick-search']); ?>
    <div
        class="form-group--icon"
        style="display: none"
    >
        <div class="product-category-label">
            <label for="product-category-select" class="text"><?php echo e(__('All Categories')); ?></label>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-chevron-down"
                        xlink:href="#svg-icon-chevron-down"
                    ></use>
                </svg>
            </span>
        </div>
        <?php if (isset($component)) { $__componentOriginalb6909b0ea2309f5e9f16e68caf218b3c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb6909b0ea2309f5e9f16e68caf218b3c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '766be9a31b02d85b4410065d426a1ede::fronts.ajax-search.categories-dropdown','data' => ['class' => 'form-control product-category-select','id' => 'product-category-select']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('plugins-ecommerce::fronts.ajax-search.categories-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control product-category-select','id' => 'product-category-select']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb6909b0ea2309f5e9f16e68caf218b3c)): ?>
<?php $attributes = $__attributesOriginalb6909b0ea2309f5e9f16e68caf218b3c; ?>
<?php unset($__attributesOriginalb6909b0ea2309f5e9f16e68caf218b3c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb6909b0ea2309f5e9f16e68caf218b3c)): ?>
<?php $component = $__componentOriginalb6909b0ea2309f5e9f16e68caf218b3c; ?>
<?php unset($__componentOriginalb6909b0ea2309f5e9f16e68caf218b3c); ?>
<?php endif; ?>
    </div>
    <?php if (isset($component)) { $__componentOriginald7d73f83e04d5f260717ce3bbffc01d3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald7d73f83e04d5f260717ce3bbffc01d3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '766be9a31b02d85b4410065d426a1ede::fronts.ajax-search.input','data' => ['type' => 'text','class' => 'form-control input-search-product']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('plugins-ecommerce::fronts.ajax-search.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'text','class' => 'form-control input-search-product']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald7d73f83e04d5f260717ce3bbffc01d3)): ?>
<?php $attributes = $__attributesOriginald7d73f83e04d5f260717ce3bbffc01d3; ?>
<?php unset($__attributesOriginald7d73f83e04d5f260717ce3bbffc01d3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald7d73f83e04d5f260717ce3bbffc01d3)): ?>
<?php $component = $__componentOriginald7d73f83e04d5f260717ce3bbffc01d3; ?>
<?php unset($__componentOriginald7d73f83e04d5f260717ce3bbffc01d3); ?>
<?php endif; ?>
    <button
        class="btn"
        type="submit"
        aria-label="Submit"
    >
        <span class="svg-icon">
            <svg>
                <use
                    href="#svg-icon-search"
                    xlink:href="#svg-icon-search"
                ></use>
            </svg>
        </span>
    </button>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a03368ec6e49e00ad030dd0f1968073)): ?>
<?php $attributes = $__attributesOriginal8a03368ec6e49e00ad030dd0f1968073; ?>
<?php unset($__attributesOriginal8a03368ec6e49e00ad030dd0f1968073); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a03368ec6e49e00ad030dd0f1968073)): ?>
<?php $component = $__componentOriginal8a03368ec6e49e00ad030dd0f1968073; ?>
<?php unset($__componentOriginal8a03368ec6e49e00ad030dd0f1968073); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/header-search-ecommerce.blade.php ENDPATH**/ ?>