<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Ecommerce\Events\OrderCreated;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\OrderLoyaltyPoints;
use Botble\LoyaltyPoints\Services\LoyaltyMemberService;

class ProcessAdminOrderLoyaltyPoints
{
    public function __construct(
        protected LoyaltyHelper $loyaltyHelper,
        protected LoyaltyMemberService $loyaltyMemberService
    ) {
    }

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if (! $this->loyaltyHelper->isEnabled()) {
            return;
        }

        // Skip if order already has a customer (will be handled by regular flow)
        if ($order->user_id && $order->user_id > 0) {
            return;
        }

        // Check if member ID was provided in the request
        $memberId = request()->input('loyalty_member_id');
        $memberCustomerId = request()->input('loyalty_member_customer_id');

        // Use validated customer ID if available, otherwise try to parse member ID
        $customerId = null;

        if ($memberCustomerId && $memberCustomerId > 0) {
            $customerId = (int) $memberCustomerId;
        } elseif ($memberId) {
            $customer = $this->loyaltyMemberService->parseAndValidate($memberId);
            $customerId = $customer?->id;
        }

        if (! $customerId) {
            return;
        }

        // Check if order loyalty points record already exists
        $existingRecord = OrderLoyaltyPoints::query()->where('order_id', $order->id)->first();

        if ($existingRecord) {
            // Update existing record with customer_id
            $existingRecord->update([
                'customer_id' => $customerId,
            ]);

            return;
        }

        // Calculate points to earn
        $pointsToEarn = $this->loyaltyHelper->calculatePointsFromAmount($order->amount);

        // Create order loyalty points record
        OrderLoyaltyPoints::query()->create([
            'order_id' => $order->id,
            'customer_id' => $customerId,
            'points_redeemed' => 0,
            'discount_amount' => 0,
            'points_to_earn' => $pointsToEarn,
        ]);
    }
}
