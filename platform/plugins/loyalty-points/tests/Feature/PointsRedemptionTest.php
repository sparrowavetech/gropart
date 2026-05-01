<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PointsRedemptionTest extends BaseTestCase
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
        setting()->forceSet('loyalty_points_points_redemption_rate', 100)->save();
        setting()->forceSet('loyalty_points_points_redemption_currency', 1)->save();
        setting()->forceSet('loyalty_points_points_exchange_rate', 100)->save();
        setting()->forceSet('loyalty_points_min_redeemable_points', 0)->save();
        setting()->forceSet('loyalty_points_max_redeemable_points', 0)->save();
        setting()->forceSet('loyalty_points_max_redemption_percentage', 100)->save();
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createCustomerWithPoints(int $points = 500): Customer
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => $points,
            'lifetime_points' => $points,
        ]);

        return $customer;
    }

    protected function createOrder(Customer $customer, float $amount = 100): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'amount' => $amount,
            'sub_total' => $amount,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
        ]);
    }

    public function test_can_redeem_points(): void
    {
        $customer = $this->createCustomerWithPoints(500);
        $order = $this->createOrder($customer);

        $result = $this->service->redeemPoints($customer->id, 200, $order->id);

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(300, $balance->total_points);
    }

    public function test_redeem_creates_transaction(): void
    {
        $customer = $this->createCustomerWithPoints(500);
        $order = $this->createOrder($customer);

        $this->service->redeemPoints($customer->id, 200, $order->id);

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals(-200, $transaction->points);
        $this->assertEquals($order->id, $transaction->order_id);
    }

    public function test_cannot_redeem_more_than_balance(): void
    {
        $customer = $this->createCustomerWithPoints(100);

        $result = $this->service->redeemPoints($customer->id, 200);

        $this->assertFalse($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(100, $balance->total_points);
    }

    public function test_can_redeem_all_points(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $result = $this->service->redeemPoints($customer->id, 500);

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(0, $balance->total_points);
    }

    public function test_lifetime_points_unchanged_after_redemption(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $this->service->redeemPoints($customer->id, 200);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(500, $balance->lifetime_points);
    }

    public function test_redemption_without_order(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $result = $this->service->redeemPoints($customer->id, 100);

        $this->assertTrue($result);

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->first();

        $this->assertNull($transaction->order_id);
    }

    public function test_calculate_discount_from_points(): void
    {
        $discount = $this->service->calculateDiscount(100);

        $this->assertEquals(0.01, $discount);
    }

    public function test_calculate_discount_larger_points(): void
    {
        $discount = $this->service->calculateDiscount(500);

        $this->assertEquals(0.05, $discount);
    }

    public function test_validation_minimum_points(): void
    {
        setting()->forceSet('loyalty_points_min_redeemable_points', 100)->save();

        $customer = $this->createCustomerWithPoints(500);

        $errors = $this->service->validateRedemption($customer->id, 50, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validation_maximum_points(): void
    {
        setting()->forceSet('loyalty_points_max_redeemable_points', 200)->save();

        $customer = $this->createCustomerWithPoints(500);

        $errors = $this->service->validateRedemption($customer->id, 300, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validation_insufficient_balance(): void
    {
        $customer = $this->createCustomerWithPoints(100);

        $errors = $this->service->validateRedemption($customer->id, 200, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validation_passes_with_valid_redemption(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $errors = $this->service->validateRedemption($customer->id, 100, 100);

        $this->assertEmpty($errors);
    }

    public function test_multiple_redemptions(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $this->service->redeemPoints($customer->id, 100);
        $this->service->redeemPoints($customer->id, 100);
        $this->service->redeemPoints($customer->id, 100);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(200, $balance->total_points);

        $transactionCount = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->count();

        $this->assertEquals(3, $transactionCount);
    }

    public function test_redemption_creates_balance_if_not_exists(): void
    {
        $customer = $this->createCustomer();

        $result = $this->service->redeemPoints($customer->id, 100);

        $this->assertFalse($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($balance);
        $this->assertEquals(0, $balance->total_points);
    }

    public function test_no_redemption_when_program_disabled(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', false)->save();

        $customer = $this->createCustomerWithPoints(500);

        $service = app(LoyaltyPointService::class);
        $result = $service->redeemPoints($customer->id, 100);

        $this->assertFalse($result);
    }

    public function test_transaction_formatted_points_negative(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $this->service->redeemPoints($customer->id, 200);

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->first();

        $this->assertEquals('-200', $transaction->formatted_points);
    }
}
