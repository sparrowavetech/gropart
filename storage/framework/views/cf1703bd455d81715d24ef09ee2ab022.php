<!DOCTYPE html>
<html <?php echo Theme::htmlAttributes(); ?>>
<head>
    <meta charset="utf-8">
    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=1"
    />
    <meta
        name="csrf-token"
        content="<?php echo e(csrf_token()); ?>"
    >

    <link rel="stylesheet" href="<?php echo e(url('/')); ?>/themes/gropart/plugins/font-awesome/css/font-awesome.min.css" />
    <style>
        nav.navigation .navigation__right .d-sticky-header.header-middle {
            display: none;
        }
        .header.header--sticky nav.navigation .navigation__right .d-sticky-header.header-middle {
            display: block;
            border-bottom: 0;
            margin-top: 7px;
        }
        .header.header--sticky nav.navigation .navigation__right .header-recently-viewed {
            display: none;
        }
        .header.header--sticky nav.navigation .navigation__right {
            min-width:20%;
        }
        .header.header--sticky nav.navigation .navigation__right .header-middle.d-sticky-header .header__right {
            width:100%;
        }
    </style>

    <style>
        :root {
            --primary-color: <?php echo e(theme_option('primary_color', '#fab528')); ?>;
            --primary-color-rgb: <?php echo e(implode(', ', BaseHelper::hexToRgb(theme_option('primary_color', '#fab528')))); ?>;
            --heading-color: <?php echo e(theme_option('heading_color', '#000')); ?>;
            --text-color: <?php echo e(theme_option('text_color', '#000')); ?>;
            --primary-button-color: <?php echo e(theme_option('primary_button_color', '#000')); ?>;
            --primary-button-background-color: <?php echo e(theme_option('primary_button_background_color') ?: theme_option('primary_color', '#fab528')); ?>;
            --top-header-background-color: <?php echo e(theme_option('top_header_background_color', '#f7f7f7')); ?>;
            --top-header-text-color: <?php echo e(theme_option('top_header_text_color') ?: theme_option('header_text_color', '#000')); ?>;
            --middle-header-background-color: <?php echo e(theme_option('middle_header_background_color', '#fff')); ?>;
            --middle-header-text-color: <?php echo e(theme_option('middle_header_text_color') ?: theme_option('header_text_color', '#000')); ?>;
            --bottom-header-background-color: <?php echo e(theme_option('bottom_header_background_color', '#fff')); ?>;
            --bottom-header-text-color: <?php echo e(theme_option('bottom_header_text_color') ?: theme_option('header_text_color', '#000')); ?>;
            --header-text-color: <?php echo e(theme_option('header_text_color', '#000')); ?>;
            --header-text-secondary-color: <?php echo e(BaseHelper::hexToRgba(theme_option('header_text_color', '#000'), 0.5)); ?>;
            --header-deliver-color: <?php echo e(BaseHelper::hexToRgba(theme_option('header_deliver_color', '#000'), 0.15)); ?>;
            --header-mobile-background-color: <?php echo e(theme_option('header_mobile_background_color', '#fff')); ?>;
            --header-mobile-icon-color: <?php echo e(theme_option('header_mobile_icon_color', '#222')); ?>;
            --footer-text-color: <?php echo e(theme_option('footer_text_color', '#555')); ?>;
            --footer-heading-color: <?php echo e(theme_option('footer_heading_color', '#555')); ?>;
            --footer-hover-color: <?php echo e(theme_option('footer_hover_color', '#fab528')); ?>;
            --footer-border-color: <?php echo e(theme_option('footer_border_color', '#dee2e6')); ?>;
        }
    </style>

    <?php
        Theme::asset()->remove('language-css');
        Theme::asset()
            ->container('footer')
            ->remove('language-public-js');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-owl-carousel-css');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-owl-carousel-js');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-css');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-js');
    ?>

    <?php echo Theme::header(); ?>

</head>

