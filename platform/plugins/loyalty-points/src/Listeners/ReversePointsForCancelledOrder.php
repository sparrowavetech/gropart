<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;

class ReversePointsForCancelledOrder
{
    public function __construct(protected LoyaltyPointService $loyaltyPointService)
    {
    }

    public function handle(OrderCancelledEvent $event): void
    {
        $order = $event->order;

        if (! $order->user_id) {
            return;
        }

        $this->loyaltyPointService->reversePointsForOrder($order);
    }
}
