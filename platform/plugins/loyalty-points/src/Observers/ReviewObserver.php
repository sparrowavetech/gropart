<?php

namespace Botble\LoyaltyPoints\Observers;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Review;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;

class ReviewObserver
{
    public function __construct(
        protected LoyaltyPointService $loyaltyPointService,
        protected LoyaltyHelper $loyaltyHelper
    ) {
    }

    public function created(Review $review): void
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            return;
        }

        if (! $review->customer_id) {
            return;
        }

        if ($review->status != BaseStatusEnum::PUBLISHED) {
            return;
        }

        $this->awardPointsForReview($review);
    }

    public function updated(Review $review): void
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            return;
        }

        if (! $review->customer_id) {
            return;
        }

        $wasPublished = $review->getOriginal('status') == BaseStatusEnum::PUBLISHED;
        $isPublished = $review->status == BaseStatusEnum::PUBLISHED;

        // Review was just published - award points
        if (! $wasPublished && $isPublished) {
            $this->awardPointsForReview($review);

            return;
        }

        // Review was unpublished - reverse points
        if ($wasPublished && ! $isPublished) {
            $this->reversePointsForReview($review);
        }
    }

    public function deleted(Review $review): void
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            return;
        }

        if (! $review->customer_id) {
            return;
        }

        $this->reversePointsForReview($review);
    }

    protected function awardPointsForReview(Review $review): void
    {
        // Check if already awarded (prevent duplicates from both observer and event listener)
        // Pattern handles both "review #1" and "review with photos #1"
        $existingTransaction = PointTransaction::query()
            ->where('customer_id', $review->customer_id)
            ->where('note', 'LIKE', '%review%#' . $review->id . '%')
            ->where('type', PointTransaction::TYPE_EARN)
            ->exists();

        if ($existingTransaction) {
            return;
        }

        $hasImages = ! empty($review->images) && is_array($review->images) && count($review->images) > 0;

        $points = $hasImages
            ? $this->loyaltyHelper->getPointsForPhotoReview()
            : $this->loyaltyHelper->getPointsForReview();

        if ($points <= 0) {
            return;
        }

        $note = $hasImages
            ? trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_photo_review', ['id' => $review->id])
            : trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_review', ['id' => $review->id]);

        $this->loyaltyPointService->awardBonusPoints($review->customer_id, $points, $note);
    }

    protected function reversePointsForReview(Review $review): void
    {
        // Check if there's an earned transaction to reverse
        // Pattern handles both "review #1" and "review with photos #1"
        $existingTransaction = PointTransaction::query()
            ->where('customer_id', $review->customer_id)
            ->where('note', 'LIKE', '%review%#' . $review->id . '%')
            ->where('type', PointTransaction::TYPE_EARN)
            ->exists();

        if (! $existingTransaction) {
            return;
        }

        // Check if already reversed
        $alreadyReversed = PointTransaction::query()
            ->where('customer_id', $review->customer_id)
            ->where('note', 'LIKE', '%review%#' . $review->id . '%')
            ->where('type', PointTransaction::TYPE_REVERSE)
            ->exists();

        if ($alreadyReversed) {
            return;
        }

        $this->loyaltyPointService->reversePointsForReview($review->id, $review->customer_id);
    }
}
