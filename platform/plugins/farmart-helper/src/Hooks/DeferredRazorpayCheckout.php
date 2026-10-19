<?php
namespace SparroWave\FarmartHelper\Hooks;

use Botble\Razorpay\Providers\HookServiceProvider;
use Botble\Razorpay\Services\Gateways\RazorpayPaymentService;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Theme\Facades\Theme;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class DeferredRazorpayCheckout extends HookServiceProvider
{
    public function registerRazorpayMethod(?string $html, array $data): string
    {
        if (!get_payment_setting('key', 'razorpay') || !get_payment_setting('secret', 'razorpay')) return $html ?? '';
        // Rendering payment options must not create a gateway order for an unfinished total.
        PaymentMethods::method('razorpay', ['html' => view('plugins/farmart-helper::razorpay.method', [
            'paymentService' => new RazorpayPaymentService(), 'amount' => $data['amount'] ?? 0,
        ])->render()]);
        return $html ?? '';
    }

    public function checkoutWithRazorpay(array $data, Request $request): array
    {
        if (($data['type'] ?? null) !== 'razorpay' || get_payment_setting('payment_type', 'razorpay', 'hosted_checkout') !== 'website_embedded') {
            return parent::checkoutWithRazorpay($data, $request);
        }
        // Legacy, already-open Standard Checkout callbacks retain the author's signature verification.
        if ($request->filled('razorpay_payment_id')) return parent::checkoutWithRazorpay($data, $request);
        $payment = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);
        $service = new RazorpayPaymentService();
        if ((float)$payment['amount'] < $service->getMinimumOrderAmount()) {
            return array_merge($data, ['error' => true, 'message' => trans('plugins/razorpay::razorpay.minimum_amount_error', ['amount' => format_price($service->getMinimumOrderAmount())])]);
        }
        $notes = array_merge([
            'order_id' => implode(',', (array)$payment['order_id']), 'order_token' => $payment['checkout_token'],
            'customer_name' => $payment['address']['name'], 'customer_email' => $payment['address']['email'], 'customer_phone' => $payment['address']['phone'],
        ], $this->getShippingAddressNotes($payment['order_id']));
        $payload = ['receipt' => $payment['checkout_token'], 'amount' => (int)round($payment['amount'] * 100), 'currency' => $payment['currency'], 'notes' => $notes];
        try {
            do_action('payment_before_making_api_request', 'razorpay', $payload);
            $order = $this->createGatewayOrder($payload);
            do_action('payment_after_api_response', 'razorpay', $payload, $order);
        } catch (\Exception $exception) {
            \Botble\Base\Facades\BaseHelper::logError($exception);
            return array_merge($data, ['error' => true, 'message' => $exception->getMessage()]);
        }
        $options = [
            'key' => get_payment_setting('key', 'razorpay'), 'order_id' => $order['id'], 'amount' => $payload['amount'], 'currency' => $payload['currency'],
            'name' => Theme::getSiteTitle(), 'description' => $payment['description'],
            'image' => Theme::getLogo() ? RvMedia::getImageUrl(Theme::getLogo()) : null,
            'prefill' => ['name' => $payment['address']['name'], 'email' => $payment['address']['email'], 'contact' => $payment['address']['phone']],
            'notes' => $notes, 'redirect' => true,
            'callback_url' => route('payments.razorpay.callback', ['token' => $payment['checkout_token'], 'customer_id' => $payment['customer_id'], 'customer_type' => $payment['customer_type'], 'order_id' => $payment['order_id']]),
        ];
        if ($request->hasSession()) $request->session()->save();
        $this->present($options, route('public.checkout.recover', $payment['checkout_token']));
        return $data;
    }

    protected function createGatewayOrder(array $payload): array
    {
        $api = new Api(get_payment_setting('key', 'razorpay'), get_payment_setting('secret', 'razorpay'));
        return $api->order->create($payload)->toArray();
    }

    protected function present(array $options, string $retryUrl): void
    {
        // Like the core hosted checkout response, this terminates before unpaid order finalization.
        response()->view('plugins/farmart-helper::razorpay.standard', compact('options', 'retryUrl'))->send();
        exit;
    }
}
