<?php

namespace Botble\LoyaltyPoints\Listeners;

use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Models\Invoice;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\OrderLoyaltyPoints;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;

class SaveOrderLoyaltyPoints
{
    public function __construct(
        protected LoyaltyHelper $loyaltyHelper,
        protected LoyaltyPointService $loyaltyPointService
    ) {
    }

    public function handle(OrderPlacedEvent $event): void
    {
        $order = $event->order;

        if (! $this->loyaltyHelper->isEnabled()) {
            return;
        }

        // Check for guest member ID from session
        $guestCustomerId = session('loyalty_guest_customer_id');

        // Skip if no logged-in user and no guest member ID
        if (! $order->user_id && ! $guestCustomerId) {
            return;
        }

        $pointsToRedeem = (int) session('applied_loyalty_points', 0);
        $discount = (float) session('loyalty_points_discount', 0);

        // Only logged-in users can redeem points
        if ($order->user_id && $pointsToRedeem > 0 && $discount > 0) {
            // Calculate what the order amount should be WITHOUT loyalty discount
            $expectedAmountWithoutLoyalty = $order->sub_total
                - ($order->discount_amount ?? 0)
                + ($order->tax_amount ?? 0)
                + ($order->shipping_amount ?? 0)
                + ($order->payment_fee ?? 0);

            // Check if discount was already applied via ecommerce_cart_raw_total filter
            // by comparing current amount with expected amount
            $discountAlreadyApplied = $order->amount < $expectedAmountWithoutLoyalty;

            // Store original amount for points calculation
            // If discount was already applied, add it back to get original amount
            $originalAmount = $discountAlreadyApplied
                ? $order->amount + $discount
                : $order->amount;

            // Update order with loyalty discount
            $order->discount_amount = ($order->discount_amount ?? 0) + $discount;

            // Only subtract from amount if not already applied
            if (! $discountAlreadyApplied) {
                $order->amount = max(0, $order->amount - $discount);
            }

            $order->save();

            // Update invoice if it exists
            $this->updateInvoiceWithLoyaltyDiscount($order, $discount, $discountAlreadyApplied);

            // Redeem points from customer
            $this->loyaltyPointService->redeemPoints($order->user_id, $pointsToRedeem, $order->id, $order);

            // Calculate points to earn from the original amount (before discount)
            $pointsToEarn = $this->loyaltyHelper->calculatePointsFromAmount($originalAmount);
        } else {
            // Calculate points to earn from order amount
            $pointsToEarn = $this->loyaltyHelper->calculatePointsFromAmount($order->amount);
        }

        // Save to order loyalty points table with customer_id for guest orders
        OrderLoyaltyPoints::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'customer_id' => $guestCustomerId,
                'points_redeemed' => $pointsToRedeem,
                'discount_amount' => $discount,
                'points_to_earn' => $pointsToEarn,
            ]
        );

        // Clear session
        session()->forget([
            'loyalty_points_to_redeem',
            'applied_loyalty_points',
            'loyalty_points_discount',
            'loyalty_guest_member_id',
            'loyalty_guest_customer_id',
            'loyalty_guest_customer',
        ]);
    }

    protected function updateInvoiceWithLoyaltyDiscount($order, float $discount, bool $discountAlreadyApplied = false): void
    {
        $invoice = Invoice::query()
            ->where('reference_id', $order->id)
            ->where('reference_type', get_class($order))
            ->first();

        if (! $invoice) {
            return;
        }

        // Always update discount_amount for display purposes
        $invoice->discount_amount = ($invoice->discount_amount ?? 0) + $discount;

        // Only subtract from amount if discount wasn't already applied
        if (! $discountAlreadyApplied) {
            $invoice->amount = max(0, $invoice->amount - $discount);
        }

        $invoice->save();
    }
}
