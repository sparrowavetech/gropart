<?php

namespace Botble\LoyaltyPoints\Events;

use Botble\Base\Events\Event;
use Botble\Ecommerce\Models\Order;
use Illuminate\Queue\SerializesModels;

class PointsRedeemed extends Event
{
    use SerializesModels;

    public function __construct(
        public int|string $customerId,
        public int $points,
        public float $discountAmount,
        public int $remainingBalance,
        public ?Order $order = null
    ) {
    }
}
