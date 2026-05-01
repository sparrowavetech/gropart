<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Support\Str;

class AwardPointsForRegistration
{
    public function __construct(
        protected LoyaltyHelper $loyaltyHelper,
        protected LoyaltyPointService $loyaltyPointService
    ) {
    }

    public function handle(CreatedContentEvent $event): void
    {
        if (! $event->data instanceof Customer) {
            return;
        }

        $customer = $event->data;

        if (! $this->loyaltyHelper->isEnabled()) {
            return;
        }

        $existingTransaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->where('note', 'LIKE', '%registration%')
            ->exists();

        if ($existingTransaction) {
            return;
        }

        $points = $this->loyaltyHelper->getPointsForRegistration();

        if ($points <= 0) {
            return;
        }

        $referralCode = $this->generateUniqueReferralCode();
        $customer->update(['referral_code' => $referralCode]);

        $this->loyaltyPointService->awardBonusPoints(
            $customer->id,
            $points,
            trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_registration')
        );
    }

    protected function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
            $exists = Customer::query()->where('referral_code', $code)->exists();
        } while ($exists);

        return $code;
    }
}
