    <footer id="footer">
        <div class="footer-info border-top">
            <div class="container-xxxl py-3">
                {!! dynamic_sidebar('pre_footer_sidebar') !!}
            </div>
        </div>
        @if (Widget::group('footer_sidebar')->getWidgets())
            <div class="footer-widgets">
                <div class="container-xxxl">
                    <div class="row border-top py-5">
                        {!! dynamic_sidebar('footer_sidebar') !!}
                    </div>
                </div>
            </div>
        @endif
        @if (Widget::group('bottom_footer_sidebar')->getWidgets())
            <div class="container-xxxl">
                <div class="footer__links" id="footer-links">
                    {!! dynamic_sidebar('bottom_footer_sidebar') !!}
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
                                <a href="{{ url(theme_option('payment_methods_link')) }}" target="_blank">
                            @endif

                            <img class="lazyload"
                                data-src="{{ RvMedia::getImageUrl(theme_option('payment_methods_image')) }}" alt="footer-payments">

                            @if (theme_option('payment_methods_link'))
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="col-lg-3 col-md-4 py-3">
                    <div class="footer-socials d-flex justify-content-md-end justify-content-center">
                        @if (theme_option('social_links'))
                            <p class="me-3 mb-0">{{ __('Stay connected:') }}</p>
                            <div class="footer-socials-container">
                                <ul class="ps-0 mb-0">
                                    @foreach(json_decode(theme_option('social_links'), true) as $socialLink)
                                        @if (count($socialLink) == 3)
                                            <li class="d-inline-block ps-1 my-1">
                                                <a target="_blank" href="{{ Arr::get($socialLink[2], 'value') }}" title="{{ Arr::get($socialLink[0], 'value') }}">
                                                    <img class="lazyload" data-src="{{ RvMedia::getImageUrl(Arr::get($socialLink[1], 'value')) }}"
                                                        alt="{{ Arr::get($socialLink[0], 'value') }}" />
                                                </a>
                                            </li>
                                        @endif
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
                        <use href="#svg-icon-arrow-left" xlink:href="#svg-icon-arrow-left"></use>
                    </svg>
                </span>
                <h3>{{ __('Categories') }}</h3>
            </div>
            <div class="panel__content">
                <ul class="menu--mobile">
                    {!! Theme::partial('product-categories-dropdown', compact('categories')) !!}
                </ul>
            </div>
        </div>
    @endif

    <div class="panel--sidebar" id="menu-mobile">
        <div class="panel__header">
            <span class="svg-icon close-toggle--sidebar">
                <svg>
                    <use href="#svg-icon-arrow-left" xlink:href="#svg-icon-arrow-left"></use>
                </svg>
            </span>
            <h3>{{ __('Menu') }}</h3>
        </div>
        <div class="panel__content">
            {!! Menu::renderMenuLocation('main-menu', [
                'view'    => 'menu',
                'options' => ['class' => 'menu--mobile'],
            ]) !!}

            {!! Menu::renderMenuLocation('header-navigation', [
                'view'    => 'menu',
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

                    @php $currencies = get_all_currencies(); @endphp
                    @if (count($currencies) > 1)
                        <li class="menu-item-has-children">
                            <a href="#">
                                <span>{{ get_application_currency()->title }}</span>
                                <span class="sub-toggle">
                                <span class="svg-icon">
                                    <svg>
                                        <use href="#svg-icon-chevron-down" xlink:href="#svg-icon-chevron-down"></use>
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
                                                <use href="#svg-icon-chevron-down" xlink:href="#svg-icon-chevron-down"></use>
                                            </svg>
                                        </span>
                                    </span>
                                </a>
                                <ul class="sub-menu">
                                    @foreach ($supportedLocales as $localeCode => $properties)
                                        @if ($localeCode != Language::getCurrentLocale())
                                            <li>
                                                <a href="{{ Language::getSwitcherUrl($localeCode, $properties['lang_code']) }}">
                                                    @if ($languageDisplay == 'all' || $languageDisplay == 'flag'){!! language_flag($properties['lang_flag'], $properties['lang_name']) !!}@endif
                                                    @if ($languageDisplay == 'all' || $languageDisplay == 'name')<span>{{ $properties['lang_name'] }}</span>@endif
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
    <div class="panel--sidebar panel--sidebar__right" id="search-mobile">
        <div class="panel__header">
            @if (is_plugin_active('ecommerce'))
            <form class="form--quick-search w-100" action="{{ route('public.products') }}" data-ajax-url="{{ route('public.ajax.search-products') }}" method="get">
                <div class="search-inner-content">
                    <div class="text-search">
                        <div class="search-wrapper">
                            <input class="search-field input-search-product" name="q" type="text" placeholder="{{ __('Search something...') }}" autocomplete="off">
                            <button class="btn" type="submit">
                                <span class="svg-icon">
                                    <svg>
                                        <use href="#svg-icon-search" xlink:href="#svg-icon-search"></use>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <a class="close-search-panel close-toggle--sidebar" href="#">
                            <span class="svg-icon">
                                <svg>
                                    <use href="#svg-icon-times" xlink:href="#svg-icon-times"></use>
                                </svg>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="panel--search-result"></div>
            </form>
            @endif
        </div>
    </div>
    <div class="footer-mobile">
        <ul class="menu--footer">
            <li>
                <a href="{{ route('public.index') }}">
                    <i class="icon-home3"></i>
                    <span>{{ __('Home') }}</span>
                </a>
            </li>
            @if (is_plugin_active('ecommerce'))
                <li>
                    <a class="toggle--sidebar" href="#navigation-mobile">
                        <i class="icon-list"></i>
                        <span>{{ __('Category') }}</span>
                    </a>
                </li>
                @if (EcommerceHelper::isCartEnabled())
                    <li>
                        <a class="toggle--sidebar" href="#cart-mobile">
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

    <div id="rightFloatingBar" class="rightFloatingBar"><div class="dockerTab"><i class="fa fa-chevron-circle-up"></i></div>@if ($supportedLocales && count($supportedLocales) > 1)<a href="#" data-bs-toggle="modal" data-bs-target="#languageModel" class="langChange" style="margin-right:-6px;"><i class="fa fa-language"></i></a>@endif @if (theme_option('hotline'))<a href="http://api.whatsapp.com/send?phone={{ theme_option('hotline') }}" class="supportWhatsApp"><i class="fa fa-whatsapp"></i></a>@endif<a class="orderTrack" href="{{ route('public.orders.tracking') }}"><i class="fa fa-truck"></i></a></div>

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
                <use href="#svg-icon-arrow-up" xlink:href="#svg-icon-arrow-up"></use>
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
            "url"            : "{{ route('public.index') }}",
            "img_placeholder": "{{ theme_option('lazy_load_image_enabled', 'yes') == 'yes' ? image_placeholder() : null }}",
            "countdown_text" : {
                "days"   : "{{ __('days') }}",
                "hours"  : "{{ __('hours') }}",
                "minutes": "{{ __('mins') }}",
                "seconds": "{{ __('secs') }}"
            }
        };

        @if (is_plugin_active('ecommerce') && EcommerceHelper::isCartEnabled())
            siteConfig.ajaxCart = "{{ route('public.ajax.cart') }}";
            siteConfig.cartUrl = "{{ route('public.cart') }}";
        @endif
    </script>

    {!! Theme::footer() !!}

     @if (session()->has('success_msg') || session()->has('error_msg') || (isset($errors) && $errors->count() > 0) || isset($error_msg))
         <script type="text/javascript">
             window.onload = function () {
                 @if (session()->has('success_msg'))
                    MartApp.showSuccess('{{ session('success_msg') }}');
                 @endif

                 @if (session()->has('error_msg'))
                    MartApp.showError('{{ session('error_msg') }}');
                 @endif

                 @if (isset($error_msg))
                    MartApp.showError('{{ $error_msg }}');
                 @endif

                 @if (isset($errors))
                     @foreach ($errors->all() as $error)
                        MartApp.showError('{!! BaseHelper::clean($error) !!}');
                     @endforeach
                 @endif
             };
         </script>
     @endif

     <script>
        $('.fqtcheckbox').change(function(){
            calculate();
        });
        function calculate(){
            var amt = 0;
            $('.comboamount').html(`<img class="mx-auto" style="height:100px" src="{{ image_placeholder('images/placeholder.png') }}" />`);

            $('.fqtcheckbox:checked').each(function() {
                var cuamt = $(this).attr('data-price');
                    amt = amt + parseFloat(cuamt);

            });
            $.ajax({
                url: "{{ route('public.ajax.get-combo-price')}}/"+amt,
                type:"GET",
                success:function(res){
                    $('.comboamount').html(res);
                }
            })
        }
        function addToCartAll(){
            $("#preloader").show();
            var checkboxArr=[];
            $('.fqtcheckbox:checked').each(function() {
                checkboxArr.push($(this).val());
            });
            console.log(checkboxArr);
            $.ajax({
                    type: "POST",
                    url: "{{ route('public.cart.multiple')}}",
                    data: {id:checkboxArr},
                    success: function(data) {
                        if(data.error == false){
                            MartApp.showSuccess(data.message);
                        }else{
                            MartApp.showSuccess(data.message);
                        }
                        $("#preloader").hide();
                        MartApp.loadAjaxCart();
                    },
                    error: function(data) {
                    }
                });

        }
        function checkPincode(formPincode){
           var toPincode = $('#pincodetext').val();
           if(toPincode !=''){
            $.ajax({
                    type: "GET",
                    url: "{{ route('public.ajax.check-pincode')}}/"+formPincode+"/"+toPincode,
                    success: function(data) {
                        if(data){
                            $('.picodetext').removeClass('alert alert-danger');
                            $('.picodetext').text('Delivery available at your location').addClass('alert alert-success').show();
                        }else{
                            $('.picodetext').removeClass('alert alert-success');
                            $('.picodetext').text('Delivery not available at your location').addClass('alert alert-danger').show();
                        }

                    },
                    error: function(data) {
                        $('.picodetext').removeClass('alert alert-success');
                        $('.picodetext').text('Error in check pincode').addClass('alert alert-danger').show();
                    }
                });
            }else{
                $('.picodetext').removeClass('alert alert-success');
                $('.picodetext').text('Please enter pincode').addClass('alert alert-danger').show();
            }
        }
    </script>
    @if (EcommerceHelper::isZipCodeEnabled())
        <script type="text/javascript">
            $(document).ready(function () {
                $(document).on("keyup","#zip_code",function(){
                    var toPincode = $(this).val();
                    var formPincode = $(this).data('pincode');
                    if(toPincode !=''){
                        $.ajax({
                            type: "GET",
                            url: "{{ route('public.ajax.check-pincode')}}/"+formPincode+"/"+toPincode,
                            success: function(data) {
                                $(".picodetext.customer-panel").remove();
                                if(data) {
                                    $(".customer-address-button").removeAttr('disabled');
                                    $("#zip_code").after("<p class='picodetext customer-panel alert alert-success mt-2'><i class='fa fa-check-circle'></i> Congratulations! Delivery is available on your location.").show();
                                } else {
                                    $(".customer-address-button").attr('disabled','disabled');
                                    $("#zip_code").after("<p class='picodetext customer-panel alert alert-danger mt-2'><i class='fa fa-times-circle'></i> Sorry! Delivery not available at your location.").show();
                                }
                            },
                            error: function(data) {
                                $(".picodetext.customer-panel").remove();
                                $(".customer-address-button").attr('disabled','disabled');
                                $("#zip_code").after("<p class='picodetext customer-panel alert alert-danger mt-2'><i class='fa fa-times-circle'></i> Error in checking pincode! Please try later.").show();
                            }
                        });
                    } else {
                        $(".picodetext.customer-panel").remove();
                        $(".customer-address-button").attr('disabled','disabled');
                        $(this).after("<p class='picodetext customer-panel alert alert-warning mt-2'><i class='fa fa-circle-info'></i> Please enter pincode!").show();
                    }
                });
            });
        </script>
    @endif
    </body>
</html>
