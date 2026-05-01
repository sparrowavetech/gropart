<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerPointBalanceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_can_create_point_balance(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $this->assertDatabaseHas('ec_customer_points_balances', [
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);
    }

    public function test_can_add_points(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $balance->addPoints(50);

        $this->assertEquals(150, $balance->fresh()->total_points);
    }

    public function test_can_deduct_points(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $balance->deductPoints(30);

        $this->assertEquals(70, $balance->fresh()->total_points);
    }

    public function test_has_enough_points(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $this->assertTrue($balance->hasEnoughPoints(50));
        $this->assertTrue($balance->hasEnoughPoints(100));
        $this->assertFalse($balance->hasEnoughPoints(150));
    }

    public function test_balance_belongs_to_customer(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $this->assertEquals($customer->id, $balance->customer->id);
        $this->assertEquals('Test Customer', $balance->customer->name);
    }

    public function test_balance_can_have_level(): void
    {
        $customer = $this->createCustomer();

        $level = LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 1000,
            'earning_rate' => 1.5,
            'status' => 'published',
            'order' => 1,
        ]);

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 1500,
            'lifetime_points' => 1500,
            'level_id' => $level->id,
        ]);

        $this->assertEquals('Gold', $balance->level->name);
        $this->assertEquals(1.5, $balance->level->earning_rate);
    }

    public function test_balance_without_level(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $this->assertNull($balance->level);
    }

    public function test_lifetime_points_not_affected_by_add_points(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $balance->addPoints(50);
        $balance = $balance->fresh();

        $this->assertEquals(150, $balance->total_points);
        $this->assertEquals(100, $balance->lifetime_points);
    }

    public function test_lifetime_points_not_affected_by_deduct_points(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 200,
        ]);

        $balance->deductPoints(50);
        $balance = $balance->fresh();

        $this->assertEquals(50, $balance->total_points);
        $this->assertEquals(200, $balance->lifetime_points);
    }

    public function test_multiple_customers_have_separate_balances(): void
    {
        $customer1 = Customer::query()->create([
            'name' => 'Customer 1',
            'email' => 'customer1@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer2 = Customer::query()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@example.com',
            'password' => bcrypt('password'),
        ]);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer1->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer2->id,
            'total_points' => 500,
            'lifetime_points' => 500,
        ]);

        $balance1 = CustomerPointBalance::query()->where('customer_id', $customer1->id)->first();
        $balance2 = CustomerPointBalance::query()->where('customer_id', $customer2->id)->first();

        $this->assertEquals(100, $balance1->total_points);
        $this->assertEquals(500, $balance2->total_points);
    }

    public function test_balance_level_updated_at(): void
    {
        $customer = $this->createCustomer();

        $level = LoyaltyLevel::query()->create([
            'name' => 'Silver',
            'min_points' => 500,
            'earning_rate' => 1.1,
            'status' => 'published',
            'order' => 1,
        ]);

        $levelUpdatedAt = now();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 600,
            'lifetime_points' => 600,
            'level_id' => $level->id,
            'level_updated_at' => $levelUpdatedAt,
        ]);

        $this->assertNotNull($balance->level_updated_at);
        $this->assertEquals($levelUpdatedAt->format('Y-m-d H:i:s'), $balance->level_updated_at->format('Y-m-d H:i:s'));
    }
}
