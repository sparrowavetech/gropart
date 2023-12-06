<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >
    <title> @yield('title', __('Checkout')) </title>

    @if (theme_option('favicon'))
        <link
            href="{{ RvMedia::getImageUrl(theme_option('favicon')) }}"
            rel="shortcut icon"
        >
    @endif

    {!! Html::style('vendor/core/core/base/libraries/font-awesome/css/fontawesome.min.css') !!}
    {!! Html::style('vendor/core/plugins/ecommerce/css/front-theme.css?v=3.2.0') !!}

    @if (BaseHelper::isRtlEnabled())
        {!! Html::style('vendor/core/plugins/ecommerce/css/front-theme-rtl.css?v=3.2.0') !!}
    @endif

    {!! Html::style('vendor/core/core/base/libraries/toastr/toastr.min.css') !!}

    {!! Html::script('vendor/core/plugins/ecommerce/js/checkout.js?v=3.2.0') !!}

    @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
        <link
            href="{{ asset('vendor/core/core/base/libraries/select2/css/select2.min.css') }}"
            rel="stylesheet"
        >
        <script src="{{ asset('vendor/core/core/base/libraries/select2/js/select2.min.js') }}"></script>
        <script src="{{ asset('vendor/core/plugins/location/js/location.js') }}?v=3.2.0"></script>
    @endif

    {!! apply_filters('ecommerce_checkout_header', null) !!}

    @stack('header')

    <style type="text/css">
        .checkout-product-img-wrapper .img-thumbnail { width: 100%; }
        .btn.payment-checkout-btn { vertical-align: bottom; font-size: 1rem; padding: 10px 30px; font-weight: 600; text-transform: uppercase; background-color: #198754; display: block; }
        .btn.payment-checkout-btn:hover { background-color: #00b460!important; }
        .thank-you i { margin-bottom: 10px; color: #198754; vertical-align: middle; }
        .thank-you .thank-you-sentence, .thank-you p { line-height: 1; margin:0 }
        .order-customer-info .h3, .order-customer-info h3 { font-weight: 700; text-transform: uppercase; font-size: 1rem; }
        .order-customer-info p span:first-child { min-width: 100px; font-weight: 600; text-transform: capitalize; }
        .order-customer-info { background: #f5f5f5; margin: 0 0 10px; padding: 15px; }
        .thank-you { margin-bottom: 20px; text-align: center; }
        .order-customer-info p { color: #000; font-size: .75rem; margin-bottom: 3px; display: flex; }
        .order-number-data .od-no {font-size: 1.5rem;text-transform: capitalize; text-decoration: underline; text-align: center; margin-bottom: 50px;}
        .price-text span { vertical-align: text-bottom; }
        .pricing-data .price-text { font-size: 18px; padding-bottom: 15px; }
        .pricing-data .price-text-label { font-size: 18px; font-weight: 600; }
        .pricing-data .price-text:after { content: ""; display: block; position: absolute; height: 1px; width: 95%; left: 10px; background: #ccc; margin-top: 2px; }
        .price-text, .total-text { color: #000000; font-weight: 600; }
        .thank-you-links { text-align: center; }
        .thank-you-links .link-text a { line-height: 1; font-size: 1rem; color: #888; padding: 10px; border-radius: 5px; text-transform: capitalize; text-decoration: underline; font-weight: 600; border: 2px solid rgb(173 173 173); }
    </style>
</head>

<body
    class="checkout-page"
    @if (BaseHelper::isRtlEnabled()) dir="rtl" @endif
>
    {!! apply_filters('ecommerce_checkout_body', null) !!}
    <div class="checkout-content-wrap">
        <div class="container">
            <div class="row">
                @yield('content')
            </div>
        </div>
    </div>

    @stack('footer')

    {!! Html::script('vendor/core/plugins/ecommerce/js/utilities.js') !!}
    {!! Html::script('vendor/core/core/base/libraries/toastr/toastr.min.js') !!}

    <script type="text/javascript">
        window.messages = {
            error_header: '{{ __('Error') }}',
            success_header: '{{ __('Success') }}',
        }
    </script>

    @if (session()->has('success_msg') || session()->has('error_msg') || isset($errors))
        <script type="text/javascript">
            $(document).ready(function() {
                @if (session()->has('success_msg') && session('success_msg'))
                    MainCheckout.showNotice('success', '{{ session('success_msg') }}');
                @endif
                @if (session()->has('error_msg'))
                    MainCheckout.showNotice('error', '{{ session('error_msg') }}');
                @endif
                @if (isset($errors) && $errors->count())
                    MainCheckout.showNotice('error', '{{ $errors->first() }}');
                @endif
            });
        </script>
    @endif

    {!! apply_filters('ecommerce_checkout_footer', null) !!}

</body>

</html>
