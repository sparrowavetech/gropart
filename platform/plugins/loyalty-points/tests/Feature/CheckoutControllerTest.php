<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutControllerTest extends BaseTestCase
{
    use RefreshDatabase;

    protected LoyaltyPointService $service;

    protected LoyaltyHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LoyaltyPointService::class);
        $this->helper = app(LoyaltyHelper::class);
        $this->enableLoyaltyProgram();
    }

    protected function enableLoyaltyProgram(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
        setting()->forceSet('loyalty_points_points_redemption_rate', 100)->save();
        setting()->forceSet('loyalty_points_points_redemption_currency', 1)->save();
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

    public function test_loyalty_program_enabled(): void
    {
        $this->assertTrue($this->helper->isEnabled());
    }

    public function test_loyalty_program_can_be_disabled(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', false)->save();

        $helper = app(LoyaltyHelper::class);
        $this->assertFalse($helper->isEnabled());
    }

    public function test_redeem_points_requires_sufficient_balance(): void
    {
        $customer = $this->createCustomerWithPoints(100);

        $result = $this->service->redeemPoints($customer->id, 500);

        $this->assertFalse($result);
    }

    public function test_can_redeem_points_with_sufficient_balance(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $result = $this->service->redeemPoints($customer->id, 200);

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(300, $balance->total_points);
    }

    public function test_validation_fails_below_minimum(): void
    {
        setting()->forceSet('loyalty_points_min_redeemable_points', 100)->save();

        $customer = $this->createCustomerWithPoints(500);

        $errors = $this->service->validateRedemption($customer->id, 50, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validation_fails_above_maximum(): void
    {
        setting()->forceSet('loyalty_points_max_redeemable_points', 200)->save();

        $customer = $this->createCustomerWithPoints(500);

        $errors = $this->service->validateRedemption($customer->id, 300, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validation_fails_insufficient_balance(): void
    {
        $customer = $this->createCustomerWithPoints(100);

        $errors = $this->service->validateRedemption($customer->id, 500, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validation_passes_with_valid_redemption(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $errors = $this->service->validateRedemption($customer->id, 100, 100);

        $this->assertEmpty($errors);
    }

    public function test_session_can_store_applied_points(): void
    {
        session()->put('applied_loyalty_points', 100);
        session()->put('loyalty_points_discount', 1.00);

        $this->assertEquals(100, session('applied_loyalty_points'));
        $this->assertEquals(1.00, session('loyalty_points_discount'));
    }

    public function test_session_can_clear_applied_points(): void
    {
        session()->put('applied_loyalty_points', 100);
        session()->put('loyalty_points_discount', 1.00);

        session()->forget('applied_loyalty_points');
        session()->forget('loyalty_points_discount');

        $this->assertNull(session('applied_loyalty_points'));
        $this->assertNull(session('loyalty_points_discount'));
    }

    public function test_validation_rejects_negative_points(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $errors = $this->helper->validateRedeemablePoints(-100, 500, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_validation_rejects_zero_points(): void
    {
        $customer = $this->createCustomerWithPoints(500);

        $errors = $this->helper->validateRedeemablePoints(0, 500, 100);

        $this->assertNotEmpty($errors);
    }

    public function test_customer_with_no_balance_cannot_redeem(): void
    {
        $customer = $this->createCustomer();

        $result = $this->service->redeemPoints($customer->id, 100);

        $this->assertFalse($result);
    }

    public function test_discount_calculation(): void
    {
        $discount = $this->service->calculateDiscount(100);

        $this->assertIsFloat($discount);
        $this->assertGreaterThanOrEqual(0, $discount);
    }

    public function test_max_redemption_percentage_validation(): void
    {
        setting()->forceSet('loyalty_points_max_redemption_percentage', 20)->save();

        $helper = app(LoyaltyHelper::class);

        $this->assertEquals(20, $helper->getMaxRedemptionPercentage());
    }
}
