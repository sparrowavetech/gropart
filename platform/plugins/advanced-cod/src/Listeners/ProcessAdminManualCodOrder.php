<?php

namespace SparroWave\AdvancedCod\Listeners;

use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Enums\ShippingCodStatusEnum;
use Botble\Ecommerce\Models\Shipment;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Illuminate\Support\Str;
use SparroWave\AdvancedCod\Supports\AdvancePayment;
use SparroWave\AdvancedCod\Supports\CodAmounts;
use SparroWave\AdvancedCod\Supports\ManualConfirmation;
use SparroWave\AdvancedCod\Supports\Settlement;

class ProcessAdminManualCodOrder
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $request = request();

        $paymentMethod = $request->input('payment_method');
        if ($paymentMethod !== ManualConfirmation::CHANNEL) {
            return;
        }

        $orderTotal = (float) $order->amount;
        $prepaymentPercentage = CodAmounts::getPrepaymentPercentage();
        $split = CodAmounts::split($orderTotal, $prepaymentPercentage);
        $defaultAdvance = $split['advance'];

        $advanceInput = $request->input('advanced_cod_advance_amount');
        if ($advanceInput !== null && is_numeric($advanceInput) && (float) $advanceInput > 0 && (float) $advanceInput <= $orderTotal) {
            $advanceAmount = round((float) $advanceInput, 2);
            $rawRemaining = max(0, round($orderTotal - $advanceAmount, 2));

            // Ensure courier cash collection is strictly in whole rupees (no paise)
            $cashPaise = (int) round($rawRemaining * 100);
            if ($cashPaise % 100 !== 0) {
                // Round courier cash to nearest whole rupee, absorb residual into advance
                $remainingAmount = (float) round($rawRemaining);
                $advanceAmount = max(0, round($orderTotal - $remainingAmount, 2));
            } else {
                $remainingAmount = $rawRemaining;
            }
        } else {
            $advanceAmount = $defaultAdvance;
            $remainingAmount = $split['remaining'];
        }

        $channel = (string) $request->input('advanced_cod_channel', 'cash');
        if (!in_array($channel, ['cash', 'bank', 'upi'], true)) {
            $channel = 'cash';
        }

        $reference = trim((string) ($request->input('advanced_cod_reference') ?: $request->input('transaction_id') ?: ('ADMIN-' . strtoupper(Str::random(8)))));
        $note = trim((string) $request->input('advanced_cod_note', ''));
        $date = $request->input('advanced_cod_date') ?: now()->toDateTimeString();

        // 1. Update order advance and remaining fields
        $order->cod_prepayment_amount = $advanceAmount;
        $order->cod_remaining_amount = $remainingAmount;
        $order->saveQuietly();

        // 2. Set AdvancePayment intent metadata
        $intent = [
            'admin_manual' => true,
            'gateway' => ManualConfirmation::CHANNEL,
            'currency' => get_application_currency()->title,
            'total' => $orderTotal,
            'advance' => $advanceAmount,
            'remaining' => $remainingAmount,
            'percentage' => $prepaymentPercentage,
            'order_ids' => [$order->id],
            'fields' => array_intersect_key($order->getAttributes(), array_flip(['amount', 'sub_total', 'tax_amount', 'shipping_amount', 'shipping_tax_amount', 'payment_fee', 'discount_amount', 'shipping_method', 'shipping_option', 'coupon_code'])),
        ];
        $order->setOrderMetadata(AdvancePayment::KEY, json_encode($intent));

        // 3. Update or create the payment record
        $chargeId = 'manual-cod-' . hash('sha256', strtoupper($reference));
        $payment = Payment::query()->where('order_id', $order->id)->latest('id')->first();
        if ($payment) {
            $payment->amount = $advanceAmount;
            $payment->status = PaymentStatusEnum::COMPLETED;
            $payment->payment_channel = ManualConfirmation::CHANNEL;
            $payment->charge_id = $chargeId;
            $payment->save();

            $order->setOrderMetadata('advanced_cod_manual_receipt', (string) $payment->id);
        } else {
            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'amount' => $advanceAmount,
                'currency' => get_application_currency()->title,
                'payment_channel' => ManualConfirmation::CHANNEL,
                'charge_id' => $chargeId,
                'status' => PaymentStatusEnum::COMPLETED,
                'payment_fee' => 0,
                'user_id' => $order->user_id ?? 0,
            ]);
            $order->setOrderMetadata('advanced_cod_manual_receipt', (string) $payment->id);
        }

        // 4. Save manual confirmation record
        $confirmationRecord = [
            'mode' => 'manual',
            'amount' => $advanceAmount,
            'method' => $channel,
            'reference' => $reference,
            'occurred_at' => $date,
            'note' => $note,
            'actor_id' => auth()->id() ?: 0,
            'payment_id' => $payment?->id,
            'admin_created' => true,
        ];
        $order->setOrderMetadata(ManualConfirmation::KEY, json_encode($confirmationRecord));

        // 5. Add order history entry
        $order->histories()->create([
            'user_id' => auth()->id() ?: 0,
            'action' => 'advanced_cod_admin_manual_order',
            'description' => sprintf(
                'Advanced COD Admin Manual Order: Received advance %s via %s (Ref: %s). Remaining %s to be collected on delivery.',
                format_price($advanceAmount),
                strtoupper($channel),
                $reference,
                format_price($remainingAmount)
            ),
        ]);

        // 6. Terminating callback to ensure shipments and settlements are synced after HTTP response
        app()->terminating(function () use ($order, $remainingAmount) {
            $order->refresh();
            Settlement::sync($order);

            Shipment::query()->where('order_id', $order->id)->update([
                'cod_amount' => $remainingAmount,
                'cod_status' => ShippingCodStatusEnum::PENDING,
            ]);
        });
    }
}
