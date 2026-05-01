<?php

namespace Botble\LoyaltyPoints\Tests\Unit;

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

class LoyaltyPointServiceTest extends BaseTestCase
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
        setting()->forceSet('loyalty_points_points_redemption_rate', 100)->save();
        setting()->forceSet('loyalty_points_points_redemption_currency', 1)->save();
        setting()->forceSet('loyalty_points_points_for_registration', 100)->save();
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

    public function test_can_award_bonus_points(): void
    {
        $customer = $this->createCustomer();

        $result = $this->service->awardBonusPoints($customer->id, 100, 'Registration bonus');

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(100, $balance->total_points);
        $this->assertEquals(100, $balance->lifetime_points);

        $this->assertDatabaseHas('ec_customer_points_transactions', [
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
        ]);
    }

    public function test_award_bonus_points_zero_returns_false(): void
    {
        $customer = $this->createCustomer();

        $result = $this->service->awardBonusPoints($customer->id, 0, 'Zero points');

        $this->assertFalse($result);
    }

    public function test_award_bonus_points_negative_returns_false(): void
    {
        $customer = $this->createCustomer();

        $result = $this->service->awardBonusPoints($customer->id, -50, 'Negative points');

        $this->assertFalse($result);
    }

    public function test_can_redeem_points(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 500,
            'lifetime_points' => 500,
        ]);

        $result = $this->service->redeemPoints($customer->id, 200);

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(300, $balance->total_points);
        $this->assertEquals(500, $balance->lifetime_points);
    }

    public function test_redeem_points_insufficient_balance_returns_false(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $result = $this->service->redeemPoints($customer->id, 200);

        $this->assertFalse($result);
    }

    public function test_can_adjust_points_positive(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $result = $this->service->adjustPoints($customer->id, 50, 'Admin bonus');

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(150, $balance->total_points);
    }

    public function test_can_adjust_points_negative(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $result = $this->service->adjustPoints($customer->id, -30, 'Admin deduction');

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(70, $balance->total_points);
    }

    public function test_get_customer_balance(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 250,
            'lifetime_points' => 250,
        ]);

        $balance = $this->service->getCustomerBalance($customer->id);

        $this->assertEquals(250, $balance);
    }

    public function test_get_customer_balance_no_record(): void
    {
        $customer = $this->createCustomer();

        $balance = $this->service->getCustomerBalance($customer->id);

        $this->assertEquals(0, $balance);
    }

    public function test_award_points_for_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $result = $this->service->awardPointsForOrder($order);

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertGreaterThan(0, $balance->total_points);
    }

    public function test_award_points_for_order_not_duplicated(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);
        $result = $this->service->awardPointsForOrder($order);

        $this->assertFalse($result);

        $transactionCount = PointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->count();

        $this->assertEquals(1, $transactionCount);
    }

    public function test_reverse_points_for_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);

        $balanceBefore = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $pointsBefore = $balanceBefore->total_points;

        $result = $this->service->reversePointsForOrder($order);

        $this->assertTrue($result);

        $balanceAfter = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertLessThan($pointsBefore, $balanceAfter->total_points);
    }

    public function test_reverse_points_for_order_no_earned_transaction(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer);

        $result = $this->service->reversePointsForOrder($order);

        $this->assertFalse($result);
    }

    public function test_calculate_discount(): void
    {
        $discount = $this->service->calculateDiscount(100);

        $this->assertIsFloat($discount);
        $this->assertGreaterThanOrEqual(0, $discount);
    }

    public function test_validate_redemption(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 500,
            'lifetime_points' => 500,
        ]);

        $errors = $this->service->validateRedemption($customer->id, 100, 50.00);

        $this->assertIsArray($errors);
    }

    public function test_level_upgrade_on_points_award(): void
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

        $this->service->awardBonusPoints($customer->id, 100, 'First bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($balance->level_id);

        $this->service->awardBonusPoints($customer->id, 500, 'Big bonus');

        $balance = $balance->fresh();
        $this->assertEquals('Silver', $balance->level->name);
    }

    public function test_points_with_level_multiplier(): void
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

        $this->assertNotNull($transaction);
    }

    public function test_creates_balance_if_not_exists(): void
    {
        $customer = $this->createCustomer();

        $this->assertNull(CustomerPointBalance::query()->where('customer_id', $customer->id)->first());

        $this->service->awardBonusPoints($customer->id, 100, 'First points');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($balance);
        $this->assertEquals(100, $balance->total_points);
    }

    public function test_recalculate_points_for_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer, 1000);

        $this->service->awardPointsForOrder($order);

        $result = $this->service->recalculatePointsForOrder($order);

        $this->assertTrue($result);

        $transactionCount = PointTransaction::query()
            ->where('order_id', $order->id)
            ->count();

        $this->assertEquals(2, $transactionCount);
    }
}
