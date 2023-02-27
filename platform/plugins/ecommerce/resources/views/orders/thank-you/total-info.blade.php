@include('plugins/ecommerce::orders.thank-you.total-row', [
    'label' => __('Subtotal'),
    'value' => format_price($order->sub_total)
])
@if (EcommerceHelper::isTaxEnabled() && $order->tax_amount > 0)
    @include('plugins/ecommerce::orders.thank-you.total-row', [
        'label' => __('Tax'),
        'value' => format_price($order->tax_amount)
    ])
@endif
@if ($order->shipping_method->getValue() && $order->shipping_amount > 0)
    @include('plugins/ecommerce::orders.thank-you.total-row', [
            'label' =>  __('Shipping fee') . ($order->is_free_shipping ? ' <small>(' . __('Using coupon code') . ': <strong>' . $order->coupon_code . '</strong>)</small>' : '') . '<br><span><em>(' . $order->shipping_method_name . ')</span></em>',
            'value' => format_price($order->shipping_amount)
        ])
@endif

@if ($order->discount_amount !== null && $order->discount_amount > 0)
    @include('plugins/ecommerce::orders.thank-you.total-row', [
        'label' => __('Discount'),
        'value' => format_price($order->discount_amount) . ($order->coupon_code ? ' <small>(' . __('Using coupon code') . ': <strong>' . $order->coupon_code . '</strong>)</small>' : ''),
    ])
@endif



<hr>

<div class="row">
    <div class="col-6">
        <p class="fw-bold">{{ __('Total') }}:</p>
    </div>
    <div class="col-6 float-end">
        <p class="total-text raw-total-text"> {{ format_price($order->amount) }} </p>
    </div>
</div>
