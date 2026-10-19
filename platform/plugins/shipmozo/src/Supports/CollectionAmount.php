<?php

namespace SparroWave\Shipmozo\Supports;

use Botble\Ecommerce\Models\Order;
use Botble\Payment\Models\Payment;
use RuntimeException;

/** Resolve delivery collection from money actually received, independent of gateway name. */
class CollectionAmount
{
    public static function forOrder(Order $order): float
    {
        $override = apply_filters('ecommerce_cod_collection_override', false, $order);
        if ($override !== false) return (float)$override;
        $advance = $order->getAttribute('cod_prepayment_amount');
        $remaining = $order->getAttribute('cod_remaining_amount');
        if ($advance !== null && (float)$advance > 0 && $remaining !== null) {
            $received = (float) Payment::query()->where('order_id', $order->id)
                ->where('status', 'completed')->get()->sum(fn ($payment) => (float) $payment->amount - (float) $payment->refunded_amount);
            return (float) apply_filters('ecommerce_cod_collection_amount', self::partial((float) $order->amount, (float) $advance, $received), $order);
        }

        return (string) $order->payment?->payment_channel === 'cod' ? (float) $order->amount : 0.0;
    }

    public static function partial(float $total, float $requiredAdvance, float $received): float
    {
        if (!is_finite($total) || !is_finite($requiredAdvance) || !is_finite($received)) throw new RuntimeException('Invalid collection amount.');
        $total = (int) round($total * 100);
        $requiredAdvance = (int) round($requiredAdvance * 100);
        $received = (int) round($received * 100);
        if ($total < 0 || $requiredAdvance <= 0 || $requiredAdvance > $total || $received < $requiredAdvance || $received > $total) {
            throw new RuntimeException('COD payment amounts require reconciliation before shipping.');
        }
        return ($total - $received) / 100;
    }
}
