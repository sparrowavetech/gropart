<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderStatusIntegrationTest extends BaseTestCase
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

    protected function createOrder(Customer $customer, float $amount = 1000, ?string $status = null): Order
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

    public function test_points_awarded_when_order_completed(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        event(new OrderCompletedEvent($order));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance);
        $this->assertGreaterThan(0, $balance->total_points);
    }

    public function test_points_reversed_when_order_cancelled(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);

        $balanceBefore = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $pointsBefore = $balanceBefore->total_points;

        event(new OrderCancelledEvent($order));

        $balanceAfter = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(0, $balanceAfter->total_points);
    }

    public function test_reverse_creates_transaction_record(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);

        event(new OrderCancelledEvent($order));

        $reverseTransaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REVERSE)
            ->first();

        $this->assertNotNull($reverseTransaction);
        $this->assertLessThan(0, $reverseTransaction->points);
    }

    public function test_cancel_without_award_no_effect(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        event(new OrderCancelledEvent($order));

        $reverseCount = PointTransaction::query()
            ->where('type', PointTransaction::TYPE_REVERSE)
            ->count();

        $this->assertEquals(0, $reverseCount);
    }

    public function test_guest_order_cancelled_no_effect(): void
    {
        $order = Order::query()->create([
            'user_id' => 0,
            'amount' => 1000,
            'sub_total' => 1000,
            'status' => OrderStatusEnum::CANCELED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        event(new OrderCancelledEvent($order));

        $reverseCount = PointTransaction::query()
            ->where('type', PointTransaction::TYPE_REVERSE)
            ->count();

        $this->assertEquals(0, $reverseCount);
    }

    public function test_pending_order_no_points(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000, OrderStatusEnum::PENDING);

        $result = $this->service->awardPointsForOrder($order);

        $this->assertFalse($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNull($balance);
    }

    public function test_processing_order_no_points(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000, OrderStatusEnum::PROCESSING);

        $result = $this->service->awardPointsForOrder($order);

        $this->assertFalse($result);
    }

    public function test_full_order_lifecycle(): void
    {
        $customer = $this->createCustomer();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'amount' => 1000,
            'sub_total' => 1000,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);

        $result = $this->service->awardPointsForOrder($order);
        $this->assertFalse($result);

        $order->update(['status' => OrderStatusEnum::PROCESSING]);
        $result = $this->service->awardPointsForOrder($order);
        $this->assertFalse($result);

        $order->update([
            'status' => OrderStatusEnum::COMPLETED,
            'completed_at' => now(),
        ]);

        event(new OrderCompletedEvent($order));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertGreaterThan(0, $balance->total_points);
        $pointsEarned = $balance->total_points;

        event(new OrderCancelledEvent($order));

        $balance = $balance->fresh();
        $this->assertEquals(0, $balance->total_points);
    }

    public function test_recalculate_points_after_order_modification(): void
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

    public function test_lifetime_points_unchanged_on_cancellation(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $lifetimeBefore = $balance->lifetime_points;

        event(new OrderCancelledEvent($order));

        $balance = $balance->fresh();

        $this->assertEquals($lifetimeBefore, $balance->lifetime_points);
    }

    public function test_level_not_downgraded_on_cancellation(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'is_default' => true,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        $silverLevel = LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 600,
            'lifetime_points' => 600,
            'level_id' => $silverLevel->id,
        ]);

        $order = $this->createOrder($customer, 1000);
        $this->service->awardPointsForOrder($order);

        event(new OrderCancelledEvent($order));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals('Silver', $balance->level->name);
    }

    public function test_multiple_orders_independent_reversal(): void
    {
        $customer = $this->createCustomer();

        $order1 = $this->createOrder($customer, 500);
        $order2 = $this->createOrder($customer, 300);

        $this->service->awardPointsForOrder($order1);
        $this->service->awardPointsForOrder($order2);

        $balanceAfterBoth = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $totalAfterBoth = $balanceAfterBoth->total_points;

        event(new OrderCancelledEvent($order1));

        $balanceAfterCancel = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertGreaterThan(0, $balanceAfterCancel->total_points);
        $this->assertLessThan($totalAfterBoth, $balanceAfterCancel->total_points);
    }

    public function test_order_with_discount_points_calculation(): void
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

        $this->assertGreaterThan(0, $balance->total_points);
    }

    public function test_order_completed_listener_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Botble\LoyaltyPoints\Listeners\AwardPointsForCompletedOrder::class)
        );
    }

    public function test_order_cancelled_listener_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Botble\LoyaltyPoints\Listeners\ReversePointsForCancelledOrder::class)
        );
    }
}
