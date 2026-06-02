<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\OrderLoyaltyPoints;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class BulkOrderStatusChangeTest extends BaseTestCase
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
        $status = $status ?? OrderStatusEnum::PENDING;

        return Order::query()->create([
            'user_id' => $customer->id,
            'amount' => $amount,
            'sub_total' => $amount,
            'status' => $status,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);
    }

    protected function createGuestOrder(float $amount = 1000, ?string $status = null): Order
    {
        $status = $status ?? OrderStatusEnum::PENDING;

        return Order::query()->create([
            'user_id' => null,
            'amount' => $amount,
            'sub_total' => $amount,
            'status' => $status,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);
    }

    public function test_updated_content_event_awards_points_for_completed_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000, OrderStatusEnum::PENDING);

        // Simulate bulk status change to completed
        $order->status = OrderStatusEnum::COMPLETED;
        $order->completed_at = now();
        $order->save();

        // Fire UpdatedContentEvent (what bulk changes do)
        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, new Request(), $order));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance);
        $this->assertGreaterThan(0, $balance->total_points);
    }

    public function test_updated_content_event_reverses_points_for_cancelled_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000, OrderStatusEnum::COMPLETED);

        // First award points
        $this->service->awardPointsForOrder($order);

        $balanceBefore = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertGreaterThan(0, $balanceBefore->total_points);

        // Simulate bulk status change to cancelled
        $order->status = OrderStatusEnum::CANCELED;
        $order->save();

        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, new Request(), $order));

        $balanceAfter = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(0, $balanceAfter->total_points);
    }

    public function test_guest_order_with_loyalty_member_id_gets_points_via_order_completed_event(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createGuestOrder(1000, OrderStatusEnum::COMPLETED);

        // Create loyalty member ID record
        OrderLoyaltyPoints::query()->create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'points_earned' => 0,
            'points_redeemed' => 0,
            'discount_amount' => 0,
        ]);

        // Fire OrderCompletedEvent (normal flow)
        event(new OrderCompletedEvent($order));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance);
        $this->assertGreaterThan(0, $balance->total_points);
    }

    public function test_guest_order_with_loyalty_member_id_gets_points_via_updated_content_event(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createGuestOrder(1000, OrderStatusEnum::PENDING);

        // Create loyalty member ID record
        OrderLoyaltyPoints::query()->create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'points_earned' => 0,
            'points_redeemed' => 0,
            'discount_amount' => 0,
        ]);

        // Simulate bulk status change to completed
        $order->status = OrderStatusEnum::COMPLETED;
        $order->completed_at = now();
        $order->save();

        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, new Request(), $order));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance);
        $this->assertGreaterThan(0, $balance->total_points);
    }

    public function test_no_duplicate_points_when_both_events_fire(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000, OrderStatusEnum::COMPLETED);

        // Fire both events (edge case)
        event(new OrderCompletedEvent($order));
        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, new Request(), $order));

        $transactionCount = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->count();

        $this->assertEquals(1, $transactionCount);
    }

    public function test_reversal_deducts_from_correct_customer(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = Customer::query()->create([
            'name' => 'Test Customer 2',
            'email' => 'customer2@example.com',
            'password' => bcrypt('password'),
        ]);

        $order = $this->createOrder($customer1, 1000, OrderStatusEnum::COMPLETED);

        $this->service->awardPointsForOrder($order);

        // Verify customer1 has points
        $balance1 = CustomerPointBalance::query()->where('customer_id', $customer1->id)->first();
        $this->assertGreaterThan(0, $balance1->total_points);

        // Verify customer2 has no points
        $balance2 = CustomerPointBalance::query()->where('customer_id', $customer2->id)->first();
        $this->assertNull($balance2);

        // Reverse points
        $this->service->reversePointsForOrder($order);

        // Customer1 should have 0 points
        $balance1 = $balance1->fresh();
        $this->assertEquals(0, $balance1->total_points);
    }

    public function test_guest_order_reversal_deducts_from_loyalty_member(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createGuestOrder(1000, OrderStatusEnum::COMPLETED);

        // Create loyalty member ID record
        OrderLoyaltyPoints::query()->create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'points_earned' => 0,
            'points_redeemed' => 0,
            'discount_amount' => 0,
        ]);

        // Award points to loyalty member
        $this->service->awardPointsForOrder($order, $customer->id);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertGreaterThan(0, $balance->total_points);

        // Reverse points - should use earned transaction's customer_id
        $this->service->reversePointsForOrder($order);

        $balance = $balance->fresh();
        $this->assertEquals(0, $balance->total_points);
    }

    public function test_updated_content_event_listener_registered(): void
    {
        $events = app('events');
        $listeners = $events->getListeners(UpdatedContentEvent::class);

        $this->assertGreaterThan(0, count($listeners));
    }

    public function test_order_completed_event_listener_registered(): void
    {
        $events = app('events');
        $listeners = $events->getListeners(OrderCompletedEvent::class);

        $this->assertGreaterThan(0, count($listeners));
    }

    public function test_multiple_bulk_status_changes_no_duplicate_points(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000, OrderStatusEnum::PENDING);

        // First bulk change to completed
        $order->status = OrderStatusEnum::COMPLETED;
        $order->completed_at = now();
        $order->save();
        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, new Request(), $order));

        // Second bulk change (simulate re-saving)
        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, new Request(), $order));

        // Third bulk change
        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, new Request(), $order));

        $transactionCount = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->count();

        $this->assertEquals(1, $transactionCount);
    }

    public function test_processing_to_completed_via_bulk_change(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000, OrderStatusEnum::PROCESSING);

        // No points for processing
        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNull($balance);

        // Bulk change to completed
        $order->status = OrderStatusEnum::COMPLETED;
        $order->completed_at = now();
        $order->save();
        event(new UpdatedContentEvent(ORDER_MODULE_SCREEN_NAME, new Request(), $order));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance);
        $this->assertGreaterThan(0, $balance->total_points);
    }
}
