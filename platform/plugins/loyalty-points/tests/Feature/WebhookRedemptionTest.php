<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\OrderLoyaltyPoints;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Regression coverage for ticket #4566912.
 *
 * For redirect-based payment gateways (Stripe Checkout, PayPal Standard, ...)
 * the gateway calls back via webhook with no PHP session. The redemption intent
 * must be recovered from the order_loyalty_points row that was persisted during
 * ecommerce_before_processing_payment so the customer's balance is decremented
 * and a REDEEM transaction is recorded.
 */
class WebhookRedemptionTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
        setting()->forceSet('loyalty_points_points_redemption_rate', 100)->save();
        setting()->forceSet('loyalty_points_points_redemption_currency', 1)->save();
        setting()->forceSet('loyalty_points_points_earning_rate', 1)->save();
        setting()->forceSet('loyalty_points_points_earning_currency', 100)->save();
    }

    protected function createCustomerWithPoints(int $points = 500): Customer
    {
        $customer = Customer::query()->create([
            'name' => 'Webhook Buyer',
            'email' => 'webhook@example.com',
            'password' => bcrypt('password'),
        ]);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => $points,
            'lifetime_points' => $points,
        ]);

        return $customer;
    }

    protected function createOrderWithLoyaltyDiscountApplied(Customer $customer): Order
    {
        // Simulates the post-checkout state: cart filter already removed $1
        // from the total, so the order is saved as $18 (sub_total $17 + ship $2 - $1).
        return Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 18,
            'sub_total' => 17,
            'shipping_amount' => 2,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);
    }

    public function test_webhook_redemption_recovers_intent_from_db_when_session_is_empty(): void
    {
        $customer = $this->createCustomerWithPoints(500);
        $order = $this->createOrderWithLoyaltyDiscountApplied($customer);

        // Pre-payment hook persisted the intent (user-facing context, session present).
        OrderLoyaltyPoints::query()->create([
            'order_id' => $order->id,
            'points_redeemed' => 100,
            'discount_amount' => 1,
            'points_to_earn' => 0,
        ]);

        // Webhook context: no session keys for the listener to read.
        session()->forget([
            'applied_loyalty_points',
            'loyalty_points_discount',
            'loyalty_guest_customer_id',
        ]);

        event(new OrderPlacedEvent($order->refresh()));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(400, $balance->total_points, 'Customer balance must be decremented by 100.');

        $redemption = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->first();
        $this->assertNotNull($redemption, 'A REDEEM PointTransaction must be created.');
        $this->assertEquals(-100, $redemption->points);

        $orderLoyalty = OrderLoyaltyPoints::query()->where('order_id', $order->id)->first();
        $this->assertEquals(100, $orderLoyalty->points_redeemed);
        $this->assertEquals(1, (float) $orderLoyalty->discount_amount);
    }

    public function test_webhook_fired_twice_does_not_double_redeem(): void
    {
        $customer = $this->createCustomerWithPoints(500);
        $order = $this->createOrderWithLoyaltyDiscountApplied($customer);

        OrderLoyaltyPoints::query()->create([
            'order_id' => $order->id,
            'points_redeemed' => 100,
            'discount_amount' => 1,
            'points_to_earn' => 0,
        ]);

        session()->forget(['applied_loyalty_points', 'loyalty_points_discount']);

        event(new OrderPlacedEvent($order->refresh()));
        event(new OrderPlacedEvent($order->refresh()));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(400, $balance->total_points, 'Balance must not double-deduct on retry.');

        $redemptionCount = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->count();
        $this->assertEquals(1, $redemptionCount, 'Only one REDEEM transaction should exist.');
    }

    public function test_stale_intent_is_cleared_when_buyer_removes_loyalty_on_retry(): void
    {
        $customer = $this->createCustomerWithPoints(500);
        $order = $this->createOrderWithLoyaltyDiscountApplied($customer);

        // First attempt: pre-payment hook persisted the intent.
        OrderLoyaltyPoints::query()->create([
            'order_id' => $order->id,
            'points_redeemed' => 100,
            'discount_amount' => 1,
            'points_to_earn' => 0,
        ]);

        // Buyer removed the loyalty discount on retry, so session is empty when
        // the pre-payment hook fires again on the same order_id.
        session()->forget(['applied_loyalty_points', 'loyalty_points_discount', 'loyalty_guest_customer_id']);

        do_action(
            'ecommerce_before_processing_payment',
            collect(),
            request()->merge(['order_id' => $order->id]),
            'tok',
            []
        );

        $this->assertNull(
            OrderLoyaltyPoints::query()->where('order_id', $order->id)->first(),
            'Stale intent row must be deleted when buyer cancels the loyalty discount on retry.'
        );

        // Now fire the webhook: with no intent and no session, no redemption occurs.
        event(new OrderPlacedEvent($order->refresh()));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(500, $balance->total_points);
        $this->assertEquals(
            0,
            PointTransaction::query()->where('order_id', $order->id)->where('type', PointTransaction::TYPE_REDEEM)->count()
        );
    }

    /**
     * Regression for ticket #4567670.
     *
     * Marketplace checkout merges order_id as an ARRAY of per-store order IDs
     * (marketplace/OrderSupportServiceProvider.php). The pre-payment hook used to
     * pass that array straight into OrderLoyaltyPoints::updateOrCreate(), which
     * made Laravel's query grammar treat each column value as a row and crash with
     * "parameterize(): Argument #1 must be of type array, null given" on the null
     * customer_id (logged-in redeemer) - an HTTP 500 at checkout on marketplace
     * sites. The hook must normalize the array to a single order_id.
     */
    public function test_marketplace_array_order_id_does_not_crash_and_persists_single_intent(): void
    {
        $customer = $this->createCustomerWithPoints(500);
        $first = $this->createOrderWithLoyaltyDiscountApplied($customer);
        $second = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 10,
            'sub_total' => 10,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        // Logged-in redeemer -> guest customer_id is null (the value that crashed).
        session()->forget(['loyalty_guest_customer_id']);
        session()->put('applied_loyalty_points', 100);
        session()->put('loyalty_points_discount', 1);

        do_action(
            'ecommerce_before_processing_payment',
            collect(),
            request()->merge(['order_id' => [$first->id, $second->id]]),
            'tok',
            []
        );

        // Intent persisted once, against the first order id - no crash.
        $this->assertNotNull(
            OrderLoyaltyPoints::query()->where('order_id', $first->id)->first(),
            'Intent must be persisted against the first marketplace order id.'
        );
        $this->assertNull(
            OrderLoyaltyPoints::query()->where('order_id', $second->id)->first(),
            'Intent must NOT be duplicated across vendor orders.'
        );

        $intent = OrderLoyaltyPoints::query()->where('order_id', $first->id)->first();
        $this->assertEquals(100, $intent->points_redeemed);
        $this->assertEquals(1, (float) $intent->discount_amount);
    }

    /**
     * Audit for ticket #4567670: on a marketplace on-site checkout the session is
     * still populated when OrderPlacedEvent fires for EACH vendor order. The
     * customer's balance must be decremented only ONCE for the whole cart, never
     * once per vendor order.
     */
    public function test_marketplace_multi_order_redeems_points_only_once(): void
    {
        $customer = $this->createCustomerWithPoints(500);
        $first = $this->createOrderWithLoyaltyDiscountApplied($customer);
        $second = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 10,
            'sub_total' => 10,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        // On-site checkout: session is live for the redeeming customer.
        session()->forget(['loyalty_guest_customer_id']);
        session()->put('applied_loyalty_points', 100);
        session()->put('loyalty_points_discount', 1);

        // Pre-payment hook persists intent (array order_id, marketplace).
        do_action(
            'ecommerce_before_processing_payment',
            collect(),
            request()->merge(['order_id' => [$first->id, $second->id]]),
            'tok',
            []
        );

        // Worst-case ordering: the order WITHOUT the intent row fires first
        // (drives redemption from session, clears it), then the order WITH the
        // intent row fires (would recover from DB and redeem a second time).
        event(new OrderPlacedEvent($second->refresh()));
        event(new OrderPlacedEvent($first->refresh()));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(400, $balance->total_points, 'Points must be redeemed once for the whole cart, not per vendor order.');

        $redemptionCount = PointTransaction::query()
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->whereIn('order_id', [$first->id, $second->id])
            ->count();
        $this->assertEquals(1, $redemptionCount, 'Exactly one REDEEM transaction across all vendor orders.');
    }

    public function test_no_db_intent_and_no_session_skips_redemption(): void
    {
        $customer = $this->createCustomerWithPoints(500);
        $order = $this->createOrderWithLoyaltyDiscountApplied($customer);

        session()->forget(['applied_loyalty_points', 'loyalty_points_discount']);

        event(new OrderPlacedEvent($order->refresh()));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(500, $balance->total_points, 'No redemption intent means balance stays put.');

        $redemption = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->first();
        $this->assertNull($redemption);
    }
}
