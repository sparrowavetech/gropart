<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Events\PointsRedeemed;
use Botble\LoyaltyPoints\Services\LoyaltyEmailService;

class SendPointsRedeemedEmail
{
    public function __construct(protected LoyaltyEmailService $emailService)
    {
    }

    public function handle(PointsRedeemed $event): void
    {
        if (! $event->order) {
            return;
        }

        $customer = Customer::find($event->customerId);

        if (! $customer) {
            return;
        }

        $discountFormatted = format_price($event->discountAmount);

        if ($customer->email) {
            $this->emailService->sendPointsRedeemedEmail(
                $customer,
                $event->points,
                $discountFormatted,
                $event->order,
                $event->remainingBalance
            );
        }

        $this->emailService->sendAdminPointsRedeemedEmail(
            $customer,
            $event->points,
            $discountFormatted,
            $event->order,
            $event->remainingBalance
        );
    }
}
