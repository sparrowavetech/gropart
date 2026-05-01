<?php

namespace Botble\LoyaltyPoints\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\Cart;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Services\LoyaltyMemberService;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Http\Request;

class LoyaltyCheckoutController extends BaseController
{
    public function __construct(
        protected LoyaltyHelper $loyaltyHelper,
        protected LoyaltyPointService $loyaltyService,
        protected LoyaltyMemberService $loyaltyMemberService
    ) {
    }

    public function applyPoints(Request $request): BaseHttpResponse
    {
        if (! $this->loyaltyHelper->isEnabled()) {
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

        $currentPoints = $this->loyaltyService->getCustomerBalance($customer->id);

        if ($points > $currentPoints) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.insufficient_points'));
        }

        $minRedeemable = $this->loyaltyHelper->getMinRedeemablePoints();
        if ($minRedeemable > 0 && $points < $minRedeemable) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.points_below_minimum', ['min' => number_format($minRedeemable)]));
        }

        $maxRedeemable = $this->loyaltyHelper->getMaxRedeemablePoints();
        if ($maxRedeemable > 0 && $points > $maxRedeemable) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.points_above_maximum', ['max' => number_format($maxRedeemable)]));
        }

        $discountAmount = $this->loyaltyHelper->calculateDiscountFromPoints($points);
        $cartTotal = (float) Cart::instance('cart')->rawSubTotal();

        if ($discountAmount > $cartTotal) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.discount_exceeds_total'));
        }

        $maxRedemptionPercentage = $this->loyaltyHelper->getMaxRedemptionPercentage();
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

    public function validateMemberId(Request $request): BaseHttpResponse
    {
        if (! $this->loyaltyHelper->isEnabled()) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.errors.loyalty_disabled'));
        }

        $memberId = $request->input('member_id');

        if (empty($memberId)) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.member_id.empty'));
        }

        if (! $this->loyaltyMemberService->isValidFormat($memberId)) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.member_id.invalid_format'));
        }

        $customer = $this->loyaltyMemberService->parseAndValidate($memberId);

        if (! $customer) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/loyalty-points::loyalty-points.member_id.not_found'));
        }

        // Store validated member info in session
        session()->put('loyalty_guest_member_id', $memberId);
        session()->put('loyalty_guest_customer_id', $customer->id);
        session()->put('loyalty_guest_customer', [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
        ]);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/loyalty-points::loyalty-points.member_id.valid', [
                'name' => $customer->name,
            ]))
            ->setData([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
            ]);
    }

    public function removeMemberId(): BaseHttpResponse
    {
        session()->forget([
            'loyalty_guest_member_id',
            'loyalty_guest_customer_id',
            'loyalty_guest_customer',
        ]);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/loyalty-points::loyalty-points.member_id.removed'));
    }
}
