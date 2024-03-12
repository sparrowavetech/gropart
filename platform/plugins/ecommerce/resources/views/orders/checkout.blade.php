<style type="text/css">
    @media screen and (max-width: 768px) {
        .container, .left, .page-wrap, .right, body, html { height: auto !important; min-height: auto !important; }
        #main-checkout-product-info .coupon-wrapper, .accepted-payments { margin-bottom: 15px !important; }
        #main-checkout-product-info .checkout-discount-section { text-align: center; }
        .checkout-logo { text-align: center; }
        .back-to-cart-button-group { margin-bottom: 20px !important; }
        .checkout-form, .checkout-content-wrap { margin:0 !important; }
    }
    .form-group .iti.iti--allow-dropdown { width: 100%; }
    .text-right { text-align: right; }
    .btn.payment-checkout-btn-step.payment-checkout-btn { width: 100%; font-size: 1.25rem; color: #fff; padding: 10px 0; font-weight: 600; text-transform: uppercase; background-color: #198754; }
    .back-to-cart-btn { font-size: 1.25rem; font-weight: 600; }
    .accepted-payments { max-width: 420px; margin: auto; }
    .btn.payment-checkout-btn-step.payment-checkout-btn:hover { background-color: #00b460!important; }
</style>
@extends('plugins/ecommerce::orders.master')
@section('title', __('Checkout'))
@section('content')
    @if (Cart::instance('cart')->isNotEmpty())
        @php
            $rawTotal = Cart::instance('cart')->rawTotal();
            $orderAmount = max($rawTotal - $promotionDiscountAmount - $couponDiscountAmount, 0);
            $orderAmount += (float) $shippingAmount;
        @endphp

        @if (is_plugin_active('payment') && $orderAmount)
            @include('plugins/payment::partials.header')
        @endif

        <x-core::form :url="route('public.checkout.process', $token)" id="checkout-form" class="checkout-form payment-checkout-form">
            <input id="checkout-token" name="checkout-token" type="hidden" value="{{ $token }}">

            <div class="container" id="main-checkout-product-info">
                <div class="row">
                    <div class="order-1 order-md-2 col-lg-5 col-md-6 right">
                        <div class="d-block d-sm-none">
                            @include('plugins/ecommerce::orders.partials.logo')
                        </div>
                        <div class="position-relative" id="cart-item">
                            <div class="payment-info-loading" style="display: none;">
                                <div class="payment-info-loading-content">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </div>

                            {!! apply_filters(RENDER_PRODUCTS_IN_CHECKOUT_PAGE, $products) !!}

                            <div class="mt-2 p-3 bg-light pricing-data">
                                <div class="row">
                                    <div class="col-8">
                                        <p class="price-text-label m-0">{{ __('Subtotal') }}:</p>
                                    </div>
                                    <div class="col-4">
                                        <p class="price-text sub-total-text text-end m-0"> {{ format_price(Cart::instance('cart')->rawSubTotal()) }} </p>
                                    </div>
                                </div>
                                @if (EcommerceHelper::isTaxEnabled() && Cart::instance('cart')->rawTax() > 0)
                                    <div class="row">
                                        <div class="col-8">
                                            <p class="price-text-label m-0">{{ __('Tax') }}:</p>
                                        </div>
                                        <div class="col-4 float-end">
                                            <p class="price-text tax-price-text m-0 text-success"><span>(+)</span> {{ format_price(Cart::instance('cart')->rawTax()) }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if (session('applied_coupon_code'))
                                    <div class="row coupon-information">
                                        <div class="col-8">
                                            <p class="price-text-label m-0">{{ __('Coupon code') }}:</p>
                                        </div>
                                        <div class="col-4">
                                            <p class="price-text coupon-code-text m-0 text-success">{{ session('applied_coupon_code') }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if ($couponDiscountAmount > 0)
                                    <div class="row price discount-amount">
                                        <div class="col-8">
                                            <p class="price-text-label m-0">{{ __('Coupon code discount amount') }}:</p>
                                        </div>
                                        <div class="col-4">
                                            <p class="price-text total-discount-amount-text m-0 text-danger">
                                            <span>(-)</span> {{ format_price($couponDiscountAmount) }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                                @if ($promotionDiscountAmount > 0)
                                    <div class="row">
                                        <div class="col-8">
                                            <p class="price-text-label m-0">{{ __('Promotion discount amount') }}:</p>
                                        </div>
                                        <div class="col-4">
                                            <p class="price-text m-0 text-danger">
                                            <span>(-)</span> {{ format_price($promotionDiscountAmount) }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                                @if (!empty($shipping) && Arr::get($sessionCheckoutData, 'is_available_shipping', true))
                                    <div class="row">
                                        <div class="col-8">
                                            <p class="price-text-label m-0">{{ __('Shipping fee') }}:</p>
                                        </div>
                                        <div class="col-4 float-end">
                                            <p class="price-text shipping-price-text m-0 text-success"><span>(+)</span> {{ format_price($shippingAmount) }}</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-8">
                                        <p class="price-text-label m-0"><strong>{{ __('Total') }}</strong>:</p>
                                    </div>
                                    <div class="col-4 float-end">
                                        <p class="total-text raw-total-text" data-price="{{ format_price($rawTotal, null, true) }}">
                                            {{ format_price($orderAmount) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="mt-0" />
                        <div class="mt-3">
                            @include('plugins/ecommerce::themes.discounts.partials.form', compact('discounts'))
                        </div>
                        @if (theme_option('payment_methods_image'))
                            <hr/>
                            <div class="accepted-payments">
                                @if (theme_option('payment_methods_link'))
                                    <a href="{{ url(theme_option('payment_methods_link')) }}" target="_blank">
                                @endif

                                <img class="img-fluid" src="{{ RvMedia::getImageUrl(theme_option('payment_methods_image')) }}" alt="payments accepted">

                                @if (theme_option('payment_methods_link'))
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-7 col-md-6 left">
                        <div class="d-none d-sm-block">
                            <div class="container g-0">
                                <div class="row">
                                    <div class="col-sm-6">
                                        @include('plugins/ecommerce::orders.partials.logo')
                                    </div>
                                    <div class="col-sm-6 text-right">
                                        <div class="back-to-cart-button-group mt-3">
                                            <a class="back-to-cart-btn text-danger" href="{{ route('public.cart') }}"><i class="fas fa-long-arrow-alt-left"></i> <span class="d-inline-block">{{ __('Back to cart') }}</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="form-checkout">
                            {!! apply_filters('ecommerce_checkout_form_before', null, $products) !!}

                            @if ($isShowAddressForm)
                                <div>
                                    <h5 class="checkout-payment-title">{{ __('Shipping information') }}</h5>
                                    <input
                                        id="save-shipping-information-url"
                                        type="hidden"
                                        value="{{ route('public.checkout.save-information', $token) }}"
                                    >
                                    @include(
                                        'plugins/ecommerce::orders.partials.address-form',
                                        compact('sessionCheckoutData'))
                                </div>
                                <br>
                                {!! apply_filters('ecommerce_checkout_form_after_shipping_address_form', null, $products) !!}
                            @endif

                            @if (EcommerceHelper::isBillingAddressEnabled())
                                <div>
                                    <h5 class="checkout-payment-title">{{ __('Billing information') }}</h5>
                                    @include(
                                        'plugins/ecommerce::orders.partials.billing-address-form',
                                        compact('sessionCheckoutData'))
                                </div>
                                <br>
                                {!! apply_filters('ecommerce_checkout_form_after_billing_address_form', null, $products) !!}
                            @endif

                            @if (!is_plugin_active('marketplace'))
                                @if (Arr::get($sessionCheckoutData, 'is_available_shipping', true))
                                    <div id="shipping-method-wrapper">
                                        <h5 class="checkout-payment-title">{{ __('Shipping method') }}</h5>
                                        <div class="shipping-info-loading" style="display: none;">
                                            <div class="shipping-info-loading-content">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </div>
                                        </div>
                                        @if (!empty($shipping))
                                            <div class="payment-checkout-form">
                                                <input
                                                    name="shipping_option"
                                                    type="hidden"
                                                    value="{{ BaseHelper::stringify(old('shipping_option', $defaultShippingOption)) }}"
                                                >
                                                <ul class="list-group list_payment_method">
                                                    @foreach ($shipping as $shippingKey => $shippingItems)
                                                        @foreach ($shippingItems as $shippingOption => $shippingItem)
                                                            @include(
                                                                'plugins/ecommerce::orders.partials.shipping-option',
                                                                [
                                                                    'shippingItem' => $shippingItem,
                                                                    'attributes' => [
                                                                        'id' =>
                                                                            'shipping-method-' .
                                                                            $shippingKey .
                                                                            '-' .
                                                                            $shippingOption,
                                                                        'name' => 'shipping_method',
                                                                        'class' => 'magic-radio shipping_method_input',
                                                                        'checked' =>
                                                                            old(
                                                                                'shipping_method',
                                                                                $defaultShippingMethod) == $shippingKey &&
                                                                            old(
                                                                                'shipping_option',
                                                                                $defaultShippingOption) == $shippingOption,
                                                                        'disabled' => Arr::get(
                                                                            $shippingItem,
                                                                            'disabled'),
                                                                        'data-option' => $shippingOption,
                                                                    ],
                                                                ]
                                                            )
                                                        @endforeach
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <p>{{ __('No shipping methods available!') }}</p>
                                        @endif
                                    </div>
                                    <br>
                                    {!! apply_filters('ecommerce_checkout_form_after_shipping_address_form', null, $products) !!}
                                @endif
                            @endif

                            {!! apply_filters('ecommerce_checkout_form_before_payment_form', null, $products) !!}

                            <input
                                name="amount"
                                type="hidden"
                                value="{{ format_price($orderAmount, null, true) }}"
                            >

                            @if (is_plugin_active('payment') && $orderAmount)
                                @php
                                    $paymentMethods = apply_filters(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, null, [
                                            'amount' => format_price($orderAmount, null, true),
                                            'currency' => strtoupper(get_application_currency()->title),
                                            'name' => null,
                                            'selected' => PaymentMethods::getSelectedMethod(),
                                            'default' => PaymentMethods::getDefaultMethod(),
                                            'selecting' => PaymentMethods::getSelectingMethod(),
                                        ]) . PaymentMethods::render();
                                @endphp

                                <input
                                    name="currency"
                                    type="hidden"
                                    value="{{ strtoupper(get_application_currency()->title) }}"
                                >

                                @if($paymentMethods)
                                    <div class="position-relative mb-4">
                                        <div class="payment-info-loading" style="display: none;">
                                            <div class="payment-info-loading-content">
                                                <i class="fas fa-spinner fa-spin"></i>
                                            </div>
                                        </div>
                                        <h5 class="checkout-payment-title">{{ __('Payment method') }}</h5>

                                        {!! apply_filters(PAYMENT_FILTER_PAYMENT_PARAMETERS, null) !!}

                                        <ul class="list-group list_payment_method">
                                            {!! $paymentMethods !!}
                                        </ul>
                                    </div>
                                @endif
                            @endif

                            {!! apply_filters('ecommerce_checkout_form_after_payment_form', null, $products) !!}

                            <div @class(['form-group mb-3', 'has-error' => $errors->has('description')])>
                                <label class="form-label" for="description">{{ __('Order notes') }}</label>
                                <textarea
                                    class="form-control"
                                    id="description"
                                    name="description"
                                    rows="3"
                                    placeholder="{{ __('Notes about your order, e.g. special notes for delivery.') }}"
                                >{{ old('description') }}</textarea>
                                {!! Form::error('description', $errors) !!}
                            </div>

                            @if (EcommerceHelper::getMinimumOrderAmount() > Cart::instance('cart')->rawSubTotal())
                                <div role="alert" class="alert alert-warning">
                                    {{ __('Minimum order amount is :amount, you need to buy more :more to place an order!', ['amount' => format_price(EcommerceHelper::getMinimumOrderAmount()), 'more' => format_price(EcommerceHelper::getMinimumOrderAmount() - Cart::instance('cart')->rawSubTotal())]) }}
                                </div>
                            @endif

                            @if (EcommerceHelper::isDisplayTaxFieldsAtCheckoutPage())
                                @include(
                                    'plugins/ecommerce::orders.partials.tax-information',
                                    compact('sessionCheckoutData'))

                                {!! apply_filters('ecommerce_checkout_form_after_tax_information_form', null, $products) !!}
                            @endif

                            {!! apply_filters('ecommerce_checkout_form_after', null, $products) !!}

                            <div class="row align-items-center g-3">
                                <div class="order-2 order-md-1 col-md-6 text-center text-md-start mb-md-0">
                                    <a class="back-to-cart-btn text-danger" href="{{ route('public.cart') }}">
                                        <x-core::icon name="ti ti-arrow-narrow-left" />
                                        <span class="d-inline-block back-to-cart">{{ __('Back to cart') }}</span>
                                    </a>

                                    {!! apply_filters('ecommerce_checkout_form_after_back_to_cart_link', null, $products) !!}
                                </div>
                                <div class="order-1 order-md-2 col-md-6">
                                    @if (EcommerceHelper::isValidToProcessCheckout())
                                        <button id="checkoutBtn"
                                            class="btn payment-checkout-btn payment-checkout-btn-step float-end"
                                            data-processing-text="{{ __('Processing. Please wait...') }}"
                                            data-error-header="{{ __('Error') }}"
                                            type="submit" disabled
                                        >
                                            {{ __('Checkout') }}
                                        </button>
                                    @else
                                        <span class="btn payment-checkout-btn-step float-end disabled">
                                            {{ __('Checkout') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="ps-0 mt-2">
                                <p class="m-0 fs-6 text-default text-decoration-underline">
                                    <em>({{ __('I have read ') }} <a target="_BLANK" title="{{ _('refund and return policy') }}" href="{{ url(theme_option('ecommerce_checkout_policy_url')?:'') }}"><strong>{{ _('refund and return policy') }}</strong></a>)</em>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-core::form>

        @if (is_plugin_active('payment'))
            @include('plugins/payment::partials.footer')
        @endif
    @else
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning my-5">
                        <span>{!! __('No products in cart. :link!', ['link' => Html::link(route('public.index'), __('Back to shopping'))]) !!}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@push('footer')
    <script type="text/javascript" src="{{ asset('vendor/core/core/js-validation/js/js-validation.js') }}"></script>

    {!! JsValidator::formRequest(
        Botble\Ecommerce\Http\Requests\SaveCheckoutInformationRequest::class,
        '#checkout-form',
    ) !!}
@endpush
