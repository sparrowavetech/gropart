<?php
    $slickConfig = [
        'arrows' => false,
        'dots' => false,
        'autoplay' => false,
        'infinite' => false,
        'autoplaySpeed' => 3000,
        'speed' => 800,
        'slidesToShow' => 2,
        'slidesToScroll' => 1,
        'responsive' => [
            [
                'breakpoint' => 1024,
                'settings' => [
                    'slidesToShow' => 2,
                ],
            ],
            [
                'breakpoint' => 767,
                'settings' => [
                    'arrows' => false,
                    'dots' => true,
                    'slidesToShow' => 1,
                ],
            ],
        ],
    ];

    if (!$shortcode->app_enabled) {
        $slickConfig['slidesToShow'] = 3;
    }

    $posts = get_featured_posts(!$shortcode->app_enabled ? 3 : 2, ['author', 'categories:id,name', 'categories.slugable']);
?>

<div
    class="widget-blog py-5 lazyload"
    <?php if($shortcode->bg): ?> data-bg="<?php echo e(RvMedia::getImageUrl($shortcode->bg)); ?>" <?php endif; ?>
>
    <div class="container-xxxl">
        <div class="row">
            <div class="<?php if($shortcode->app_enabled): ?> col-lg-8 <?php else: ?> col-12 <?php endif; ?> py-4 py-lg-0">
                <div class="row justify-content-between align-items-center widget-header ms-0 me-0">
                    <h2 class="col-auto mb-0 py-2 ps-0"><?php echo e($shortcode->title); ?></h2>
                    <a
                        class="col-auto pe-0"
                        href="<?php echo e(get_blog_page_url()); ?>"
                    >
                        <span class="link-text"><?php echo e(__('All Articles')); ?>

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
                <div
                    class="col slick-slides-carousel widget-blog-container position-relative"
                    data-slick="<?php echo e(json_encode($slickConfig)); ?>"
                >
                    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="post-item-wrapper">
                            <div class="card post-item__inner">
                                <div class="row g-0">
                                    <div class="col-md-4 post-item__image">
                                        <a
                                            class="img-fluid-eq"
                                            href="<?php echo e($post->url); ?>"
                                        >
                                            <div class="img-fluid-eq__dummy"></div>
                                            <div class="img-fluid-eq__wrap">
                                                <img
                                                    class="lazyload"
                                                    data-src="<?php echo e(RvMedia::getImageUrl($post->image, null, false, RvMedia::getDefaultImage())); ?>"
                                                    src="<?php echo e(image_placeholder($post->image)); ?>"
                                                    alt="<?php echo e($post->name); ?>"
                                                >
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-8 post-item__content">
                                        <div>
                                            <div class="entry-meta">
                                                <?php if($post->author && theme_option('blog_show_author_name', 'yes') == 'yes'): ?>
                                                    <div class="entry-meta-author">
                                                        <span><?php echo e(__('By')); ?></span>
                                                        <strong><?php echo e($post->author->name); ?></strong>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if($post->categories->isNotEmpty()): ?>
                                                    <div class="entry-meta-categories">
                                                        <span><?php echo e(($post->author && theme_option('blog_show_author_name', 'yes') == 'yes') ? __('in') : ucfirst(__('in'))); ?></span>
                                                        <a
                                                            href="<?php echo e($post->firstCategory->url); ?>"><?php echo e($post->firstCategory->name); ?></a>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="entry-meta-date">
                                                    <span><?php echo e(__('on')); ?></span>
                                                    <time><?php echo e(Theme::formatDate($post->created_at)); ?></time>
                                                </div>
                                            </div>
                                            <div class="entry-title mb-3 mt-2">
                                                <p class="h4 text-truncate"><a
                                                        href="<?php echo e($post->url); ?>"><?php echo e($post->name); ?></a></p>
                                            </div>
                                            <div class="entry-description">
                                                <p><?php echo e(Str::words($post->description, 20)); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php if($shortcode->app_enabled): ?>
                <div class="col-lg-4 py-4 py-lg-0">
                    <div
                        class="widget-wrapper widget-mobile-apps h-100 lazyload"
                        <?php if($shortcode->app_bg): ?> data-bg="<?php echo e(RvMedia::getImageUrl($shortcode->app_bg)); ?>" <?php endif; ?>
                    >
                        <?php if($shortcode->app_title): ?>
                            <div class="widget-header text-center me-0">
                                <h2><?php echo BaseHelper::clean($shortcode->app_title); ?></h2>
                            </div>
                        <?php endif; ?>

                        <?php if($shortcode->app_description || ($shortcode->app_ios_img && $shortcode->app_ios_link) || ($shortcode->app_android_img && $shortcode->app_android_link)): ?>
                            <div class="widget-subtitle text-center">
                                <?php if($shortcode->app_description): ?>
                                    <p class="my-3"><?php echo BaseHelper::clean($shortcode->app_description); ?></p>
                                <?php endif; ?>

                                <div>
                                    <?php if($shortcode->app_ios_img && $shortcode->app_ios_link): ?>
                                        <a
                                            href="<?php echo e(url($shortcode->app_ios_link)); ?>"
                                            title="iOS"
                                        >
                                            <img
                                                class="my-4 mx-2 lazyload"
                                                data-src="<?php echo e(RvMedia::getImageUrl($shortcode->app_ios_img)); ?>"
                                                alt="iOS"
                                            >
                                        </a>
                                    <?php endif; ?>
                                    <?php if($shortcode->app_android_img && $shortcode->app_android_link): ?>
                                        <a
                                            href="<?php echo e(url($shortcode->app_android_link)); ?>"
                                            title="Android"
                                        >
                                            <img
                                                class="my-4 mx-2 lazyload"
                                                data-src="<?php echo e(RvMedia::getImageUrl($shortcode->app_android_img)); ?>"
                                                alt="Android"
                                            >
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/shortcodes/featured-posts.blade.php ENDPATH**/ ?>