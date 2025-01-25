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
                    <!--@if (Cart::instance('cart')->rawTax())
                        (<small>{{ Cart::instance('cart')->taxClassesName() }}</small>)
                    @endif-->
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
            <p class="total-text-label price-text-label m-0"><strong>{{ __('Total') }}</strong>:</p>
        </div>
        <div class="col-4 float-end">
            <p class="total-text raw-total-text m-0" data-price="{{ format_price($rawTotal, null, true) }}">
                {{ format_price($orderAmount) }}
            </p>
        </div>
    </div>
</div>
