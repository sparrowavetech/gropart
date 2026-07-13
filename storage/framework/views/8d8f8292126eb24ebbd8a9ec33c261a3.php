<div class="product-thumbnail">
    <a
        class="product-loop__link img-fluid-eq"
        href="<?php echo e($product->url); ?>"
        tabindex="0"
    >
        <div class="img-fluid-eq__dummy"></div>
        <div class="img-fluid-eq__wrap">
            <img
                class="lazyload product-thumbnail__img"
                data-src="<?php echo e(RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage())); ?>"
                src="<?php echo e(image_placeholder($product->image, 'small')); ?>"
                alt="<?php echo e($product->name); ?>"
            >
        </div>
        <span class="ribbons">
            <?php if($product->isOutOfStock()): ?>
                <span class="ribbon out-stock"><?php echo e(__('Out Of Stock')); ?></span>
            <?php else: ?>
                <?php if($product->productLabels->isNotEmpty()): ?>
                    <?php $__currentLoopData = $product->productLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span
                            class="ribbon"
                            <?php echo $label->css_styles; ?>

                        ><?php echo e($label->name); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <?php if($product->front_sale_price !== $product->price): ?>
                        <div
                            class="featured ribbon"
                            dir="ltr"
                        ><?php echo e(get_sale_percentage($product->price, $product->front_sale_price)); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </span>
    </a>
    <?php echo Theme::partial(
        'ecommerce.product-loop-buttons',
        compact('product') + (!empty($wishlistIds) ? compact('wishlistIds') : []),
    ); ?>

</div>
<div class="product-details position-relative">
    <div class="product-content-box">
        <?php if(is_plugin_active('marketplace') && $product->store->id): ?>
            <div class="sold-by-meta">
                <a
                    href="<?php echo e($product->store->url); ?>"
                    tabindex="0"
                ><?php echo e($product->store->name); ?></a>
                <?php echo $product->store->badge; ?>

            </div>
        <?php endif; ?>
        <h3 class="product__title">
            <a
                href="<?php echo e($product->url); ?>"
                tabindex="0"
            ><?php echo e($product->name); ?></a>
        </h3>
        <?php if(EcommerceHelper::isReviewEnabled()): ?>
            <?php echo Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]); ?>

        <?php endif; ?>
        <?php echo Theme::partial('ecommerce.product-price', compact('product')); ?>

        <?php if(!empty($isFlashSale)): ?>
            <div class="deal-sold row mt-2">
                <?php if(Botble\Ecommerce\Facades\FlashSale::isShowSaleCountLeft()): ?>
                    <div class="deal-text col-auto">
                        <span class="sold fw-bold">
                            <?php if($product->pivot->quantity > $product->pivot->sold): ?>
                                <span class="text"><?php echo e(__('Sold')); ?>: </span>
                                <span class="value"><?php echo e((int) $product->pivot->sold); ?> /
                                    <?php echo e((int) $product->pivot->quantity); ?></span>
                            <?php else: ?>
                                <span class="text text-danger"><?php echo e(__('Sold out')); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
                <div class="deal-progress col">
                    <div class="progress">
                        <div
                            class="progress-bar"
                            role="progressbar"
                            aria-label="<?php echo e(__('Sold out')); ?>"
                            aria-valuenow="<?php echo e($product->pivot->quantity > 0 ? ($product->pivot->sold / $product->pivot->quantity) * 100 : 0); ?>"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            style="width: <?php echo e($product->pivot->quantity > 0 ? ($product->pivot->sold / $product->pivot->quantity) * 100 : 0); ?>%"
                        >
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
</div>
<div class="product-bottom-box">
    <?php echo Theme::partial('ecommerce.product-cart-form', compact('product')); ?>

</div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/ecommerce/product-item.blade.php ENDPATH**/ ?>