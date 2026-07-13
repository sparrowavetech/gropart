<div class="widget-product-deals-day py-5">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <div class="col-auto">
                        <h2 class="mb-0 py-2"><?php echo $shortcode->title ? BaseHelper::clean($shortcode->title) : BaseHelper::clean($flashSale->name); ?></h2>
                        <?php if(!empty($subtitle)): ?>
                            <p class="mb-0"><?php echo e($subtitle); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="ps-4 col-auto py-2 d-none d-md-block">
                        <a href="<?php echo e(route('public.products')); ?>?discounted_only=1">
                            <span class="link-text"><?php echo e(__('All Offers')); ?>

                                <span class="svg-icon">
                                    <svg>
                                        <use
                                            href="#svg-icon-chevron-right"
                                            xlink:href="#svg-icon-chevron-right"
                                        ></use>
                                    </svg>
                                </span>
                            </span>
                        </a>
                    </div>
                    <div class="countdown-wrapper col-auto ps-md-5 py-2">
                        <div class="header-countdown row align-items-center justify-content-center gx-2">
                            <div class="ends-text col-auto">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="svg-icon me-2">
                                        <i class="icon icon-speed-fast"></i>
                                    </span><?php echo e(__('Expires in')); ?>:
                                </div>
                            </div>
                            <div
                                class="expire-countdown col-auto"
                                data-expire="<?php echo e(Carbon\Carbon::now()->diffInSeconds($flashSale->end_date)); ?>"
                            >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-deals-day__body arrows-top-right">
                    <div
                        class="product-deals-day-body slick-slides-carousel"
                        data-slick="<?php echo e(json_encode([
                            'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                            'appendArrows' => '.arrows-wrapper',
                            'arrows' => true,
                            'dots' => false,
                            'autoplay' => $shortcode->is_autoplay == 'yes',
                            'infinite' => $shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes',
                            'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options())
                                ? $shortcode->autoplay_speed
                                : 3000,
                            'speed' => 800,
                            'slidesToShow' => 6,
                            'slidesToScroll' => 1,
                            'swipeToSlide' => true,
                            'responsive' => [
                                [
                                    'breakpoint' => 1400,
                                    'settings' => [
                                        'slidesToShow' => 5,
                                    ],
                                ],
                                [
                                    'breakpoint' => 1199,
                                    'settings' => [
                                        'slidesToShow' => 4,
                                    ],
                                ],
                                [
                                    'breakpoint' => 1024,
                                    'settings' => [
                                        'slidesToShow' => 3,
                                    ],
                                ],
                                [
                                    'breakpoint' => 767,
                                    'settings' => [
                                        'arrows' => true,
                                        'dots' => false,
                                        'slidesToShow' => 2,
                                        'slidesToScroll' => 2,
                                    ],
                                ],
                            ],
                        ])); ?>"
                    >
                        <?php $__currentLoopData = $flashSale->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(!EcommerceHelper::showOutOfStockProducts() && $product->isOutOfStock()) continue; ?>
                            <div class="product-inner">
                                <?php echo Theme::partial('ecommerce.product-item', compact('product', 'flashSale', 'isFlashSale', 'wishlistIds')); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="arrows-wrapper"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/shortcodes/ecommerce/flash-sale.blade.php ENDPATH**/ ?>