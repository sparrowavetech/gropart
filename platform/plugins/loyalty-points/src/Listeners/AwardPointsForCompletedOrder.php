<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\LoyaltyPoints\Models\OrderLoyaltyPoints;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;

class AwardPointsForCompletedOrder
{
    public function __construct(protected LoyaltyPointService $loyaltyPointService)
    {
    }

    public function handle(OrderCompletedEvent $event): void
    {
        $order = $event->order;

        // If order has user_id, use the standard flow
        if ($order->user_id) {
            $this->loyaltyPointService->awardPointsForOrder($order);

            return;
        }

        // For guest orders, check if there's a customer_id from member ID
        $orderLoyaltyPoints = OrderLoyaltyPoints::query()
            ->where('order_id', $order->id)
            ->first();

        if (! $orderLoyaltyPoints || ! $orderLoyaltyPoints->customer_id) {
            return;
        }

        // Award points to the customer identified by member ID
        $this->loyaltyPointService->awardPointsForOrder($order, $orderLoyaltyPoints->customer_id);
    }
}
