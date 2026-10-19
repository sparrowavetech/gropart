<?php

namespace SparroWave\FarmartHelper\Supports;

use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;

class CheckoutPaymentPolicy
{
    public function blocksFinalization(Order $order, ?string $chargeId = null): bool
    {
        if (!apply_filters('ecommerce_order_can_finalize', true, $order)) return true;
        if ($order->is_finished) {
            return false;
        }

        $payments = Payment::query()->where('order_id', $order->getKey());
        $payment = null;
        if ($chargeId) {
            $payment = (clone $payments)->where('charge_id', $chargeId)->first();
        }
        if (! $payment) {
            // A failed attempt from another charge must not hide an actual capture.
            if ((clone $payments)->where('status', PaymentStatusEnum::COMPLETED)->exists()) {
                return false;
            }
            $payment = $order->payment_id
                ? (clone $payments)->whereKey($order->payment_id)->first()
                : (clone $payments)->latest('id')->first();
        }

        return $payment
            && $payment->payment_channel == 'razorpay'
            && $payment->status != PaymentStatusEnum::COMPLETED;
    }
}
