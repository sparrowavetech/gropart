<?php

namespace SparroWave\FarmartHelper\Http\Middleware;

use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Shipment;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentFeeHelper;
use Closure;
use Illuminate\Http\Request;

class ProcessNormalCodIncompleteOrder
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->routeIs('orders.mark-as-completed') && $request->isMethod('POST')) {
            $value = $request->route('order');
            $order = $value instanceof Order ? $value : Order::query()->find($value);

            if ($order && $request->input('payment_method') === 'cod') {
                $order->refresh();

                if ($order->is_finished) {
                    $base = round((float) $order->amount - (float) $order->payment_fee, 2);
                    $fee = round(PaymentFeeHelper::calculateFee('cod', $base), 2);
                    $total = round($base + $fee, 2);

                    $order->payment_fee = $fee;
                    $order->amount = $total;
                    $order->cod_prepayment_amount = 0.0;
                    $order->cod_remaining_amount = $total;
                    $order->saveQuietly();

                    $payment = Payment::query()->where('order_id', $order->id)->latest('id')->first();
                    if ($payment) {
                        $payment->update([
                            'amount' => $total,
                            'payment_fee' => $fee,
                            'payment_channel' => 'cod',
                            'status' => PaymentStatusEnum::PENDING,
                        ]);
                        if (! $order->payment_id) {
                            $order->payment_id = $payment->id;
                            $order->saveQuietly();
                        }
                    }

                    Shipment::query()
                        ->where('order_id', $order->id)
                        ->update([
                            'cod_amount' => $total,
                            'cod_status' => ShippingCodStatusEnum::PENDING,
                        ]);

                    $order->histories()->create([
                        'user_id' => auth()->id() ?: 0,
                        'action' => 'update_cod_status',
                        'description' => sprintf(
                            'Normal COD: Cash collection fee of %s added. Total %s to be collected on delivery.',
                            format_price($fee),
                            format_price($total)
                        ),
                    ]);
                }
            }
        }

        return $response;
    }
}
