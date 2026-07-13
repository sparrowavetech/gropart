<?php if(count($sliders) > 0): ?>
    <?php
        $sliders->loadMissing('metadata');
        $slick = [
            'autoplay' => ($shortcode->is_autoplay ?: 'yes') == 'yes',
            'infinite' => ($shortcode->is_autoplay ?: 'yes') == 'yes',
            'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options()) ? $shortcode->autoplay_speed : 5000,
            'speed' => 1000,
            'slidesToShow' => 1,
            'slidesToScroll' => 1,
            'appendArrows' => '.arrows-wrapper',
            'fade' => true,
        ];
    ?>
    <div
        class="section-content section-content__slider lazyload"
        <?php if($shortcode->background): ?> data-bg="<?php echo e(RvMedia::getImageUrl($shortcode->background)); ?>" <?php endif; ?>
    >
        <div class="container-xxxl">
            <div class="row gx-0 gx-md-4">
                <div class="<?php if(is_plugin_active('ads') && $shortcode->ads): ?> col-md-8 <?php else: ?> col-md-12 <?php endif; ?>">
                    <div class="section-slides-wrapper my-3">
                        <div
                            class="slide-body slick-slides-carousel"
                            data-slick="<?php echo e(json_encode($slick)); ?>"
                        >
                            <?php $__currentLoopData = $sliders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="slide-item">
                                    <div class="slide-item__image">
                                        <?php if($slider->link): ?>
                                            <a href="<?php echo e(url($slider->link)); ?>">
                                        <?php endif; ?>
                                        <?php
                                            $tabletImage = $slider->getMetaData('tablet_image', true) ?: $slider->image;
                                            $mobileImage = $slider->getMetaData('mobile_image', true) ?: $tabletImage;
                                        ?>
                                        <picture>
                                            <source
                                                srcset="<?php echo e(RvMedia::getImageUrl($slider->image, null, false, RvMedia::getDefaultImage())); ?>"
                                                media="(min-width: 1200px)"
                                            />
                                            <source
                                                srcset="<?php echo e(RvMedia::getImageUrl($tabletImage, null, false, RvMedia::getDefaultImage())); ?>"
                                                media="(min-width: 768px)"
                                            />
                                            <source
                                                srcset="<?php echo e(RvMedia::getImageUrl($mobileImage, null, false, RvMedia::getDefaultImage())); ?>"
                                                media="(max-width: 767px)"
                                            />
                                            <img
                                                src="<?php echo e(image_placeholder($slider->image)); ?>"
                                                alt="<?php echo e($slider->title); ?>"
                                                loading="eager"
                                            />
                                        </picture>
                                        <?php if($slider->link): ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="arrows-wrapper"></div>
                    </div>
                </div>
                <?php if(is_plugin_active('ads') && $shortcode->ads): ?>
                    <div class="col-md-4">
                        <div class="section-banner-wrapper my-3">
                            <div class="banner-medium">
                                <div class="banner-item__image">
                                    <?php echo display_ads_advanced($shortcode->ads); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/shortcodes/sliders.blade.php ENDPATH**/ ?>