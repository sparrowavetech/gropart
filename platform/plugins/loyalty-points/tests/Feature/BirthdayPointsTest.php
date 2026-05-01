<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BirthdayPointsTest extends BaseTestCase
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
        setting()->forceSet('loyalty_points_points_for_birthday', 200)->save();
        setting()->forceSet('loyalty_points_points_expiry_months', 12)->save();
    }

    protected function createCustomer(array $attributes = []): Customer
    {
        static $emailCounter = 0;
        $emailCounter++;

        return Customer::query()->create(array_merge([
            'name' => 'Test Customer',
            'email' => "customer{$emailCounter}@example.com",
            'password' => bcrypt('password'),
        ], $attributes));
    }

    public function test_birthday_points_setting_configured(): void
    {
        $this->assertEquals(200, $this->helper->getPointsForBirthday());
    }

    public function test_birthday_points_can_be_customized(): void
    {
        setting()->forceSet('loyalty_points_points_for_birthday', 500)->save();

        $helper = app(LoyaltyHelper::class);

        $this->assertEquals(500, $helper->getPointsForBirthday());
    }

    public function test_birthday_points_can_be_disabled(): void
    {
        setting()->forceSet('loyalty_points_points_for_birthday', 0)->save();

        $helper = app(LoyaltyHelper::class);

        $this->assertEquals(0, $helper->getPointsForBirthday());
    }

    public function test_can_award_birthday_bonus_points(): void
    {
        $customer = $this->createCustomer();

        $currentYear = now()->year;
        $result = $this->service->awardBonusPoints(
            $customer->id,
            200,
            "Birthday bonus for year {$currentYear}"
        );

        $this->assertTrue($result);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance);
        $this->assertEquals(200, $balance->total_points);
    }

    public function test_birthday_bonus_creates_transaction(): void
    {
        $customer = $this->createCustomer();

        $currentYear = now()->year;
        $this->service->awardBonusPoints(
            $customer->id,
            200,
            "Birthday bonus for year {$currentYear}"
        );

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals(200, $transaction->points);
        $this->assertStringContainsString('Birthday', $transaction->note);
        $this->assertStringContainsString((string) $currentYear, $transaction->note);
    }

    public function test_birthday_points_have_expiry(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints(
            $customer->id,
            200,
            'Birthday bonus'
        );

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertNotNull($transaction->expires_at);
    }

    public function test_no_birthday_points_when_program_disabled(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', false)->save();

        $service = app(LoyaltyPointService::class);
        $customer = $this->createCustomer();

        $result = $service->awardBonusPoints(
            $customer->id,
            200,
            'Birthday bonus'
        );

        $this->assertFalse($result);
    }

    public function test_multiple_customers_can_receive_birthday_points(): void
    {
        $customer1 = $this->createCustomer(['name' => 'Customer 1']);
        $customer2 = $this->createCustomer(['name' => 'Customer 2']);
        $customer3 = $this->createCustomer(['name' => 'Customer 3']);

        $this->service->awardBonusPoints($customer1->id, 200, 'Birthday bonus');
        $this->service->awardBonusPoints($customer2->id, 200, 'Birthday bonus');
        $this->service->awardBonusPoints($customer3->id, 200, 'Birthday bonus');

        $balance1 = CustomerPointBalance::query()->where('customer_id', $customer1->id)->first();
        $balance2 = CustomerPointBalance::query()->where('customer_id', $customer2->id)->first();
        $balance3 = CustomerPointBalance::query()->where('customer_id', $customer3->id)->first();

        $this->assertEquals(200, $balance1->total_points);
        $this->assertEquals(200, $balance2->total_points);
        $this->assertEquals(200, $balance3->total_points);
    }

    public function test_birthday_points_accumulate_with_other_points(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 100, 'Registration bonus');

        $this->service->awardBonusPoints($customer->id, 200, 'Birthday bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(300, $balance->total_points);
    }

    public function test_birthday_points_update_lifetime_points(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 200, 'Birthday bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(200, $balance->lifetime_points);
    }

    public function test_custom_birthday_points_amount_awarded(): void
    {
        setting()->forceSet('loyalty_points_points_for_birthday', 500)->save();

        $customer = $this->createCustomer();
        $birthdayPoints = app(LoyaltyHelper::class)->getPointsForBirthday();

        $this->service->awardBonusPoints($customer->id, $birthdayPoints, 'Birthday bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(500, $balance->total_points);
    }

    public function test_birthday_command_exists(): void
    {
        $this->assertTrue(class_exists(\Botble\LoyaltyPoints\Console\AwardBirthdayPointsCommand::class));
    }

    public function test_expire_command_exists(): void
    {
        $this->assertTrue(class_exists(\Botble\LoyaltyPoints\Console\ExpirePointsCommand::class));
    }
}
