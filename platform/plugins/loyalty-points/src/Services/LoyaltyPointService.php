<?php

namespace Botble\LoyaltyPoints\Services;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\Enum;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Events\LevelUpgraded;
use Botble\LoyaltyPoints\Events\PointsEarned;
use Botble\LoyaltyPoints\Events\PointsRedeemed;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Illuminate\Support\Facades\DB;

class LoyaltyPointService
{
    public function __construct(
        protected LoyaltyHelper $loyaltyHelper
    ) {
    }

    public function awardPointsForOrder(Order $order, int|string|null $customerId = null): bool
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            return false;
        }

        // Use provided customer ID or order's user_id
        $customerId = $customerId ?: $order->user_id;

        // No customer to award points to
        if (! $customerId) {
            return false;
        }

        $statusValue = $order->status instanceof Enum
            ? $order->status->getValue()
            : (string) $order->status;

        if (! in_array($statusValue, $this->loyaltyHelper->getEligibleOrderStatuses())) {
            return false;
        }

        $existing = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->exists();

        if ($existing) {
            return false;
        }

        $points = $this->loyaltyHelper->calculatePointsForOrder($order);

        // Apply Level Multiplier
        $balance = CustomerPointBalance::query()->where('customer_id', $customerId)->first();
        if ($balance && $balance->level_id) {
            $level = LoyaltyLevel::find($balance->level_id);
            if ($level && $level->earning_rate > 1) {
                $points = (int) round($points * $level->earning_rate);
            }
        }

        if ($points <= 0) {
            return false;
        }

        $result = $this->addPoints(
            $customerId,
            $points,
            PointTransaction::TYPE_EARN,
            $order->id,
            trans('plugins/loyalty-points::loyalty-points.transaction.earned_from_order', ['code' => $order->code])
        );

        if ($result) {
            $balance = CustomerPointBalance::query()->where('customer_id', $customerId)->first();
            if ($balance) {
                event(new PointsEarned($customerId, $points, $balance, $order));
            }
        }

        return $result;
    }

    public function recalculatePointsForOrder(Order $order): bool
    {
        // Reverse existing points if any
        $this->reversePointsForOrder($order);

        // Award new points
        return $this->awardPointsForOrder($order);
    }

    public function reversePointsForOrder(Order $order, int|string|null $customerId = null): bool
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            return false;
        }

        $earnedTransaction = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        if (! $earnedTransaction) {
            return false;
        }

        // Use the customer_id from the earned transaction (most accurate)
        // This handles both registered users and guest orders with loyalty member IDs
        $customerId = $customerId ?: $earnedTransaction->customer_id ?: $order->user_id;

        if (! $customerId) {
            return false;
        }

        $result = $this->deductPoints(
            $customerId,
            $earnedTransaction->points,
            PointTransaction::TYPE_REVERSE,
            $order->id,
            trans('plugins/loyalty-points::loyalty-points.transaction.reversed_from_order', ['code' => $order->code])
        );

        // Delete the original EARN transaction so points can be re-awarded if needed
        if ($result) {
            $earnedTransaction->delete();
        }

        return $result;
    }

    public function redeemPoints(int|string $customerId, int $points, int|string|null $orderId = null, ?Order $order = null): bool
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            return false;
        }

        /**
         * @var CustomerPointBalance $balance
         */
        $balance = CustomerPointBalance::query()->firstOrCreate(
            ['customer_id' => $customerId],
            ['total_points' => 0, 'lifetime_points' => 0]
        );

        if (! $balance->hasEnoughPoints($points)) {
            return false;
        }

        $result = $this->deductPoints(
            $customerId,
            $points,
            PointTransaction::TYPE_REDEEM,
            $orderId,
            trans('plugins/loyalty-points::loyalty-points.transaction.redeemed_for_discount')
        );

        if ($result && $order) {
            $discountAmount = $this->calculateDiscount($points);
            $remainingBalance = $this->getCustomerBalance($customerId);
            event(new PointsRedeemed($customerId, $points, $discountAmount, $remainingBalance, $order));
        }

        return $result;
    }

    public function adjustPoints(int|string $customerId, int $points, ?string $note = null): bool
    {
        if ($points > 0) {
            return $this->addPoints($customerId, $points, PointTransaction::TYPE_ADJUST, null, $note);
        }

        return $this->deductPoints($customerId, abs($points), PointTransaction::TYPE_ADJUST, null, $note);
    }

    protected function addPoints(int|string $customerId, int $points, string $type, int|string|null $orderId = null, ?string $note = null): bool
    {
        return DB::transaction(function () use ($customerId, $points, $type, $orderId, $note) {
            /**
             * @var CustomerPointBalance $balance
             */
            $balance = CustomerPointBalance::query()->firstOrCreate(
                ['customer_id' => $customerId],
                ['total_points' => 0, 'lifetime_points' => 0]
            );

            $balance->addPoints($points);

            if ($type === PointTransaction::TYPE_EARN) {
                $balance->increment('lifetime_points', $points);
            }

            $balance->save();

            $this->checkAndUpdateLevel($balance);

            $expiryMonths = $this->loyaltyHelper->getPointsExpiryMonths();
            $expiresAt = $expiryMonths > 0 ? now()->addMonths($expiryMonths) : null;

            PointTransaction::query()->create([
                'customer_id' => $customerId,
                'order_id' => $orderId,
                'type' => $type,
                'points' => $points,
                'note' => $note,
                'created_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            return true;
        });
    }

    protected function deductPoints(int|string $customerId, int $points, string $type, int|string|null $orderId = null, ?string $note = null): bool
    {
        return DB::transaction(function () use ($customerId, $points, $type, $orderId, $note) {
            /**
             * @var CustomerPointBalance $balance
             */
            $balance = CustomerPointBalance::query()->firstOrCreate(
                ['customer_id' => $customerId],
                ['total_points' => 0, 'lifetime_points' => 0]
            );

            $balance->deductPoints($points);
            $balance->save();

            PointTransaction::query()->create([
                'customer_id' => $customerId,
                'order_id' => $orderId,
                'type' => $type,
                'points' => -$points,
                'note' => $note,
                'created_at' => now(),
            ]);

            return true;
        });
    }

    public function getCustomerBalance(int|string $customerId): int
    {
        $balance = CustomerPointBalance::query()
            ->where('customer_id', $customerId)
            ->first();

        return $balance ? $balance->total_points : 0;
    }

    public function calculateDiscount(int $points): float
    {
        return $this->loyaltyHelper->calculateDiscountFromPoints($points);
    }

    public function validateRedemption(int|string $customerId, int $points, float $orderTotal): array
    {
        $balance = $this->getCustomerBalance($customerId);

        return $this->loyaltyHelper->validateRedeemablePoints($points, $balance, $orderTotal);
    }

    public function expireOldPoints(): int
    {
        $expiryMonths = $this->loyaltyHelper->getPointsExpiryMonths();

        if ($expiryMonths <= 0) {
            return 0;
        }

        $expiredTransactions = PointTransaction::query()
            ->where('type', PointTransaction::TYPE_EARN)
            ->where('expires_at', '<=', now())
            ->whereNotNull('expires_at')
            ->get();

        $expiredCount = 0;

        foreach ($expiredTransactions as $transaction) {
            $balance = CustomerPointBalance::query()
                ->where('customer_id', $transaction->customer_id)
                ->first();

            if ($balance && $balance->total_points >= $transaction->points) {
                $this->deductPoints(
                    $transaction->customer_id,
                    $transaction->points,
                    PointTransaction::TYPE_REVERSE,
                    null,
                    trans('plugins/loyalty-points::loyalty-points.transaction.points_expired')
                );

                $transaction->delete();
                $expiredCount++;
            }
        }

        return $expiredCount;
    }

    public function awardBonusPoints(int|string $customerId, int $points, string $reason): bool
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            return false;
        }

        if ($points <= 0) {
            return false;
        }

        return $this->addPoints(
            $customerId,
            $points,
            PointTransaction::TYPE_EARN,
            null,
            $reason
        );
    }

    public function reversePointsForReview(int|string $reviewId, int|string $customerId): bool
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            return false;
        }

        // Pattern handles both "review #1" and "review with photos #1"
        $earnedTransaction = PointTransaction::query()
            ->where('customer_id', $customerId)
            ->where('note', 'LIKE', '%review%#' . $reviewId . '%')
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        if (! $earnedTransaction) {
            return false;
        }

        $result = $this->deductPoints(
            $customerId,
            $earnedTransaction->points,
            PointTransaction::TYPE_REVERSE,
            null,
            trans('plugins/loyalty-points::loyalty-points.transaction.reversed_from_review', ['id' => $reviewId])
        );

        if ($result) {
            $earnedTransaction->delete();
        }

        return $result;
    }

    protected function checkAndUpdateLevel(CustomerPointBalance $balance): void
    {
        $levels = LoyaltyLevel::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->orderByDesc('min_points')
            ->get();

        foreach ($levels as $level) {
            if ($balance->lifetime_points >= $level->min_points) {
                if ($balance->level_id !== $level->id) {
                    $oldLevelId = $balance->level_id;
                    $balance->level_id = $level->id;
                    $balance->level_updated_at = now();
                    $balance->save();

                    event(new LevelUpgraded($balance, $level, $oldLevelId ? LoyaltyLevel::find($oldLevelId) : null));
                }

                break;
            }
        }
    }
}
