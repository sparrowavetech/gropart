<?php

namespace SparroWave\Shipmozo\Listeners;

use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\OrderConfirmedEvent;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Payment\Enums\PaymentStatusEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use SparroWave\Shipmozo\Shipmozo;
use Throwable;

class OrderConfirmedListener
{
    public function __construct(protected Shipmozo $shipmozo) {}

    public function handle(OrderConfirmedEvent $event): void
    {
        $order = $event->order;

        if (! setting('shipping_shipmozo_status') || ! $order->getKey()) {
            return;
        }

        if (! $this->shipmozo->isShipmozoOrder($order)) {
            return;
        }

        $lock = Cache::lock("shipmozo:order:{$order->getKey()}", 60);

        if (! $lock->get()) {
            return;
        }

        try {
            $shipment = Shipment::query()->firstOrCreate(
                ['order_id' => $order->getKey()],
                [
                    'user_id' => $event->confirmedBy?->getAuthIdentifier() ?: 0,
                    'weight' => $order->products_weight,
                    'cod_amount' => is_plugin_active('payment')
                        && $order->payment
                        && $order->payment->status != PaymentStatusEnum::COMPLETED
                        ? $order->amount
                        : 0,
                    'cod_status' => 'pending',
                    'status' => ShippingStatusEnum::PENDING,
                    'price' => $order->shipping_amount,
                    'store_id' => $order->store_id,
                ]
            );

            if ($shipment->wasRecentlyCreated) {
                ShipmentHistory::query()->create([
                    'action' => 'create_from_order',
                    'description' => trans('plugins/ecommerce::order.shipping_was_created_from'),
                    'shipment_id' => $shipment->getKey(),
                    'order_id' => $order->getKey(),
                    'user_id' => $event->confirmedBy?->getAuthIdentifier() ?: 0,
                ]);
            }

            $shipment->refresh();

            if (! $this->shipmozo->canCreateTransaction($shipment)) {
                return;
            }

            $transaction = $this->shipmozo->createShipment($order);
            $awb = Arr::get($transaction, 'data.awb_number');

            if (Arr::get($transaction, 'result') != 1 || ! $awb) {
                $shipment->metadata = $transaction;
                $shipment->save();

                ShipmentHistory::query()->create([
                    'action' => 'create_transaction_failed',
                    'description' => 'ShipMozo courier assignment failed: '.Arr::get($transaction, 'data.error', 'Unknown error'),
                    'order_id' => $order->getKey(),
                    'user_id' => $event->confirmedBy?->getAuthIdentifier() ?: 0,
                    'shipment_id' => $shipment->getKey(),
                ]);

                $this->shipmozo->logError('Automatic order push failed', [
                    'order_id' => $order->getKey(),
                    'message' => Arr::get($transaction, 'data.error', Arr::get($transaction, 'message')),
                ]);

                return;
            }

            $shipment->tracking_link = "https://panel.shipmozo.com/track-order/{$awb}";
            $shipment->label_url = $this->shipmozo->getOrderLabelUrl((string) $awb);
            $shipment->tracking_id = $awb;
            $shipment->metadata = $transaction;
            $shipment->status = ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT;
            $shipment->save();

            ShipmentHistory::query()->create([
                'action' => 'create_transaction',
                'description' => "Automatically pushed order to ShipMozo. Tracking ID: {$awb}",
                'order_id' => $order->getKey(),
                'user_id' => $event->confirmedBy?->getAuthIdentifier() ?: 0,
                'shipment_id' => $shipment->getKey(),
            ]);
        } catch (Throwable $exception) {
            $this->shipmozo->logError('Automatic order push failed', [
                'order_id' => $order->getKey(),
                'message' => $exception->getMessage(),
            ]);
        } finally {
            $lock->release();
        }
    }
}
