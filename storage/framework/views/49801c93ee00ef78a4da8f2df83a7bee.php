<?php
    $slick = [
        'rtl' => BaseHelper::isRtlEnabled(),
        'appendArrows' => '.arrows-wrapper',
        'arrows' => true,
        'dots' => false,
        'autoplay' => $shortcode->is_autoplay == 'yes',
        'infinite' => $shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes',
        'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options()) ? $shortcode->autoplay_speed : 3000,
        'speed' => 800,
        'slidesToShow' => 8,
        'slidesToScroll' => 1,
        'responsive' => [
            [
                'breakpoint' => 1700,
                'settings' => [
                    'slidesToShow' => 7,
                ],
            ],
            [
                'breakpoint' => 1500,
                'settings' => [
                    'slidesToShow' => 6,
                ],
            ],
            [
                'breakpoint' => 1199,
                'settings' => [
                    'slidesToShow' => 5,
                ],
            ],
            [
                'breakpoint' => 1024,
                'settings' => [
                    'slidesToShow' => 4,
                ],
            ],
            [
                'breakpoint' => 767,
                'settings' => [
                    'arrows' => false,
                    'dots' => true,
                    'slidesToShow' => 2,
                    'slidesToScroll' => 2,
                ],
            ],
        ],
    ];
?>
<?php if($categories->isNotEmpty()): ?>
    <div class="widget-product-categories pt-5 pb-2">
        <div class="container-xxxl">
            <div class="row">
                <div class="col-12">
                    <div class="row align-items-center mb-2 widget-header">
                        <div class="col-auto">
                            <h2 class="mb-0 py-2"><?php echo e($shortcode->title); ?></h2>
                            <?php if($shortcode->subtitle): ?>
                                <p class="mb-0"><?php echo e($shortcode->subtitle); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-categories-body pb-4 arrows-top-right">
                        <div
                            class="product-categories-box slick-slides-carousel"
                            data-slick="<?php echo e(json_encode($slick)); ?>"
                        >
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="product-category-item p-3">
                                    <div class="category-item-body p-3">
                                        <a
                                            class="d-block"
                                            href="<?php echo e(route('public.single', $item->url)); ?>"
                                        >
                                            <div class="category__thumb img-fluid-eq mb-3">
                                                <div class="img-fluid-eq__dummy"></div>
                                                <div class="img-fluid-eq__wrap">
                                                    <img
                                                        class="mx-auto"
                                                        src="<?php echo e(RvMedia::getImageUrl($item->image, 'small', false, RvMedia::getDefaultImage())); ?>"
                                                        alt="icon <?php echo e($item->name); ?>"
                                                    />
                                                </div>
                                            </div>
                                            <div class="category__text text-center py-2 text-truncate">
                                                <span class="category__name"><?php echo e($item->name); ?></span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="arrows-wrapper"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/shortcodes/ecommerce/featured-product-categories.blade.php ENDPATH**/ ?>