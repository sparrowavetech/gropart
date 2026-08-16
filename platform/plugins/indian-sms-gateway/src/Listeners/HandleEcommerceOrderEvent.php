<?php

namespace Ashikul\IndiaSmsGateway\Listeners;

use Ashikul\IndiaSmsGateway\Services\EcommerceNotificationService;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class HandleEcommerceOrderEvent
{
    public function __construct(private EcommerceNotificationService $notifications)
    {
    }

    public function handle(object $event): void
    {
        $eventName = class_basename($event);

        try {
            if ($eventName === 'ShippingStatusChanged') {
                $shipment = data_get($event, 'shipment');
                $order = data_get($shipment, 'order');

                if ($order instanceof Model) {
                    $this->notifications->shippingStatusChanged($order, $shipment);
                }

                return;
            }

            $order = data_get($event, 'order');

            if (! $order instanceof Model) {
                return;
            }

            match ($eventName) {
                'OrderCreated', 'OrderPlacedEvent' => $this->notifications->orderCreated($order),
                'OrderConfirmedEvent' => $this->notifications->orderLifecycleChanged($order, 'confirmed'),
                'OrderCompletedEvent' => $this->notifications->orderLifecycleChanged($order, 'completed'),
                'OrderCancelledEvent' => $this->notifications->orderLifecycleChanged($order, 'canceled'),
                'OrderReturnedEvent' => $this->notifications->orderLifecycleChanged($order, 'returned'),
                'OrderPaymentConfirmedEvent' => $this->notifications->paymentConfirmed($order),
                default => null,
            };
        } catch (Throwable) {
            // SMS notifications must never interrupt an order operation.
        }
    }
}
