<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerDashboardTest extends BaseTestCase
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
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createOrder(Customer $customer, float $amount = 100): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'amount' => $amount,
            'sub_total' => $amount,
            'status' => OrderStatusEnum::COMPLETED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'completed_at' => now(),
        ]);
    }

    public function test_customer_sees_current_balance(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 500,
            'lifetime_points' => 800,
        ]);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(500, $balance->total_points);
        $this->assertEquals(800, $balance->lifetime_points);
    }

    public function test_customer_balance_created_if_not_exists(): void
    {
        $customer = $this->createCustomer();

        $this->assertNull(CustomerPointBalance::query()->where('customer_id', $customer->id)->first());

        $balance = CustomerPointBalance::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            ['total_points' => 0, 'lifetime_points' => 0]
        );

        $this->assertNotNull($balance);
        $this->assertEquals(0, $balance->total_points);
    }

    public function test_customer_sees_transaction_history(): void
    {
        $customer = $this->createCustomer();

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Registration bonus',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Order completed',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REDEEM,
            'points' => -50,
            'note' => 'Redeemed for discount',
        ]);

        $transactions = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        $this->assertCount(3, $transactions);
    }

    public function test_transactions_paginated(): void
    {
        $customer = $this->createCustomer();

        for ($i = 1; $i <= 25; $i++) {
            PointTransaction::query()->create([
                'customer_id' => $customer->id,
                'type' => PointTransaction::TYPE_EARN,
                'points' => $i * 10,
                'note' => "Transaction {$i}",
            ]);
        }

        $transactions = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->paginate(20);

        $this->assertEquals(25, $transactions->total());
        $this->assertCount(20, $transactions->items());
    }

    public function test_customer_sees_current_level(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 500,
            'earning_rate' => 1.25,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 2,
        ]);

        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 600,
            'lifetime_points' => 600,
            'level_id' => $level->id,
        ]);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance->level);
        $this->assertEquals('Gold', $balance->level->name);
    }

    public function test_customer_sees_next_level_info(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 300,
            'lifetime_points' => 300,
        ]);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $nextLevel = LoyaltyLevel::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where('min_points', '>', $balance->lifetime_points)
            ->orderBy('min_points')
            ->first();

        $this->assertNotNull($nextLevel);
        $this->assertEquals('Silver', $nextLevel->name);
    }

    public function test_customer_at_max_level_no_next_level(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Platinum',
            'min_points' => 1000,
            'earning_rate' => 1.5,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 3,
        ]);

        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 2000,
            'lifetime_points' => 2000,
        ]);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $nextLevel = LoyaltyLevel::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where('min_points', '>', $balance->lifetime_points)
            ->orderBy('min_points')
            ->first();

        $this->assertNull($nextLevel);
    }

    public function test_transactions_show_order_info_if_available(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 500);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 50,
            'note' => 'Order points',
        ]);

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->with('order:id,code')
            ->first();

        $this->assertNotNull($transaction->order);
        $this->assertEquals($order->id, $transaction->order->id);
    }

    public function test_transactions_without_order(): void
    {
        $customer = $this->createCustomer();

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Registration bonus',
        ]);

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->with('order:id,code')
            ->first();

        $this->assertNull($transaction->order);
    }

    public function test_customer_only_sees_own_transactions(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = Customer::query()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@example.com',
            'password' => bcrypt('password'),
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer1->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Customer 1 points',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer2->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Customer 2 points',
        ]);

        $customer1Transactions = PointTransaction::query()
            ->where('customer_id', $customer1->id)
            ->get();

        $this->assertCount(1, $customer1Transactions);
        $this->assertEquals('Customer 1 points', $customer1Transactions->first()->note);
    }

    public function test_transactions_ordered_by_latest(): void
    {
        $customer = $this->createCustomer();

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'First transaction',
            'created_at' => now()->subDays(2),
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Second transaction',
            'created_at' => now()->subDay(),
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 300,
            'note' => 'Third transaction',
            'created_at' => now(),
        ]);

        $transactions = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        $this->assertEquals('Third transaction', $transactions->first()->note);
        $this->assertEquals('First transaction', $transactions->last()->note);
    }

    public function test_admin_routes_exist(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('loyalty-points.index'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('loyalty-points.transactions.index'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('loyalty-points.levels.index'));
    }

    public function test_balance_relationship_to_customer(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 500,
            'lifetime_points' => 500,
        ]);

        $this->assertEquals($customer->id, $balance->customer->id);
        $this->assertEquals('Test Customer', $balance->customer->name);
    }

    public function test_transaction_relationship_to_customer(): void
    {
        $customer = $this->createCustomer();

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Test',
        ]);

        $this->assertEquals($customer->id, $transaction->customer->id);
    }

    public function test_balance_can_get_customer_points(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 500,
            'lifetime_points' => 800,
        ]);

        $currentPoints = $this->service->getCustomerBalance($customer->id);

        $this->assertEquals(500, $currentPoints);
    }

    public function test_get_balance_returns_zero_for_new_customer(): void
    {
        $customer = $this->createCustomer();

        $currentPoints = $this->service->getCustomerBalance($customer->id);

        $this->assertEquals(0, $currentPoints);
    }
}
