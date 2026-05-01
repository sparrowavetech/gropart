<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Base\Events\DeletedContentEvent;
use Botble\Ecommerce\Models\Review;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;

class ReversePointsForDeletedReview
{
    public function __construct(
        protected LoyaltyHelper $loyaltyHelper,
        protected LoyaltyPointService $loyaltyPointService
    ) {
    }

    public function handle(DeletedContentEvent $event): void
    {
        if (! $event->data instanceof Review) {
            return;
        }

        $review = $event->data;

        if (! $review->customer_id) {
            return;
        }

        if (! $this->loyaltyHelper->isEnabled()) {
            return;
        }

        $this->loyaltyPointService->reversePointsForReview($review->id, $review->customer_id);
    }
}
