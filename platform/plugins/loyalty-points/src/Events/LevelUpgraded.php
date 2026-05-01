<?php

namespace Botble\LoyaltyPoints\Events;

use Botble\Base\Events\Event;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Illuminate\Queue\SerializesModels;

class LevelUpgraded extends Event
{
    use SerializesModels;

    public function __construct(
        public CustomerPointBalance $balance,
        public LoyaltyLevel $newLevel,
        public ?LoyaltyLevel $oldLevel = null
    ) {
    }
}
