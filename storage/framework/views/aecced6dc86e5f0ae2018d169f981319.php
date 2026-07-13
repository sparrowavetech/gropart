<?php if (isset($component)) { $__componentOriginal8a03368ec6e49e00ad030dd0f1968073 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a03368ec6e49e00ad030dd0f1968073 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '766be9a31b02d85b4410065d426a1ede::fronts.ajax-search.index','data' => ['class' => 'form--quick-search bb-form-quick-search w-100']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('plugins-ecommerce::fronts.ajax-search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form--quick-search bb-form-quick-search w-100']); ?>
    <div class="search-inner-content">
        <div class="text-search">
            <div class="search-wrapper">
                <?php if (isset($component)) { $__componentOriginald7d73f83e04d5f260717ce3bbffc01d3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald7d73f83e04d5f260717ce3bbffc01d3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '766be9a31b02d85b4410065d426a1ede::fronts.ajax-search.input','data' => ['type' => 'text','class' => 'search-field input-search-product']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('plugins-ecommerce::fronts.ajax-search.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'text','class' => 'search-field input-search-product']); ?>
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
            </div>
            <a
                class="close-search-panel close-toggle--sidebar"
                href="#"
                aria-label="Search"
            >
                <span class="svg-icon">
                    <svg>
                        <use
                            href="#svg-icon-times"
                            xlink:href="#svg-icon-times"
                        ></use>
                    </svg>
                </span>
            </a>
        </div>
    </div>
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
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/footer-search-ecommerce.blade.php ENDPATH**/ ?>