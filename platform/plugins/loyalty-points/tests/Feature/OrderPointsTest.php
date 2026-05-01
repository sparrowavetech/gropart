<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderPointsTest extends BaseTestCase
{
    use RefreshDatabase;

    protected LoyaltyPointService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LoyaltyPointService::class);
        $this->enableLoyaltyProgram();
    }

    protected function enableLoyaltyProgram(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
        setting()->forceSet('loyalty_points_points_earning_rate', 1)->save();
        setting()->forceSet('loyalty_points_points_earning_currency', 100)->save();
        setting()->forceSet('loyalty_points_points_exchange_rate', 1)->save();
        setting()->forceSet('loyalty_points_points_expiry_months', 12)->save();
        setting()->forceSet('loyalty_points_eligible_order_statuses', json_encode(['completed']))->save();
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createOrder(Customer $customer, float $amount = 100, ?string $status = null): Order
    {
        $status = $status ?? OrderStatusEnum::COMPLETED;

        return Order::query()->create([
            'user_id' => $customer->id,
            'amount' => $amount,
            'sub_total' => $amount,
            'status' => $status,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'completed_at' => $status === OrderStatusEnum::COMPLETED ? now() : null,
        ]);
    }

    public function test_earn_points_from_completed_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $result = $this->service->awardPointsForOrder($order);

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($balance);
        $this->assertEquals(10, $balance->total_points);
        $this->assertEquals(10, $balance->lifetime_points);
    }

    public function test_earn_points_calculation(): void
    {
        $customer = $this->createCustomer();

        $order500 = $this->createOrder($customer, 500);
        $this->service->awardPointsForOrder($order500);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(5, $balance->total_points);
    }

    public function test_no_points_for_pending_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000, OrderStatusEnum::PENDING);

        $result = $this->service->awardPointsForOrder($order);

        $this->assertFalse($result);
    }

    public function test_no_duplicate_points_for_same_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);
        $this->service->awardPointsForOrder($order);

        $transactionCount = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->count();

        $this->assertEquals(1, $transactionCount);
    }

    public function test_points_reversed_on_cancelled_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);

        $balanceBefore = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $pointsBefore = $balanceBefore->total_points;

        $this->service->reversePointsForOrder($order);

        $balanceAfter = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(0, $balanceAfter->total_points);
    }

    public function test_reverse_creates_transaction(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);
        $this->service->reversePointsForOrder($order);

        $reverseTransaction = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_REVERSE)
            ->first();

        $this->assertNotNull($reverseTransaction);
        $this->assertLessThan(0, $reverseTransaction->points);
    }

    public function test_level_multiplier_applied(): void
    {
        $goldLevel = LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 0,
            'earning_rate' => 1.5,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 2,
        ]);

        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 2000,
            'lifetime_points' => 2000,
            'level_id' => $goldLevel->id,
        ]);

        $order = $this->createOrder($customer, 1000);
        $this->service->awardPointsForOrder($order);

        $transaction = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertEquals(15, $transaction->points);
    }

    public function test_multiple_orders_accumulate_points(): void
    {
        $customer = $this->createCustomer();

        $order1 = $this->createOrder($customer, 500);
        $order2 = $this->createOrder($customer, 300);
        $order3 = $this->createOrder($customer, 200);

        $this->service->awardPointsForOrder($order1);
        $this->service->awardPointsForOrder($order2);
        $this->service->awardPointsForOrder($order3);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(10, $balance->total_points);
    }

    public function test_order_completed_event_awards_points(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        event(new OrderCompletedEvent($order));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($balance);
    }

    public function test_no_points_for_guest_order(): void
    {
        $order = Order::query()->create([
            'user_id' => 0,
            'amount' => 1000,
            'sub_total' => 1000,
            'status' => OrderStatusEnum::COMPLETED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'completed_at' => now(),
        ]);

        $result = $this->service->awardPointsForOrder($order);

        $this->assertFalse($result);
    }

    public function test_points_with_discount_order(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 800,
            'sub_total' => 1000,
            'discount_amount' => 200,
            'status' => OrderStatusEnum::COMPLETED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'completed_at' => now(),
        ]);

        $this->service->awardPointsForOrder($order);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($balance);
        $this->assertGreaterThan(0, $balance->total_points);
    }

    public function test_recalculate_points_for_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);

        $balanceBefore = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $pointsBefore = $balanceBefore->total_points;

        $this->service->recalculatePointsForOrder($order);

        $balanceAfter = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals($pointsBefore, $balanceAfter->total_points);
    }

    public function test_transaction_linked_to_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertNotNull($transaction->order);
        $this->assertEquals($order->id, $transaction->order->id);
    }

    public function test_transaction_note_contains_order_code(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);

        $transaction = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertStringContainsString($order->code, $transaction->note);
    }

    public function test_small_order_may_earn_zero_points(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 50);

        $result = $this->service->awardPointsForOrder($order);

        $this->assertFalse($result);
    }
}
