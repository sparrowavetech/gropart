<?php
namespace SparroWave\FarmartHelper\Http\Controllers;

use Botble\Razorpay\Http\Controllers\RazorpayController;

/** Preserve verified partial amounts on the gateway's duplicate callback/webhook path. */
class PartialPaymentRazorpayController extends RazorpayController
{
    protected function saveOrUpdatePayment(string $chargeId, array|string|null $orderId, float $amount, string $currency, ?string $status, ?int $customerId = null, ?string $customerType = null, string $context = 'callback'): void
    {
        $data = ['charge_id'=>$chargeId, 'order_id'=>$orderId, 'amount'=>$amount, 'currency'=>$currency,
            'status'=>$status, 'customer_id'=>$customerId, 'customer_type'=>$customerType, 'payment_channel'=>'razorpay'];
        if (apply_filters('ecommerce_handle_verified_partial_payment', false, $data)) return;
        parent::saveOrUpdatePayment($chargeId, $orderId, $amount, $currency, $status, $customerId, $customerType, $context);
    }
}
