<?php

namespace Botble\LoyaltyPoints\Helpers;

use Botble\Theme\Facades\Theme;

class LoyaltyHelper
{
    public function isEnabled(): bool
    {
        return (bool) get_loyalty_setting('enable_loyalty_program', true);
    }

    public function getEarningRate(): float
    {
        return (float) get_loyalty_setting('points_earning_rate', 1);
    }

    public function getEarningCurrency(): float
    {
        return (float) get_loyalty_setting('points_earning_currency', 100);
    }

    public function getRedemptionRate(): float
    {
        return (float) get_loyalty_setting('points_redemption_rate', 100);
    }

    public function getRedemptionCurrency(): float
    {
        return (float) get_loyalty_setting('points_redemption_currency', 100);
    }

    public function getMinRedeemablePoints(): int
    {
        return (int) get_loyalty_setting('min_redeemable_points', 0);
    }

    public function getMaxRedeemablePoints(): int
    {
        return (int) get_loyalty_setting('max_redeemable_points', 0);
    }

    public function getEligibleOrderStatuses(): array
    {
        $statuses = get_loyalty_setting('eligible_order_statuses', ['completed']);

        return is_array($statuses) ? $statuses : (json_decode($statuses, true) ?: ['completed']);
    }

    public function getPointsForRegistration(): int
    {
        return (int) get_loyalty_setting('points_for_registration', 100);
    }

    public function getPointsForReview(): int
    {
        return (int) get_loyalty_setting('points_for_review', 50);
    }

    public function getPointsForPhotoReview(): int
    {
        return (int) get_loyalty_setting('points_for_photo_review', 100);
    }

    public function getPointsForReferral(): int
    {
        return (int) get_loyalty_setting('points_for_referral', 300);
    }

    public function getPointsForBirthday(): int
    {
        return (int) get_loyalty_setting('points_for_birthday', 200);
    }

    public function getPointsExpiryMonths(): int
    {
        return (int) get_loyalty_setting('points_expiry_months', 12);
    }

    public function getMaxRedemptionPercentage(): int
    {
        return (int) get_loyalty_setting('max_redemption_percentage', 20);
    }

    public function isLoyaltyCardEnabled(): bool
    {
        return (bool) get_loyalty_setting('enable_loyalty_card', true);
    }

    public function isGuestCheckoutMemberIdEnabled(): bool
    {
        return (bool) get_loyalty_setting('enable_guest_checkout_member_id', true);
    }

    public function isProductInfoEnabled(): bool
    {
        return (bool) get_loyalty_setting('enable_product_info', true);
    }

    /**
     * @deprecated since 1.0.8. The exchange rate is no longer used in the
     * redemption formula; configure the ratio via "Points Required" and
     * "Discount Value" only. Kept for backward compatibility.
     */
    public function getPointsExchangeRate(): int
    {
        return (int) get_loyalty_setting('points_exchange_rate', 1);
    }

    public function calculatePointsFromAmount(float $amount): int
    {
        $currency = $this->getEarningCurrency();
        $rate = $this->getEarningRate();

        if ($currency <= 0) {
            return 0;
        }

        return (int) floor(($amount / $currency) * $rate);
    }

    public function calculatePointsForOrder($order): int
    {
        // If order has no products (e.g. loaded without relation), fall back to total amount
        if (! $order->relationLoaded('products')) {
            return $this->calculatePointsFromAmount($order->amount);
        }

        $totalPoints = 0;
        $globalCurrency = $this->getEarningCurrency();
        $globalRate = $this->getEarningRate();

        if ($globalCurrency <= 0) {
            return 0;
        }

        foreach ($order->products as $orderProduct) {
            // Check if product has specific points setting (meta)
            // Assuming we can access original product via relation if needed,
            // but order_product table usually stores snapshot.
            // We'll check the original product for meta.
            $product = $orderProduct->product;

            if ($product && $product->getMetaData('loyalty_points_flat', true)) {
                $flatPoints = (int) $product->getMetaData('loyalty_points_flat', true);
                $totalPoints += $flatPoints * $orderProduct->qty;

                continue;
            }

            // Calculate based on price
            $amount = $orderProduct->price * $orderProduct->qty;
            $points = (int) floor(($amount / $globalCurrency) * $globalRate);
            $totalPoints += $points;
        }

        return $totalPoints;
    }

    public function calculateDiscountFromPoints(int $points): float
    {
        $rate = $this->getRedemptionRate();
        $currency = $this->getRedemptionCurrency();

        if ($rate <= 0) {
            return 0;
        }

        return ($points / $rate) * $currency;
    }

    public function calculateMaxPointsFromAmount(float $amount): int
    {
        $currency = $this->getRedemptionCurrency();
        $rate = $this->getRedemptionRate();

        if ($currency <= 0) {
            return 0;
        }

        return (int) floor(($amount / $currency) * $rate);
    }

    public function validateRedeemablePoints(int $points, int $customerBalance, float $orderTotal): array
    {
        $errors = [];

        if ($points <= 0) {
            $errors[] = trans('plugins/loyalty-points::loyalty-points.errors.points_must_be_positive');
        }

        $min = $this->getMinRedeemablePoints();
        if ($min > 0 && $points < $min) {
            $errors[] = trans('plugins/loyalty-points::loyalty-points.errors.points_below_minimum', ['min' => $min]);
        }

        $max = $this->getMaxRedeemablePoints();
        if ($max > 0 && $points > $max) {
            $errors[] = trans('plugins/loyalty-points::loyalty-points.errors.points_above_maximum', ['max' => $max]);
        }

        if ($points > $customerBalance) {
            $errors[] = trans('plugins/loyalty-points::loyalty-points.errors.insufficient_points');
        }

        $discount = $this->calculateDiscountFromPoints($points);
        if ($discount > $orderTotal) {
            $errors[] = trans('plugins/loyalty-points::loyalty-points.errors.discount_exceeds_total');
        }

        $maxRedemptionPercentage = $this->getMaxRedemptionPercentage();
        if ($maxRedemptionPercentage > 0) {
            $maxAllowedDiscount = ($orderTotal * $maxRedemptionPercentage) / 100;
            if ($discount > $maxAllowedDiscount) {
                $errors[] = trans('plugins/loyalty-points::loyalty-points.errors.exceeds_max_redemption_percentage', [
                    'percentage' => $maxRedemptionPercentage,
                ]);
            }
        }

        return $errors;
    }

    public function viewPath(string $view): string
    {
        $themeView = Theme::getThemeNamespace() . '::views.loyalty-points.' . $view;

        if (view()->exists($themeView)) {
            return $themeView;
        }

        return 'plugins/loyalty-points::themes.' . $view;
    }
}
