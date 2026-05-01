<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Ecommerce\Models\Review;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;

class AwardPointsForReview
{
    public function __construct(
        protected LoyaltyHelper $loyaltyHelper,
        protected LoyaltyPointService $loyaltyPointService
    ) {
    }

    public function handle(CreatedContentEvent|UpdatedContentEvent $event): void
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

        $isPublished = $review->status->getValue() === BaseStatusEnum::PUBLISHED;

        // For UpdatedContentEvent, check if status changed FROM published to something else
        if ($event instanceof UpdatedContentEvent && ! $isPublished) {
            // Check if points were previously awarded for this review
            // Pattern handles both "review #1" and "review with photos #1"
            $existingTransaction = PointTransaction::query()
                ->where('customer_id', $review->customer_id)
                ->where('note', 'LIKE', '%review%#' . $review->id . '%')
                ->where('type', PointTransaction::TYPE_EARN)
                ->exists();

            // If points were awarded and review is no longer published, reverse them
            if ($existingTransaction) {
                $this->loyaltyPointService->reversePointsForReview($review->id, $review->customer_id);
            }

            return;
        }

        // If not published, don't award points
        if (! $isPublished) {
            return;
        }

        // Check if points were already awarded
        // Pattern handles both "review #1" and "review with photos #1"
        $existingTransaction = PointTransaction::query()
            ->where('customer_id', $review->customer_id)
            ->where('note', 'LIKE', '%review%#' . $review->id . '%')
            ->where('type', PointTransaction::TYPE_EARN)
            ->exists();

        if ($existingTransaction) {
            return;
        }

        $hasPhotos = $review->images && is_array($review->images) && count($review->images) > 0;
        $points = $hasPhotos
            ? $this->loyaltyHelper->getPointsForPhotoReview()
            : $this->loyaltyHelper->getPointsForReview();

        if ($points <= 0) {
            return;
        }

        $reason = $hasPhotos
            ? trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_photo_review', ['id' => $review->id])
            : trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_review', ['id' => $review->id]);

        $this->loyaltyPointService->awardBonusPoints(
            $review->customer_id,
            $points,
            $reason
        );
    }
}
