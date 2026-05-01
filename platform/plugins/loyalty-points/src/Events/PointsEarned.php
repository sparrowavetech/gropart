<?php

namespace Botble\LoyaltyPoints\Events;

use Botble\Base\Events\Event;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Illuminate\Queue\SerializesModels;

class PointsEarned extends Event
{
    use SerializesModels;

    public function __construct(
        public int|string $customerId,
        public int $points,
        public CustomerPointBalance $balance,
        public ?Order $order = null
    ) {
    }
}
