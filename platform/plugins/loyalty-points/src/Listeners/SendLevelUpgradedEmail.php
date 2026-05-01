<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Events\LevelUpgraded;
use Botble\LoyaltyPoints\Services\LoyaltyEmailService;

class SendLevelUpgradedEmail
{
    public function __construct(protected LoyaltyEmailService $emailService)
    {
    }

    public function handle(LevelUpgraded $event): void
    {
        $balance = $event->balance;
        $customer = Customer::find($balance->customer_id);

        if (! $customer || ! $customer->email) {
            return;
        }

        $this->emailService->sendLevelUpgradedEmail(
            $customer,
            $event->newLevel,
            $event->oldLevel,
            $balance
        );
    }
}
