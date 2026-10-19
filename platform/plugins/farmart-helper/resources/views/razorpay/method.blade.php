@if(get_payment_setting('status', 'razorpay') == 1)
<x-plugins-payment::payment-method name="razorpay" paymentName="Razorpay" :supportedCurrencies="$paymentService->supportedCurrencyCodes()">
    @if((float)$amount < $paymentService->getMinimumOrderAmount())
    <div class="alert alert-warning my-2">{{ trans('plugins/razorpay::razorpay.minimum_amount_warning', ['amount' => format_price($paymentService->getMinimumOrderAmount())]) }}</div>
    @endif
</x-plugins-payment::payment-method>
@endif
