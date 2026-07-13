<form
    class="cart-form"
    action="<?php echo e(route('public.cart.add-to-cart')); ?>"
    method="POST"
>
    <?php echo csrf_field(); ?>
    <?php if(!empty($withVariations) && $product->variations->count()): ?>
        <div class="pr_switch_wrap">
            <?php echo render_product_swatches($product, [
                'selected' => $selectedAttrs,
            ]); ?>

        </div>
    <?php endif; ?>

    <?php if(isset($withProductOptions) && $withProductOptions): ?>
        <?php echo render_product_options($product); ?>

    <?php endif; ?>

    <input
        class="hidden-product-id"
        name="id"
        type="hidden"
        value="<?php echo e($product->is_variation || !$product->defaultVariation->product_id ? $product->id : $product->defaultVariation->product_id); ?>"
    />

    <?php if(EcommerceHelper::isCartEnabled() || !empty($withButtons)): ?>
        <?php echo apply_filters(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, null, $product); ?>

        <div class="product-button">
            <?php if(EcommerceHelper::isCartEnabled()): ?>
                <?php if($product->has_variation && empty($isQuickShop) && empty($withButtons)): ?>
                    <button
                        class="btn btn-primary mb-2 js-quick-shop-button w-100 <?php if($product->isOutOfStock()): ?> disabled <?php endif; ?>"
                        type="button"
                        data-url="<?php echo e(route('public.ajax.quick-shop', $product->slug)); ?>"
                        title="<?php echo e(__('Select Options')); ?>"
                        <?php if($product->isOutOfStock()): ?> disabled <?php endif; ?>
                    >
                        <span class="svg-icon">
                            <svg>
                                <use
                                    href="#svg-icon-cart"
                                    xlink:href="#svg-icon-cart"
                                ></use>
                            </svg>
                        </span>
                        <span class="add-to-cart-text ms-2"><?php echo e(__('Select Options')); ?></span>
                    </button>
                <?php else: ?>
                    <?php if(! empty($withButtons) || ! empty($isQuickShop)): ?>
                        <?php echo Theme::partial('ecommerce.product-quantity', compact('product')); ?>

                    <?php endif; ?>
                    <button
                        class="btn btn-primary mb-2 add-to-cart-button <?php if($product->isOutOfStock()): ?> disabled <?php endif; ?>"
                        name="add_to_cart"
                        type="submit"
                        value="<?php echo e($product->min_cart_quantity); ?>"
                        title="<?php echo e(__('Add to cart')); ?>"
                        <?php if($product->isOutOfStock()): ?> disabled <?php endif; ?>
                    >
                        <span class="svg-icon">
                            <svg>
                                <use
                                    href="#svg-icon-cart"
                                    xlink:href="#svg-icon-cart"
                                ></use>
                            </svg>
                        </span>
                        <span class="add-to-cart-text ms-2"><?php echo e(__('Add to cart')); ?></span>
                    </button>
                <?php endif; ?>

                <?php if(EcommerceHelper::isQuickBuyButtonEnabled() && isset($withBuyNow) && $withBuyNow): ?>
                    <button
                        class="btn btn-primary btn-black mb-2 add-to-cart-button <?php if($product->isOutOfStock()): ?> disabled <?php endif; ?>"
                        name="checkout"
                        type="submit"
                        value="<?php echo e($product->min_cart_quantity); ?>"
                        title="<?php echo e(__('Buy Now')); ?>"
                        <?php if($product->isOutOfStock()): ?> disabled <?php endif; ?>
                    >
                        <span class="add-to-cart-text ms-2"><?php echo e(__('Buy Now')); ?></span>
                    </button>
                <?php endif; ?>
            <?php endif; ?>
            <?php if(!empty($withButtons)): ?>
                <?php echo Theme::partial('ecommerce.product-loop-buttons', compact('product', 'wishlistIds')); ?>

            <?php endif; ?>
        </div>
    <?php endif; ?>
</form>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/ecommerce/product-cart-form.blade.php ENDPATH**/ ?>