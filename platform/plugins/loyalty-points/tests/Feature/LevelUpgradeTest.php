<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Events\LevelUpgraded;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class LevelUpgradeTest extends BaseTestCase
{
    use RefreshDatabase;

    protected LoyaltyPointService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LoyaltyPointService::class);
        $this->enableLoyaltyProgram();
        $this->createLoyaltyLevels();
    }

    protected function enableLoyaltyProgram(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
        setting()->forceSet('loyalty_points_points_expiry_months', 12)->save();
    }

    protected function createLoyaltyLevels(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Bronze',
            'min_points' => 0,
            'earning_rate' => 1.0,
            'is_default' => true,
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

        LoyaltyLevel::query()->create([
            'name' => 'Gold',
            'min_points' => 2000,
            'earning_rate' => 1.25,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 2,
        ]);

        LoyaltyLevel::query()->create([
            'name' => 'Platinum',
            'min_points' => 5000,
            'earning_rate' => 1.5,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 3,
        ]);
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_new_customer_assigned_default_level(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 100, 'Welcome bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance->level_id);
        $this->assertEquals('Bronze', $balance->level->name);
    }

    public function test_level_upgrades_when_reaching_threshold(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 100, 'Initial bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals('Bronze', $balance->level->name);

        $this->service->awardBonusPoints($customer->id, 500, 'More points');

        $balance = $balance->fresh();
        $this->assertEquals('Silver', $balance->level->name);
    }

    public function test_level_upgrades_to_gold(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 2500, 'Big bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals('Gold', $balance->level->name);
    }

    public function test_level_upgrades_to_platinum(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 6000, 'VIP bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals('Platinum', $balance->level->name);
    }

    public function test_level_upgrade_event_dispatched(): void
    {
        Event::fake([LevelUpgraded::class]);

        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 100, 'Initial');

        $this->service->awardBonusPoints($customer->id, 600, 'Upgrade');

        Event::assertDispatched(LevelUpgraded::class);
    }

    public function test_level_updated_at_set_on_upgrade(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 100, 'Initial');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $initialLevelUpdatedAt = $balance->level_updated_at;

        sleep(1);

        $this->service->awardBonusPoints($customer->id, 600, 'Upgrade');

        $balance = $balance->fresh();

        $this->assertNotNull($balance->level_updated_at);
    }

    public function test_level_based_on_lifetime_points(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 2500,
        ]);

        $this->service->awardBonusPoints($customer->id, 100, 'Small bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals('Gold', $balance->level->name);
    }

    public function test_level_does_not_downgrade(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 2500,
            'lifetime_points' => 2500,
            'level_id' => LoyaltyLevel::query()->where('name', 'Gold')->first()->id,
        ]);

        $this->service->redeemPoints($customer->id, 2000);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals('Gold', $balance->level->name);
    }

    public function test_multiple_upgrades_in_single_transaction(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 6000, 'Mega bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals('Platinum', $balance->level->name);
    }

    public function test_draft_levels_not_considered(): void
    {
        LoyaltyLevel::query()->create([
            'name' => 'Diamond',
            'min_points' => 100,
            'earning_rate' => 2.0,
            'status' => BaseStatusEnum::DRAFT,
            'order' => 99,
        ]);

        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 200, 'Test');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNotEquals('Diamond', $balance->level->name);
    }

    public function test_level_earning_rate_applies(): void
    {
        $goldLevel = LoyaltyLevel::query()->where('name', 'Gold')->first();

        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 2500,
            'lifetime_points' => 2500,
            'level_id' => $goldLevel->id,
        ]);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(1.25, $balance->level->earning_rate);
    }
}
