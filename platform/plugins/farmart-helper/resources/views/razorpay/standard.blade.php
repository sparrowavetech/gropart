<!doctype html>
<html lang="{{ app()->getLocale() }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ __('Complete payment') }}</title>
<style>body{font-family:system-ui,sans-serif;max-width:38rem;margin:12vh auto;padding:24px;text-align:center}button,a{display:inline-block;margin:12px;padding:12px 20px}button{background:#00a651;color:white;border:0;border-radius:6px;cursor:pointer}</style></head>
<body><h1>{{ __('Complete payment') }}</h1><p>{{ __('Pay securely with Razorpay') }}</p><p>{{ format_price($options['amount'] / 100) }}</p>
<button id="open-payment" type="button" disabled>{{ __('Continue to payment') }}</button><a href="{{ $retryUrl }}">{{ __('Return to checkout') }}</a><p id="payment-message" role="status"></p>
<script>
const options = {{ Illuminate\Support\Js::from($options) }};
const button=document.getElementById('open-payment'),message=document.getElementById('payment-message');
function paymentReady(){
    options.modal={ondismiss:function(){button.disabled=false;message.textContent=@json(__('Payment was not completed. You can retry or return to checkout.'));}};
    const checkout=new Razorpay(options);
    checkout.on('payment.failed',function(){button.disabled=false;message.textContent=@json(__('Payment failed. You can retry or return to checkout.'));});
    button.disabled=false;button.onclick=function(){message.textContent='';checkout.open();};button.click();
}
function paymentUnavailable(){message.textContent=@json(__('Payment could not load. Please return to checkout and retry.'));}
</script><script src="https://checkout.razorpay.com/v1/checkout.js" onload="paymentReady()" onerror="paymentUnavailable()"></script></body></html>
