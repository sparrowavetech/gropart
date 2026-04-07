{!! apply_filters(RENDER_PRODUCTS_IN_CHECKOUT_PAGE, $products) !!}

@php
    $cartSubTotal = Cart::instance('cart')->rawSubTotal();
    $cartTax = Cart::instance('cart')->rawTax();
    $hasShipping = ! empty($shipping) && Arr::get($sessionCheckoutData, 'is_available_shipping', true) && $shippingAmount > 0;
    $hasDiscount = $couponDiscountAmount > 0 || $promotionDiscountAmount > 0;
    $hasPaymentFee = isset($paymentFee) && $paymentFee > 0;
    $showSubtotal = $cartSubTotal != $orderAmount || $cartTax > 0 || $hasShipping || $hasDiscount || $hasPaymentFee;
@endphp

<div class="mt-2 p-2">
    @if ($showSubtotal)
        <div class="row ec-checkout-subtotal-row">
            <div class="col-6">
                <p>{{ __('Subtotal') }}:</p>
            </div>
            <div class="col-6">
                <p class="price-text sub-total-text text-end">
                    {{ $cartSubTotal == 0 ? trans('plugins/ecommerce::ecommerce.free') : format_price($cartSubTotal) }}
                </p>
            </div>
        </div>
    @endif
    {!! apply_filters('ecommerce_checkout_after_subtotal', null, $products) !!}
    @if (EcommerceHelper::isTaxEnabled() && $cartTax > 0)
        @php
            $taxComponents = [];
            foreach ($products as $cartItem) {
                if ($cartItem->taxRate > 0) {
                    // Simulate GST Split (50% CGST, 50% SGST) for now
                    // In a real scenario, we would check the shipping address state vs store state.
                    // If complex logic is needed, we should move this to a Service class.
                    
                    $taxAmount = ($cartItem->qty * $cartItem->price) * ($cartItem->taxRate / 100);
                    if ($cartItem->options->get('price_includes_tax')) {
                        $taxAmount = ($cartItem->qty * $cartItem->price) - (($cartItem->qty * $cartItem->price) / (1 + $cartItem->taxRate / 100));
                    }
                    
                    $halfRate = $cartItem->taxRate / 2;
                    $halfAmount = $taxAmount / 2;
                    
                    // CGST
                    $cgstKey = 'CGST-' . $halfRate;
                    if (!isset($taxComponents[$cgstKey])) {
                        $taxComponents[$cgstKey] = ['name' => 'CGST (' . $halfRate . '%)', 'amount' => 0];
                    }
                    $taxComponents[$cgstKey]['amount'] += $halfAmount;
                    
                    // SGST
                    $sgstKey = 'SGST-' . $halfRate;
                    if (!isset($taxComponents[$sgstKey])) {
                        $taxComponents[$sgstKey] = ['name' => 'SGST (' . $halfRate . '%)', 'amount' => 0];
                    }
                    $taxComponents[$sgstKey]['amount'] += $halfAmount;
                }
            }
        @endphp

        @if (!empty($taxComponents))
            @foreach ($taxComponents as $tax)
                <div class="row ec-checkout-tax-row">
                    <div class="col-6">
                        <p>{{ $tax['name'] }}</p>
                    </div>
                    <div class="col-6 float-end">
                        <p class="price-text tax-price-text">
                            {{ format_price($tax['amount']) }}
                        </p>
                    </div>
                </div>
            @endforeach
        @else
            <div class="row ec-checkout-tax-row">
                <div class="col-6">
                    <p>{{ __('Tax') }} @if ($cartTax && EcommerceHelper::isDisplayCheckoutTaxInformation())
                        (<small>{{ Cart::instance('cart')->taxClassesName() }}</small>)
                    @endif</p>
                </div>
                <div class="col-6 float-end">
                    <p class="price-text tax-price-text">
                        {{ format_price($cartTax) }}
                    </p>
                </div>
            </div>
        @endif
    @endif
    @if (session('applied_coupon_code'))
        <div class="row coupon-information">
            <div class="col-6">
                <p>{{ __('Coupon code') }}:</p>
            </div>
            <div class="col-6">
                <p class="price-text coupon-code-text">
                    {{ session('applied_coupon_code') }}
                </p>
            </div>
        </div>
    @endif
    @if ($couponDiscountAmount > 0)
        <div class="row price discount-amount">
            <div class="col-6">
                <p>{{ __('Coupon code discount amount') }}:</p>
            </div>
            <div class="col-6">
                <p class="price-text total-discount-amount-text">
                    {{ format_price($couponDiscountAmount) }}
                </p>
            </div>
        </div>
    @endif
    @if ($promotionDiscountAmount > 0)
        <div class="row">
            <div class="col-6">
                <p>{{ __('Promotion discount amount') }}:</p>
            </div>
            <div class="col-6">
                <p class="price-text">
                    {{ format_price($promotionDiscountAmount) }}
                </p>
            </div>
        </div>
    @endif
    @if (!empty($shipping) && Arr::get($sessionCheckoutData, 'is_available_shipping', true))
        <div class="row">
            <div class="col-6">
                <p>{{ __('Shipping fee') }}:</p>
            </div>
            <div class="col-6 float-end">
                <p class="price-text shipping-price-text">{{ $shippingAmount > 0 ? format_price($shippingAmount) : trans('plugins/ecommerce::order.free_shipping') }}</p>
            </div>
        </div>
    @endif

    @if (isset($paymentFee) && $paymentFee > 0)
        <div class="row payment-fee-row">
            <div class="col-6">
                <p>{{ __('plugins/payment::payment.payment_fee') }}:</p>
            </div>
            <div class="col-6 float-end">
                <p class="price-text payment-fee-text">{{ format_price($paymentFee) }}</p>
            </div>
        </div>
    @endif

    <div class="row ec-checkout-total-row">
        <div class="col-6">
            <p><strong>{{ __('Total') }}</strong>:</p>
        </div>
        <div class="col-6 float-end">
            <p class="total-text raw-total-text" data-price="{{ format_price($rawTotal, null, true) }}">
                {{ $orderAmount == 0 ? trans('plugins/ecommerce::ecommerce.free') : format_price($orderAmount) }}
            </p>
        </div>
    </div>
</div>
