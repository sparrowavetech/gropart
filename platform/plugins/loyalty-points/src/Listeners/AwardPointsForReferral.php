<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;

class AwardPointsForReferral
{
    public function __construct(
        protected LoyaltyHelper $loyaltyHelper,
        protected LoyaltyPointService $loyaltyPointService
    ) {
    }

    public function handle(OrderCompletedEvent $event): void
    {
        $order = $event->order;

        if (! $order->user_id) {
            return;
        }

        if (! $this->loyaltyHelper->isEnabled()) {
            return;
        }

        $customer = Customer::query()->find($order->user_id);

        if (! $customer) {
            return;
        }

        if (! $customer->referred_by || $customer->referral_reward_given) {
            return;
        }

        $referrer = Customer::query()->find($customer->referred_by);

        if (! $referrer) {
            return;
        }

        $points = $this->loyaltyHelper->getPointsForReferral();

        if ($points <= 0) {
            return;
        }

        $this->loyaltyPointService->awardBonusPoints(
            $referrer->id,
            $points,
            trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_referral', [
                'name' => $customer->name,
            ])
        );

        $customer->update(['referral_reward_given' => true]);
    }
}
