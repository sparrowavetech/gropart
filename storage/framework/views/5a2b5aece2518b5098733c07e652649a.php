<?php
    $slick = [
        'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
        'appendArrows' => '.arrows-wrapper',
        'arrows' => true,
        'dots' => false,
        'autoplay' => $shortcode->is_autoplay == 'yes',
        'infinite' => $shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes',
        'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options()) ? $shortcode->autoplay_speed : 3000,
        'speed' => 800,
        'slidesToShow' => $shortcode->slides_to_show ?: 4,
        'slidesToScroll' => 1,
        'responsive' => [
            [
                'breakpoint' => 1024,
                'settings' => [
                    'slidesToShow' => ($shortcode->slides_to_show ?: 4) - 2,
                ],
            ],
            [
                'breakpoint' => 767,
                'settings' => [
                    'arrows' => false,
                    'dots' => true,
                    'slidesToShow' => 2,
                    'slidesToScroll' => 1,
                ],
            ],
        ],
    ];
?>
<div class="widget-featured-brands py-5">
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
                <div class="featured-brands__body arrows-top-right">
                    <div
                        class="featured-brands-body slick-slides-carousel"
                        data-slick="<?php echo e(json_encode($slick)); ?>"
                    >
                        <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="featured-brand-item">
                                <div class="brand-item-body mx-2 py-4 px-2">
                                    <a
                                        class="py-3"
                                        href="<?php echo e($brand->url); ?>"
                                    >
                                        <div class="brand__thumb mb-3 img-fluid-eq">
                                            <div class="img-fluid-eq__dummy"></div>
                                            <div class="img-fluid-eq__wrap">
                                                <img
                                                    class="mx-auto"
                                                    src="<?php echo e(RvMedia::getImageUrl($brand->logo, null, false, RvMedia::getDefaultImage())); ?>"
                                                    alt="<?php echo e($brand->name); ?>"
                                                />
                                            </div>
                                        </div>
                                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['brand__text py-3', 'text-center' => ! $brand->description]); ?>">
                                            <span class="h6 fw-bold text-secondary text-uppercase brand__name">
                                                <?php echo e($brand->name); ?>

                                            </span>
                                            <?php if($brand->description): ?>
                                                <div class="h5 fw-bold brand__desc">
                                                    <div>
                                                        <?php echo BaseHelper::clean(Str::limit($brand->description, 150)); ?>

                                                    </div>
                                                </div>
                                            <?php endif; ?>
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
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/shortcodes/ecommerce/featured-brands.blade.php ENDPATH**/ ?>