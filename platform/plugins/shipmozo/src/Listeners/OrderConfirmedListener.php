<?php

namespace SparroWave\Shipmozo\Listeners;

use Botble\Ecommerce\Events\OrderConfirmedEvent;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Models\ShipmentHistory;
use SparroWave\Shipmozo\Shipmozo;
use Illuminate\Support\Arr;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderConfirmedListener
{
    public function __construct(protected Shipmozo $shipmozo) {}

    public function handle(OrderConfirmedEvent $event): void
    {
        $order = $event->order;

        // Only process if shipping method is ShipMozo
        if ($order->shipping_method !== 'shipmozo') {
            return;
        }

        try {
            // Check if shipment already exists, if not create one
            $shipment = $order->shipment;

            if (!$shipment) {
                $shipment = Shipment::query()->create([
                    'order_id' => $order->id,
                    'user_id' => $event->confirmedBy ? $event->confirmedBy->id : 0,
                    'weight' => $order->products->sum(fn($p) => $p->weight * $p->qty),
                    'shipment_id' => $order->id, // Fallback
                    'status' => ShippingStatusEnum::PENDING,
                    'price' => $order->shipping_amount,
                    'method' => 'shipmozo',
                ]);
            }

            // If already has a tracking ID, don't push again
            if ($shipment->tracking_id) {
                return;
            }

            $transaction = $this->shipmozo->pushOrder($order);

            if (Arr::get($transaction, 'result') == 1) {
                $awb = Arr::get($transaction, 'awb_number') ?: Arr::get($transaction, 'data.order_id');

                if ($awb) {
                    $labelUrl = $this->shipmozo->getOrderLabel($awb);

                    $shipment->tracking_link = 'https://panel.shipmozo.com/track-order/' . $awb;
                    $shipment->label_url = $labelUrl;
                    $shipment->tracking_id = $awb;
                    $shipment->metadata = json_encode($transaction);
                    $shipment->status = ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT;
                    $shipment->save();

                    ShipmentHistory::query()->create([
                        'action' => 'create_transaction',
                        'description' => 'Automatically pushed order to ShipMozo. Tracking ID: ' . $awb,
                        'order_id' => $order->id,
                        'user_id' => $event->confirmedBy ? $event->confirmedBy->id : 0,
                        'shipment_id' => $shipment->id,
                    ]);
                }
            } else {
                $error = Arr::get($transaction, 'data.error', Arr::get($transaction, 'message', 'Failed to auto-push to ShipMozo.'));
                Log::error('ShipMozo Auto-Push Failed for Order #' . $order->id . ': ' . $error);
            }
        } catch (Exception $e) {
            Log::error('ShipMozo OrderConfirmedListener Error: ' . $e->getMessage());
        }
    }
}