<body <?php echo Theme::bodyAttributes(); ?>>
    <?php if(theme_option('preloader_enabled', 'yes') == 'yes'): ?>
        <?php echo Theme::partial('preloader'); ?>

    <?php endif; ?>

    <?php echo Theme::partial('svg-icons'); ?>

    <?php echo apply_filters(THEME_FRONT_BODY, null); ?>


    <header
        class="header header-js-handler"
        data-sticky="<?php echo e(theme_option('sticky_header_enabled', 'yes') == 'yes' ? 'true' : 'false'); ?>"
    >
        <?php if(theme_option('top_upper_header_text')): ?>
            <div class="header-upper-top">
                <div class="container-xxxl">
                    <div class="row align-items-center">
                        <div class="col-md-12 text-center" style="position: relative;">
                            <p class="m-0"><?php echo theme_option('top_upper_header_text'); ?></p>
                            <span class="top-dismiss" id="top-dismiss">X</span>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.getElementById('top-dismiss')?.addEventListener('click', function() {
                    this.closest('.header-upper-top').style.display = 'none';
                });
            </script>
        <?php endif; ?>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'header-top d-none d-lg-block',
            'header-content-sticky' =>
                theme_option('sticky_header_content_position', 'middle') == 'top',
        ]); ?>">
            <div class="container-xxxl">
                <div class="header-wrapper">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <div class="header-info">
                                <?php echo Menu::renderMenuLocation('header-navigation', ['view' => 'menu-default']); ?>

                            </div>
                        </div>
                        <div class="col-6">
                            <div class="header-info header-info-right">
                                <ul>
                                    <?php if(is_plugin_active('marketplace')): ?>
                                         <?php if(auth('customer')->check()): ?>
                                            <?php if(auth('customer')->user()->is_vendor): ?>
                                                <li class="become-vendor">
                                                    <a class="become-vendor-link" href="<?php echo e(route('marketplace.vendor.dashboard')); ?>"><i class="icon-speed-fast"></i> <?php echo e(__('Vendor dashboard')); ?></a>
                                                </li>
                                            <?php else: ?>
                                                <li class="become-vendor">
                                                    <a class="become-vendor-link" href="<?php echo e(route('marketplace.vendor.become-vendor')); ?>"><i class="icon-users2"></i> <?php echo e(__('Become Partner')); ?></a>
                                                </li>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <li class="become-vendor"><a class="become-vendor-link" href="<?php echo e(route('customer.register')); ?>"><i class="icon-users2"></i> <?php echo e(__('Become Partner')); ?></a></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if(is_plugin_active('language')): ?>
                                        <?php echo Theme::partial('language-switcher'); ?>

                                    <?php endif; ?>
                                    <?php if(is_plugin_active('ecommerce')): ?>
                                        <?php if(count($currencies) > 1): ?>
                                            <li>
                                                <a
                                                    class="language-dropdown-active"
                                                    href="#"
                                                >
                                                    <span><?php echo e(get_application_currency()->title); ?></span>
                                                    <span class="svg-icon">
                                                        <svg>
                                                            <use
                                                                href="#svg-icon-chevron-down"
                                                                xlink:href="#svg-icon-chevron-down"
                                                            ></use>
                                                        </svg>
                                                    </span>
                                                </a>
                                                <ul class="language-dropdown">
                                                    <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if($currency->id !== get_application_currency_id()): ?>
                                                            <li>
                                                                <a
                                                                    href="<?php echo e(route('public.change-currency', $currency->title)); ?>">
                                                                    <span><?php echo e($currency->title); ?></span>
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if(auth('customer')->check()): ?>
                                            <li>
                                                <a
                                                    href="<?php echo e(route('customer.overview')); ?>"><?php echo e(auth('customer')->user()->name); ?></a>
                                                <span class="d-inline-block ms-1">(<a
                                                        class="color-primary"
                                                        href="<?php echo e(route('customer.logout')); ?>"
                                                    ><i class="icon-exit"></i> <?php echo e(__('Logout')); ?></a>)</span>
                                            </li>
                                        <?php else: ?>
                                            <li><a href="<?php echo e(route('customer.login')); ?>"><i class="icon-enter"></i> <?php echo e(__('Login')); ?></a></li>
                                            <li><a href="<?php echo e(route('customer.register')); ?>"><i class="icon-user"></i> <?php echo e(__('Register')); ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'header-middle',
            'header-content-sticky' =>
                theme_option('sticky_header_content_position', 'middle') == 'middle',
        ]); ?>">
            <div class="container-xxxl">
                <div class="header-wrapper">
                    <div class="header-items header__left">
                        <div class="logo">
                            <a href="<?php echo e(BaseHelper::getHomepageUrl()); ?>">
                                <?php echo Theme::getLogoImage(['style' => 'max-height: 45px']); ?>

                            </a>
                        </div>
                    </div>
                    <div class="header-items header__center">
                        <?php if(is_plugin_active('ecommerce')): ?>
                            <?php echo Theme::partial('header-search-ecommerce'); ?>

                        <?php endif; ?>
                    </div>
                    <div class="header-items header__right">
                        <?php if(theme_option('hotline')): ?>
                            <div class="header__extra header-support">
                                <div class="header-box-content">
                                    <span><?php echo e(theme_option('hotline')); ?></span>
                                    <p><?php echo e(__('Support 24/7')); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(is_plugin_active('ecommerce')): ?>
                            <?php if(EcommerceHelper::isCompareEnabled()): ?>
                                <div class="header__extra header-compare">
                                    <a
                                        class="btn-compare"
                                        href="<?php echo e(route('public.compare')); ?>"
                                    >
                                        <i class="icon-repeat"></i>
                                        <span
                                            class="header-item-counter"><?php echo e(Cart::instance('compare')->count()); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if(EcommerceHelper::isWishlistEnabled()): ?>
                                <div class="header__extra header-wishlist">
                                    <a
                                        class="btn-wishlist"
                                        href="<?php echo e(route('public.wishlist')); ?>"
                                    >
                                        <span class="svg-icon">
                                            <svg>
                                                <use
                                                    href="#svg-icon-wishlist"
                                                    xlink:href="#svg-icon-wishlist"
                                                ></use>
                                            </svg>
                                        </span>
                                        <span class="header-item-counter">
                                            <?php echo e(auth('customer')->check()? auth('customer')->user()->wishlist()->count(): Cart::instance('wishlist')->count()); ?>

                                        </span>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if(EcommerceHelper::isCartEnabled()): ?>
                                <div
                                    class="header__extra cart--mini"
                                    role="button"
                                    tabindex="0"
                                >
                                    <div class="header__extra">
                                        <a
                                            class="btn-shopping-cart"
                                            href="<?php echo e(route('public.cart')); ?>"
                                        >
                                            <span class="svg-icon">
                                                <svg>
                                                    <use
                                                        href="#svg-icon-cart"
                                                        xlink:href="#svg-icon-cart"
                                                    ></use>
                                                </svg>
                                            </span>
                                            <span
                                                class="header-item-counter"><?php echo e(Cart::instance('cart')->count()); ?></span>
                                        </a>
                                        <span class="cart-text">
                                            <span class="cart-title"><?php echo e(__('Your Cart')); ?></span>
                                            <span class="cart-price-total">
                                                <span class="cart-amount">
                                                    <bdi>
                                                        <span><?php echo e(format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax())); ?></span>
                                                    </bdi>
                                                </span>
                                            </span>
                                        </span>
                                    </div>
                                    <div
                                        class="cart__content"
                                        id="cart-mobile"
                                    >
                                        <div class="backdrop"></div>
                                        <div class="mini-cart-content">
                                            <div class="widget-shopping-cart-content">
                                                <?php echo Theme::partial('cart-mini.list'); ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'header-bottom',
            'header-content-sticky' =>
                theme_option('sticky_header_content_position', 'middle') == 'bottom',
        ]); ?>">
            <div class="header-wrapper">
                <nav class="navigation">
                    <div class="container-xxxl">
                        <div class="navigation__left">
                            <?php if(is_plugin_active('ecommerce')): ?>
                                <?php if(theme_option('enabled_product_categories_on_header', 'yes') == 'yes' && theme_option('enabled_product_categories_sidebar_on_header', 'yes') != 'yes'): ?>
                                    <div class="menu--product-categories">
                                        <div class="menu__toggle">
                                            <span class="svg-icon">
                                                <svg>
                                                    <use
                                                        href="#svg-icon-list"
                                                        xlink:href="#svg-icon-list"
                                                    ></use>
                                                </svg>
                                            </span>
                                            <span class="menu__toggle-title"><?php echo e(__('Shop by Category')); ?></span>
                                        </div>
                                        <div
                                            class="menu__content"
                                            data-bb-toggle="init-categories-dropdown"
                                            data-bb-target=".menu--dropdown"
                                            data-url="<?php echo e(route('public.ajax.categories-dropdown')); ?>"
                                        >
                                            <ul class="menu--dropdown"></ul>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if(theme_option('enabled_product_categories_sidebar_on_header', 'yes') == 'yes'): ?>
                                    <div class="menu--product-categories">
                                        <a class="menu__toggle toggle--sidebar" href="#navigation-desktop">
                                            <span class="svg-icon">
                                                <svg>
                                                    <use
                                                        href="#svg-icon-list"
                                                        xlink:href="#svg-icon-list"
                                                    ></use>
                                                </svg>
                                            </span>
                                            <span class="menu__toggle-title"><?php echo e(__('Shop by Category')); ?></span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['navigation__center', 'ps-0' => theme_option('enabled_product_categories_on_header', 'yes') != 'yes' && theme_option('enabled_product_categories_sidebar_on_header', 'yes') != 'yes']); ?>">
                            <?php echo Menu::renderMenuLocation('main-menu', [
                                'view' => 'menu',
                                'options' => ['class' => 'menu'],
                            ]); ?>

                        </div>
                        <div class="navigation__right">
                            <?php if(is_plugin_active('ecommerce') && EcommerceHelper::isEnabledCustomerRecentlyViewedProducts()): ?>
                                <div
                                    class="header-recently-viewed"
                                    data-url="<?php echo e(route('public.ajax.recently-viewed-products')); ?>"
                                    role="button"
                                >
                                    <h3 class="recently-title">
                                        <span class="svg-icon recent-icon">
                                            <svg>
                                                <use
                                                    href="#svg-icon-refresh"
                                                    xlink:href="#svg-icon-refresh"
                                                ></use>
                                            </svg>
                                        </span>
                                        <?php echo e(__('Recently Viewed')); ?>

                                    </h3>
                                    <div class="recently-viewed-inner container-xxxl">
                                        <div class="recently-viewed-content">
                                            <div class="loading--wrapper">
                                                <div class="loading"></div>
                                            </div>
                                            <div class="recently-viewed-products"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if(is_plugin_active('ecommerce')): ?>
                                <div class="header-middle d-sticky-header">
                                    <div class="header-items header__right">
                                        <?php if(EcommerceHelper::isCompareEnabled()): ?>
                                            <div class="header__extra header-compare">
                                                <a class="btn-compare" href="<?php echo e(route('public.compare')); ?>">
                                                    <i class="icon-repeat"></i>
                                                    <span class="header-item-counter"><?php echo e(Cart::instance('compare')->count()); ?></span>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if(EcommerceHelper::isWishlistEnabled()): ?>
                                            <div class="header__extra header-wishlist">
                                                <a class="btn-wishlist" href="<?php echo e(route('public.wishlist')); ?>">
                                                    <span class="svg-icon">
                                                        <svg>
                                                            <use href="#svg-icon-wishlist" xlink:href="#svg-icon-wishlist"></use>
                                                        </svg>
                                                    </span>
                                                    <span class="header-item-counter">
                                                        <?php echo e(auth('customer')->check() ? auth('customer')->user()->wishlist()->count() : Cart::instance('wishlist')->count()); ?>

                                                    </span>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if(EcommerceHelper::isCartEnabled()): ?>
                                            <div class="header__extra cart--mini" tabindex="0" role="button">
                                                <div class="header__extra">
                                                    <a class="btn-shopping-cart" href="<?php echo e(route('public.cart')); ?>">
                                                        <span class="svg-icon">
                                                            <svg>
                                                                <use href="#svg-icon-cart" xlink:href="#svg-icon-cart"></use>
                                                            </svg>
                                                        </span>
                                                        <span class="header-item-counter"><?php echo e(Cart::instance('cart')->count()); ?></span>
                                                    </a>
                                                    <span class="cart-text">
                                                        <span class="cart-title"><?php echo e(__('Your Cart')); ?></span>
                                                        <span class="cart-price-total">
                                                            <span class="cart-amount">
                                                                <bdi>
                                                                    <span><?php echo e(format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax())); ?></span>
                                                                </bdi>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="cart__content" id="cart-mobile">
                                                    <div class="backdrop"></div>
                                                    <div class="mini-cart-content">
                                                        <div class="widget-shopping-cart-content">
                                                            <?php echo Theme::partial('cart-mini.list'); ?>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div
            class="header-mobile header-js-handler"
            data-sticky="<?php echo e(theme_option('sticky_header_mobile_enabled', 'yes') == 'yes' ? 'true' : 'false'); ?>"
        >
            <div class="header-items-mobile header-items-mobile--left">
                <div class="menu-mobile">
                    <div class="menu-box-title">
                        <div
                            class="icon menu-icon toggle--sidebar"
                            href="#menu-mobile"
                        >
                            <span class="svg-icon">
                                <svg>
                                    <use
                                        href="#svg-icon-list"
                                        xlink:href="#svg-icon-list"
                                    ></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-items-mobile header-items-mobile--center">
                <div class="logo">
                    <a href="<?php echo e(BaseHelper::getHomepageUrl()); ?>">
                        <?php echo Theme::getLogoImage(['style' => 'max-height: 45px']); ?>

                    </a>
                </div>
            </div>
            <div class="header-items-mobile header-items-mobile--right">
                <div class="search-form--mobile search-form--mobile-right search-panel">
                    <a
                        class="open-search-panel toggle--sidebar"
                        href="#search-mobile"
                        title="<?php echo e(__('Search')); ?>"
                    >
                        <span class="svg-icon">
                            <svg>
                                <use
                                    href="#svg-icon-search"
                                    xlink:href="#svg-icon-search"
                                ></use>
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </header>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart-child/partials/header.blade.php ENDPATH**/ ?>