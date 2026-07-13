    <footer id="footer">
        <?php if($preFooterSidebar = dynamic_sidebar('pre_footer_sidebar')): ?>
            <div class="footer-info border-top">
                <div class="container-xxxl py-3">
                    <?php echo $preFooterSidebar; ?>

                </div>
            </div>
        <?php endif; ?>
        <?php if($footerSidebar = dynamic_sidebar('footer_sidebar')): ?>
            <div class="footer-widgets">
                <div class="container-xxxl">
                    <div class="row border-top py-5">
                        <?php echo $footerSidebar; ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if($bottomFooterSidebar = dynamic_sidebar('bottom_footer_sidebar')): ?>
            <div class="container-xxxl">
                <div
                    class="footer__links"
                    id="footer-links"
                >
                    <?php echo $bottomFooterSidebar; ?>

                </div>
            </div>
        <?php endif; ?>
        <div class="container-xxxl">
            <div class="row border-top py-4">
                <div class="col-lg-3 col-md-4 py-3">
                    <div class="copyright d-flex justify-content-center justify-content-md-start">
                        <span><?php echo Theme::getSiteCopyright(); ?></span>
                    </div>
                </div>
                <div class="col-lg-6 col-md-4 py-3">
                    <?php if(theme_option('payment_methods_image')): ?>
                        <div class="footer-payments d-flex justify-content-center">
                            <?php if(theme_option('payment_methods_link')): ?>
                                <a
                                    href="<?php echo e(url(theme_option('payment_methods_link'))); ?>"
                                    target="_blank"
                                >
                            <?php endif; ?>

                            <img
                                class="lazyload"
                                data-src="<?php echo e(RvMedia::getImageUrl(theme_option('payment_methods_image'))); ?>"
                                alt="footer-payments"
                            >

                            <?php if(theme_option('payment_methods_link')): ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-3 col-md-4 py-3">
                    <div class="footer-socials d-flex justify-content-md-end justify-content-center">
                        <?php if($socialLinks = Theme::getSocialLinks()): ?>
                            <p class="me-3 mb-0"><?php echo e(__('Stay connected:')); ?></p>
                            <div class="footer-socials-container">
                                <ul class="ps-0 mb-0">
                                    <?php $__currentLoopData = $socialLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $socialLink): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(! $socialLink->getUrl() || ! $socialLink->getIconHtml()) continue; ?>

                                        <li class="d-inline-block ps-1 my-1">
                                            <a <?php echo $socialLink->getAttributes(); ?>><?php echo e($socialLink->getIconHtml()); ?></a>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <?php if(is_plugin_active('ecommerce')): ?>
        <div
            class="panel--sidebar"
            id="navigation-mobile"
        >
            <div class="panel__header">
                <span class="svg-icon close-toggle--sidebar">
                    <svg>
                        <use
                            href="#svg-icon-arrow-left"
                            xlink:href="#svg-icon-arrow-left"
                        ></use>
                    </svg>
                </span>
                <h3><?php echo e(__('Categories')); ?></h3>
            </div>
            <div
                class="panel__content"
                data-bb-toggle="init-categories-dropdown"
                data-bb-target=".product-category-dropdown-wrapper"
                data-url="<?php echo e(route('public.ajax.categories-dropdown')); ?>"
            >
                <ul class="menu--mobile product-category-dropdown-wrapper"></ul>
            </div>
        </div>
    <?php endif; ?>

    <div
        class="panel--sidebar"
        id="menu-mobile"
    >
        <div class="panel__header">
            <span class="svg-icon close-toggle--sidebar">
                <svg>
                    <use
                        href="#svg-icon-arrow-left"
                        xlink:href="#svg-icon-arrow-left"
                    ></use>
                </svg>
            </span>
            <h3><?php echo e(__('Menu')); ?></h3>
        </div>
        <div class="panel__content">
            <?php echo Menu::renderMenuLocation('main-menu', [
                'view' => 'menu',
                'options' => ['class' => 'menu--mobile'],
            ]); ?>


            <?php echo Menu::renderMenuLocation('header-navigation', [
                'view' => 'menu',
                'options' => ['class' => 'menu--mobile'],
            ]); ?>


            <ul class="menu--mobile">

                <?php if(is_plugin_active('ecommerce')): ?>
                    <?php if(EcommerceHelper::isCompareEnabled()): ?>
                        <li><a href="<?php echo e(route('public.compare')); ?>"><span><?php echo e(__('Compare')); ?></span></a></li>
                    <?php endif; ?>

                    <?php if(count($currencies) > 1): ?>
                        <li class="menu-item-has-children">
                            <a href="#">
                                <span><?php echo e(get_application_currency()->title); ?></span>
                                <span class="sub-toggle">
                                    <span class="svg-icon">
                                        <svg>
                                            <use
                                                href="#svg-icon-chevron-down"
                                                xlink:href="#svg-icon-chevron-down"
                                            ></use>
                                        </svg>
                                    </span>
                                </span>
                            </a>
                            <ul class="sub-menu">
                                <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($currency->id !== get_application_currency_id()): ?>
                                        <li><a
                                                href="<?php echo e(route('public.change-currency', $currency->title)); ?>"><span><?php echo e($currency->title); ?></span></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if(is_plugin_active('language')): ?>
                    <?php
                        $supportedLocales = Language::getSupportedLocales();
                    ?>

                    <?php if($supportedLocales && count($supportedLocales) > 1): ?>
                        <?php
                            $languageDisplay = setting('language_display', 'all');
                        ?>
                        <li class="menu-item-has-children">
                            <a href="#">
                                <?php if($languageDisplay == 'all' || $languageDisplay == 'flag'): ?>
                                    <?php echo language_flag(Language::getCurrentLocaleFlag(), Language::getCurrentLocaleName()); ?>

                                <?php endif; ?>
                                <?php if($languageDisplay == 'all' || $languageDisplay == 'name'): ?>
                                    <?php echo e(Language::getCurrentLocaleName()); ?>

                                <?php endif; ?>
                                <span class="sub-toggle">
                                    <span class="svg-icon">
                                        <svg>
                                            <use
                                                href="#svg-icon-chevron-down"
                                                xlink:href="#svg-icon-chevron-down"
                                            ></use>
                                        </svg>
                                    </span>
                                </span>
                            </a>
                            <ul class="sub-menu">
                                <?php $__currentLoopData = $supportedLocales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localeCode => $properties): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($localeCode != Language::getCurrentLocale()): ?>
                                        <li>
                                            <a
                                                href="<?php echo e(Language::getSwitcherUrl($localeCode, $properties['lang_code'])); ?>">
                                                <?php if($languageDisplay == 'all' || $languageDisplay == 'flag'): ?>
                                                    <?php echo language_flag($properties['lang_flag'], $properties['lang_name']); ?>

                                                <?php endif; ?>
                                                <?php if($languageDisplay == 'all' || $languageDisplay == 'name'): ?>
                                                    <span><?php echo e($properties['lang_name']); ?></span>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div
        class="panel--sidebar panel--sidebar__right"
        id="search-mobile"
    >
        <div class="panel__header">
            <?php if(is_plugin_active('ecommerce')): ?>
                <?php echo Theme::partial('footer-search-ecommerce'); ?>

            <?php endif; ?>
        </div>
    </div>
    <div class="footer-mobile" <?php if(theme_option('bottom_bar_menu_show_text', 'yes') != 'yes'): ?>data-hide-text="true"<?php endif; ?> style="--bottom-bar-menu-text-font-size: <?php echo e(theme_option('bottom_bar_menu_text_font_size', 11)); ?>px;">
        <ul class="menu--footer">
            <li>
                <a href="<?php echo e(BaseHelper::getHomepageUrl()); ?>">
                    <i class="icon-home3"></i>
                    <span><?php echo e(__('Home')); ?></span>
                </a>
            </li>
            <?php if(is_plugin_active('ecommerce')): ?>
                <li>
                    <a
                        class="toggle--sidebar"
                        href="#navigation-mobile"
                    >
                        <i class="icon-list"></i>
                        <span><?php echo e(__('Category')); ?></span>
                    </a>
                </li>
                <?php if(EcommerceHelper::isCartEnabled()): ?>
                    <li>
                        <a
                            class="toggle--sidebar"
                            href="#cart-mobile"
                        >
                            <i class="icon-cart">
                                <span class="cart-counter"><?php echo e(Cart::instance('cart')->count()); ?></span>
                            </i>
                            <span><?php echo e(__('Cart')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(EcommerceHelper::isWishlistEnabled()): ?>
                    <li>
                        <a href="<?php echo e(route('public.wishlist')); ?>">
                            <i class="icon-heart"></i>
                            <span><?php echo e(__('Wishlist')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?php echo e(route('customer.overview')); ?>">
                        <i class="icon-user"></i>
                        <span><?php echo e(__('Account')); ?></span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <?php if(is_plugin_active('ecommerce')): ?>
        <?php echo Theme::partial('ecommerce.quick-view-modal'); ?>

        <?php echo Theme::partial('ecommerce.quick-shop-modal'); ?>

    <?php endif; ?>
    <?php echo Theme::partial('toast'); ?>


    <div class="panel-overlay-layer"></div>
    <div id="back2top">
        <span class="svg-icon">
            <svg>
                <use
                    href="#svg-icon-arrow-up"
                    xlink:href="#svg-icon-arrow-up"
                ></use>
            </svg>
        </span>
    </div>

    <script data-pagespeed-no-defer data-pagespeed-no-transform>
        'use strict';

        window.trans = {
            "View All": "<?php echo e(__('View All')); ?>",
            "No reviews!": "<?php echo e(__('No reviews!')); ?>"
        };

        window.siteConfig = {
            "url": "<?php echo e(BaseHelper::getHomepageUrl()); ?>",
            "img_placeholder": "<?php echo e(theme_option('lazy_load_image_enabled', 'yes') == 'yes' ? image_placeholder() : null); ?>",
            "countdown_text": {
                "days": "<?php echo e(__('days')); ?>",
                "hours": "<?php echo e(__('hours')); ?>",
                "minutes": "<?php echo e(__('mins')); ?>",
                "seconds": "<?php echo e(__('secs')); ?>"
            }
        };

        <?php if(is_plugin_active('ecommerce') && EcommerceHelper::isCartEnabled()): ?>
            window.siteConfig.ajaxCart = "<?php echo e(route('public.ajax.cart')); ?>";
            window.siteConfig.cartUrl = "<?php echo e(route('public.cart')); ?>";
        <?php endif; ?>
    </script>

    <?php echo Theme::footer(); ?>


    </body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/footer.blade.php ENDPATH**/ ?>