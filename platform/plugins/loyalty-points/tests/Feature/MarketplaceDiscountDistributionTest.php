<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Regression coverage for ticket #4567670 (second issue).
 *
 * On a marketplace checkout the payment total is the sum of the per-vendor order
 * amounts, computed before the loyalty discount was ever applied - so PayPal
 * charged the full price while the order recorded the discount, overcharging the
 * buyer. The loyalty plugin now hooks the marketplace filter
 * `marketplace_checkout_orders_before_processing_payment` to reduce each order's
 * amount by its share of the discount so the discounted total reaches the gateway.
 */
class MarketplaceDiscountDistributionTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
    }

    protected function customer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function makeOrder(float $amount): Order
    {
        return Order::query()->create([
            'amount' => $amount,
            'sub_total' => $amount,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);
    }

    public function test_single_store_order_amount_is_reduced_by_full_discount(): void
    {
        $this->actingAs($this->customer(), 'customer');
        session()->put('loyalty_points_discount', 10);

        $orders = collect([$this->makeOrder(150)]);

        $result = apply_filters('marketplace_checkout_orders_before_processing_payment', $orders, request(), 'tok');

        $this->assertEquals(140.0, (float) $result->sum('amount'), 'Payment total must drop by the full $10 discount.');
        $this->assertEquals(140.0, (float) $result->first()->refresh()->amount);
    }

    public function test_multi_store_discount_is_distributed_and_sums_exactly(): void
    {
        $this->actingAs($this->customer(), 'customer');
        session()->put('loyalty_points_discount', 10);

        $a = $this->makeOrder(150);
        $b = $this->makeOrder(50);
        $orders = collect([$a, $b]);

        $result = apply_filters('marketplace_checkout_orders_before_processing_payment', $orders, request(), 'tok');

        // 200 total - 10 discount = 190, distributed without rounding drift.
        $this->assertEquals(190.0, (float) $result->sum('amount'), 'Distributed shares must sum to total minus discount exactly.');
        $this->assertEquals(142.5, (float) $a->refresh()->amount, '150 * (1 - 10/200) share.');
        $this->assertEquals(47.5, (float) $b->refresh()->amount, 'Last order absorbs the remainder.');
    }

    public function test_no_discount_session_leaves_orders_untouched(): void
    {
        $this->actingAs($this->customer(), 'customer');
        session()->forget('loyalty_points_discount');

        $orders = collect([$this->makeOrder(150)]);

        $result = apply_filters('marketplace_checkout_orders_before_processing_payment', $orders, request(), 'tok');

        $this->assertEquals(150.0, (float) $result->sum('amount'));
    }

    /**
     * The pre-payment filter reduces the order amount; the post-payment listener
     * must record discount_amount for display but must NOT subtract the discount a
     * second time (which would charge the buyer too little and corrupt the order).
     */
    public function test_filter_and_listener_do_not_double_apply_the_discount(): void
    {
        setting()->forceSet('loyalty_points_points_redemption_rate', 100)->save();
        setting()->forceSet('loyalty_points_points_redemption_currency', 1)->save();
        setting()->forceSet('loyalty_points_points_earning_rate', 1)->save();
        setting()->forceSet('loyalty_points_points_earning_currency', 100)->save();

        $customer = $this->customer();
        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 500,
            'lifetime_points' => 500,
        ]);
        $this->actingAs($customer, 'customer');

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 150,
            'sub_total' => 150,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        session()->put('applied_loyalty_points', 100);
        session()->put('loyalty_points_discount', 10);

        // Pre-payment: filter reduces the amount the gateway will charge.
        apply_filters('marketplace_checkout_orders_before_processing_payment', collect([$order]), request(), 'tok');
        $this->assertEquals(140.0, (float) $order->refresh()->amount);

        // Pre-payment: persist the redemption intent (as the real hook does).
        do_action(
            'ecommerce_before_processing_payment',
            collect(),
            request()->merge(['order_id' => $order->id]),
            'tok',
            []
        );

        // Post-payment webhook fires OrderPlacedEvent.
        event(new OrderPlacedEvent($order->refresh()));

        $order->refresh();
        $this->assertEquals(140.0, (float) $order->amount, 'Amount must NOT be reduced a second time.');
        $this->assertEquals(10.0, (float) $order->discount_amount, 'Discount recorded once for display.');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(400, $balance->total_points, 'Points redeemed exactly once.');
        $this->assertEquals(
            1,
            PointTransaction::query()->where('order_id', $order->id)->where('type', PointTransaction::TYPE_REDEEM)->count()
        );
    }

    public function test_discount_never_exceeds_payable_total(): void
    {
        $this->actingAs($this->customer(), 'customer');
        session()->put('loyalty_points_discount', 999);

        $orders = collect([$this->makeOrder(150)]);

        $result = apply_filters('marketplace_checkout_orders_before_processing_payment', $orders, request(), 'tok');

        $this->assertEquals(0.0, (float) $result->sum('amount'), 'Amount must clamp at 0, never go negative.');
    }
}
