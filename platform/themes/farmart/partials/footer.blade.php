    <footer id="footer" class="border-top pt-5">
        @if ($preFooterSidebar = dynamic_sidebar('pre_footer_sidebar'))
            <div class="footer-info">
                <div class="container-xxxl pb-5">
                    {!! $preFooterSidebar !!}
                </div>
            </div>
        @endif
        @if ($footerSidebar = dynamic_sidebar('footer_sidebar'))
            <div class="footer-widgets">
                <div class="container-xxxl">
                    <div class="row border-top py-5">
                        {!! $footerSidebar !!}
                    </div>
                </div>
            </div>
        @endif
        @if ($bottomFooterSidebar = dynamic_sidebar('bottom_footer_sidebar'))
            <div class="container-xxxl">
                <div class="footer__links" id="footer-links">
                    {!! $bottomFooterSidebar !!}
                </div>
            </div>
        @endif
        <div class="container-xxxl">
            <div class="row border-top py-4">
                <div class="col-lg-3 col-md-4 py-3">
                    <div class="copyright d-flex justify-content-center justify-content-md-start">
                        <span>{{ theme_option('copyright') }}</span>
                    </div>
                </div>
                <div class="col-lg-6 col-md-4 py-3">
                    @if (theme_option('payment_methods_image'))
                        <div class="footer-payments d-flex justify-content-center">
                            @if (theme_option('payment_methods_link'))
                                <a
                                    href="{{ url(theme_option('payment_methods_link')) }}"
                                    target="_blank"
                                >
                            @endif

                            <img
                                class="lazyload"
                                data-src="{{ RvMedia::getImageUrl(theme_option('payment_methods_image')) }}"
                                alt="footer-payments"
                            >

                            @if (theme_option('payment_methods_link'))
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="col-lg-3 col-md-4 py-3">
                    <div class="footer-socials d-flex justify-content-md-end justify-content-center">
                        @if ($socialLinks = Theme::getSocialLinks())
                            <p class="me-3 mb-0">{{ __('Stay connected:') }}</p>
                            <div class="footer-socials-container">
                                <ul class="ps-0 mb-0">
                                    @foreach($socialLinks as $socialLink)
                                        @continue(! $socialLink->getUrl() || ! $socialLink->getIconHtml())

                                        <li class="d-inline-block ps-1 my-1">
                                            <a {!! $socialLink->getAttributes() !!}>{{ $socialLink->getIconHtml() }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </footer>
    @if (is_plugin_active('ecommerce'))
        <div class="panel--sidebar" id="navigation-mobile">
            <div class="panel__header">
                <span class="svg-icon close-toggle--sidebar">
                    <svg>
                        <use
                            href="#svg-icon-arrow-left"
                            xlink:href="#svg-icon-arrow-left"
                        ></use>
                    </svg>
                </span>
                <h3>{{ __('Categories') }}</h3>
            </div>
            <div class="panel__content"
                data-bb-toggle="init-categories-dropdown"
                data-bb-target=".product-category-dropdown-wrapper"
                data-url="{{ route('public.ajax.categories-dropdown') }}">
                <ul class="menu--mobile product-category-dropdown-wrapper"></ul>
            </div>
        </div>
        @if(theme_option('enabled_product_categories_sidebar_on_header', 'yes') == 'yes')
            <div class="panel--sidebar" id="navigation-desktop">
                <div class="panel__header">
                    <span class="svg-icon close-toggle--sidebar">
                        <svg>
                            <use
                                href="#svg-icon-arrow-left"
                                xlink:href="#svg-icon-arrow-left"
                            ></use>
                        </svg>
                    </span>
                    <h3>{{ __('Categories') }}</h3>
                </div>
                <div class="panel__content"
                    data-bb-toggle="init-categories-dropdown"
                    data-bb-target=".product-category-dropdown-wrapper"
                    data-url="{{ route('public.ajax.categories-dropdown') }}">
                    <ul class="menu--mobile product-category-dropdown-wrapper"></ul>
                </div>
            </div>
        @endif
    @endif

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
            <h3>{{ __('Menu') }}</h3>
        </div>
        <div class="panel__content">
            {!! Menu::renderMenuLocation('main-menu', [
                'view' => 'menu',
                'options' => ['class' => 'menu--mobile'],
            ]) !!}

            {!! Menu::renderMenuLocation('header-navigation', [
                'view' => 'menu',
                'options' => ['class' => 'menu--mobile'],
            ]) !!}

            <ul class="menu--mobile">

                @if (is_plugin_active('ecommerce'))
                    @if (EcommerceHelper::isCompareEnabled())
                        <li><a href="{{ route('public.compare') }}"><i class="icon-repeat"></i> <span>{{ __('Compare') }}</span></a></li>
                    @endif

                    @if (is_plugin_active('marketplace'))
                        @if (auth('customer')->check())
                            @if (auth('customer')->user()->is_vendor)
                                <li>
                                    <a class="become-vendor-link" href="{{ route('marketplace.vendor.dashboard') }}"><i class="icon-user"></i> <sapn>{{ __('Vendor dashboard') }}</span></a>
                                </li>
                            @else
                                <li>
                                    <a class="become-vendor-link" href="{{ route('marketplace.vendor.become-vendor') }}"><i class="icon-user"></i> <span>{{ __('Become Vendor') }}</span></a>
                                </li>
                            @endif
                        @else
                            <li><a class="become-vendor-link" href="{{ route('customer.register') }}"><i class="icon-user"></i> <span>{{ __('Become Vendor') }}</span></a></li>
                        @endif
                    @endif


                    @if (count($currencies) > 1)
                        <li class="menu-item-has-children">
                            <a href="#">
                                <span>{{ get_application_currency()->title }}</span>
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
                                @foreach ($currencies as $currency)
                                    @if ($currency->id !== get_application_currency_id())
                                        <li><a href="{{ route('public.change-currency', $currency->title) }}"><span>{{ $currency->title }}</span></a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endif
                @if (is_plugin_active('language'))
                    @php
                        $supportedLocales = Language::getSupportedLocales();
                    @endphp

                    @if ($supportedLocales && count($supportedLocales) > 1)
                        @php
                            $languageDisplay = setting('language_display', 'all');
                        @endphp
                        <li class="menu-item-has-children">
                            <a href="#">
                                @if ($languageDisplay == 'all' || $languageDisplay == 'flag')
                                    {!! language_flag(Language::getCurrentLocaleFlag(), Language::getCurrentLocaleName()) !!}
                                @endif
                                @if ($languageDisplay == 'all' || $languageDisplay == 'name')
                                    {{ Language::getCurrentLocaleName() }}
                                @endif
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
                                @foreach ($supportedLocales as $localeCode => $properties)
                                    @if ($localeCode != Language::getCurrentLocale())
                                        <li>
                                            <a
                                                href="{{ Language::getSwitcherUrl($localeCode, $properties['lang_code']) }}">
                                                @if ($languageDisplay == 'all' || $languageDisplay == 'flag')
                                                    {!! language_flag($properties['lang_flag'], $properties['lang_name']) !!}
                                                @endif
                                                @if ($languageDisplay == 'all' || $languageDisplay == 'name')
                                                    <span>{{ $properties['lang_name'] }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
    <div
        class="panel--sidebar panel--sidebar__right"
        id="search-mobile"
    >
        <div class="panel__header">
            @if (is_plugin_active('ecommerce'))
                <x-plugins-ecommerce::fronts.ajax-search class="form--quick-search w-100">
                    <div class="search-inner-content">
                        <div class="text-search">
                            <div class="search-wrapper">
                                <x-plugins-ecommerce::fronts.ajax-search.input type="text" class="search-field input-search-product" />
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
                </x-plugins-ecommerce::fronts.ajax-search>
            @endif
        </div>
    </div>
    <div class="footer-mobile">
        <ul class="menu--footer">
            <li>
                <a href="{{ BaseHelper::getHomepageUrl() }}">
                    <i class="icon-home3"></i>
                    <span>{{ __('Home') }}</span>
                </a>
            </li>
            @if (is_plugin_active('ecommerce'))
                <li>
                    <a
                        class="toggle--sidebar"
                        href="#navigation-mobile"
                    >
                        <i class="icon-list"></i>
                        <span>{{ __('Category') }}</span>
                    </a>
                </li>
                @if (EcommerceHelper::isCartEnabled())
                    <li>
                        <a
                            class="toggle--sidebar"
                            href="#cart-mobile"
                        >
                            <i class="icon-cart">
                                <span class="cart-counter">{{ Cart::instance('cart')->count() }}</span>
                            </i>
                            <span>{{ __('Cart') }}</span>
                        </a>
                    </li>
                @endif
                @if (EcommerceHelper::isWishlistEnabled())
                    <li>
                        <a href="{{ route('public.wishlist') }}">
                            <i class="icon-heart"></i>
                            <span>{{ __('Wishlist') }}</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('customer.overview') }}">
                        <i class="icon-user"></i>
                        <span>{{ __('Account') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>

    @php
        $supportedLocales = Language::getSupportedLocales();
    @endphp

    <div id="rightFloatingBar" class="rightFloatingBar"><div class="dockerTab"><i class="fa fa-chevron-circle-up"></i></div>@if ($supportedLocales && count($supportedLocales) > 1)<a href="#" data-bs-toggle="modal" data-bs-target="#languageModel" class="langChange" style="margin-right:-6px;"><i class="fa fa-language"></i></a>@endif @if (theme_option('hotline'))<a target="_BLANK" href="http://api.whatsapp.com/send?phone={{ theme_option('hotline') }}" class="supportWhatsApp"><i class="fa fa-whatsapp"></i></a>@endif<a class="orderTrack" href="{{ route('public.orders.tracking') }}"><i class="fa fa-truck"></i></a></div>

    <!-- Modal Language-->
    <div class="modal fade" id="languageModel" tabindex="-1" role="dialog" aria-labelledby="languageModel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="languageModel">{{ __('Choose a language') }}</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true"><i class="icon-cross2"></i></span>
            </button>
          </div>
          <div class="modal-body text-center">
            @if (is_plugin_active('language'))
                {!! Theme::partial('language-switcher-full') !!}
            @endif
          </div>
        </div>
      </div>
    </div>

    @if (is_plugin_active('ecommerce'))
        {!! Theme::partial('ecommerce.quick-view-modal') !!}
    @endif
    {!! Theme::partial('toast') !!}

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

    <script>
        'use strict';

        window.trans = {
            "View All": "{{ __('View All') }}",
            "No reviews!": "{{ __('No reviews!') }}"
        };

        window.siteConfig = {
            "url": "{{ BaseHelper::getHomepageUrl() }}",
            "img_placeholder": "{{ theme_option('lazy_load_image_enabled', 'yes') == 'yes' ? image_placeholder() : null }}",
            "countdown_text": {
                "days": "{{ __('days') }}",
                "hours": "{{ __('hours') }}",
                "minutes": "{{ __('mins') }}",
                "seconds": "{{ __('secs') }}"
            }
        };

        @if (is_plugin_active('ecommerce') && EcommerceHelper::isCartEnabled())
            window.siteConfig.ajaxCart = "{{ route('public.ajax.cart') }}";
            window.siteConfig.cartUrl = "{{ route('public.cart') }}";
        @endif

        function checkPincode(fromPincode) {
            var toPincode = $('#topincode').val();
            var productWeight = $('#product_weight').val();

            if (toPincode.length != 6) {
                $('.pincodetext').html("Please enter a valid 6-digit pincode.").css("color", "red").show();
                return;
            }

            $.ajax({
                url: "/ajax/check-pincode-shiprocket", // backend route
                method: "POST",
                data: {
                    from_pincode: fromPincode,
                    to_pincode: toPincode,
                    product_weight: productWeight,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function () {
                    $('.pincodetext').html("Checking...").css("color", "blue").show();
                },
                success: function (response) {
                    if (response.serviceable) {
                        $('.pincodetext').html(`Delivery available. Expected on <strong>${response.estimated_delivery_days}.</strong>`).css("color", "green").show();
                    } else {
                        $('.pincodetext').html("Sorry! Delivery not available for this pincode.").css("color", "red").show();
                    }
                },
                error: function () {
                    $('.pincodetext').html("Error checking pincode. Please try making order directly.").css("color", "orange").show();
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Check if user already dismissed it in this session
            if (sessionStorage.getItem("topHeaderDismissed") === "true") {
                document.querySelector(".header-upper-top").classList.add("d-none");
            }

            document.getElementById("top-dismiss").addEventListener("click", function() {
                document.querySelector(".header-upper-top").classList.add("d-none");
                sessionStorage.setItem("topHeaderDismissed", "true");
            });
        });
    </script>

    {!! Theme::footer() !!}

    @include('packages/theme::toast-notification')
    </body>
    </html>
