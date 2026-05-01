<?php

namespace Botble\LoyaltyPoints\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\Cart;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Http\Request;

class LoyaltyCheckoutController extends BaseController
{
    public function applyPoints(Request $request, LoyaltyHelper $loyaltyHelper, LoyaltyPointService $loyaltyService): BaseHttpResponse
    {
        if (! $loyaltyHelper->isEnabled()) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.loyalty_disabled'));
        }

        $customer = auth('customer')->user();

        if (! $customer) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.customer_required'));
        }

        $points = (int) $request->input('points');

        if ($points <= 0) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.points_must_be_positive'));
        }

        $currentPoints = $loyaltyService->getCustomerBalance($customer->id);

        if ($points > $currentPoints) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.insufficient_points'));
        }

        $minRedeemable = $loyaltyHelper->getMinRedeemablePoints();
        if ($minRedeemable > 0 && $points < $minRedeemable) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.points_below_minimum', ['min' => number_format($minRedeemable)]));
        }

        $maxRedeemable = $loyaltyHelper->getMaxRedeemablePoints();
        if ($maxRedeemable > 0 && $points > $maxRedeemable) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.points_above_maximum', ['max' => number_format($maxRedeemable)]));
        }

        $discountAmount = $loyaltyHelper->calculateDiscountFromPoints($points);
        $cartTotal = (float) Cart::instance('cart')->rawSubTotal();

        if ($discountAmount > $cartTotal) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.discount_exceeds_total'));
        }

        $maxRedemptionPercentage = $loyaltyHelper->getMaxRedemptionPercentage();
        if ($maxRedemptionPercentage > 0) {
            $maxAllowedDiscount = ($cartTotal * $maxRedemptionPercentage) / 100;
            if ($discountAmount > $maxAllowedDiscount) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.exceeds_max_redemption_percentage', [
                        'percentage' => $maxRedemptionPercentage,
                    ]));
            }
        }

        session()->put('applied_loyalty_points', $points);
        session()->put('loyalty_points_discount', $discountAmount);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/loyalty-points::loyalty-points.customer.discount_applied', [
                'amount' => format_price($discountAmount),
                'points' => number_format($points),
            ]))
            ->setData([
                'points' => $points,
                'discount' => $discountAmount,
                'discount_formatted' => format_price($discountAmount),
            ]);
    }

    public function removePoints(): BaseHttpResponse
    {
        session()->forget('applied_loyalty_points');
        session()->forget('loyalty_points_discount');

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/loyalty-points::loyalty-points.customer.points_removed'));
    }
}
