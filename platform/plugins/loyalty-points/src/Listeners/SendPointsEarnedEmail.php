<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Events\PointsEarned;
use Botble\LoyaltyPoints\Services\LoyaltyEmailService;

class SendPointsEarnedEmail
{
    public function __construct(protected LoyaltyEmailService $emailService)
    {
    }

    public function handle(PointsEarned $event): void
    {
        if (! $event->order) {
            return;
        }

        $customer = Customer::find($event->customerId);

        if (! $customer) {
            return;
        }

        if ($customer->email) {
            $this->emailService->sendPointsEarnedEmail(
                $customer,
                $event->points,
                $event->order,
                $event->balance
            );
        }

        $this->emailService->sendAdminPointsEarnedEmail(
            $customer,
            $event->points,
            $event->order,
            $event->balance
        );
    }
}
