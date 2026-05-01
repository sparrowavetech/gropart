<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

class AdminControllerTest extends BaseTestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected LoyaltyPointService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::query()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'super_user' => true,
        ]);

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

    protected function createLoyaltyLevel(): LoyaltyLevel
    {
        return LoyaltyLevel::query()->create([
            'name' => 'Test Level',
            'min_points' => 100,
            'earning_rate' => 1.0,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 0,
        ]);
    }

    public function test_can_create_loyalty_level_model(): void
    {
        $level = LoyaltyLevel::query()->create([
            'name' => 'New Level',
            'min_points' => 500,
            'earning_rate' => 1.5,
            'status' => BaseStatusEnum::PUBLISHED,
            'order' => 1,
        ]);

        $this->assertDatabaseHas('loyalty_levels', [
            'name' => 'New Level',
            'min_points' => 500,
        ]);
    }

    public function test_can_update_loyalty_level_model(): void
    {
        $level = $this->createLoyaltyLevel();

        $level->update([
            'name' => 'Updated Level',
            'min_points' => 200,
        ]);

        $this->assertDatabaseHas('loyalty_levels', [
            'id' => $level->id,
            'name' => 'Updated Level',
            'min_points' => 200,
        ]);
    }

    public function test_can_delete_loyalty_level_model(): void
    {
        $level = $this->createLoyaltyLevel();
        $levelId = $level->id;

        $level->delete();

        $this->assertDatabaseMissing('loyalty_levels', [
            'id' => $levelId,
        ]);
    }

    public function test_can_adjust_points_add(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $this->service->adjustPoints($customer->id, 50, 'Admin bonus');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(150, $balance->total_points);
    }

    public function test_can_adjust_points_subtract(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $this->service->adjustPoints($customer->id, -30, 'Admin deduction');

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(70, $balance->total_points);
    }

    public function test_adjust_points_creates_transaction(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        $this->service->adjustPoints($customer->id, 50, 'Test adjustment');

        $this->assertDatabaseHas('ec_customer_points_transactions', [
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_ADJUST,
            'points' => 50,
        ]);
    }

    public function test_admin_routes_exist(): void
    {
        $this->assertTrue(Route::has('loyalty-points.index'));
        $this->assertTrue(Route::has('loyalty-points.levels.index'));
        $this->assertTrue(Route::has('loyalty-points.levels.create'));
        $this->assertTrue(Route::has('loyalty-points.levels.store'));
        $this->assertTrue(Route::has('loyalty-points.levels.edit'));
        $this->assertTrue(Route::has('loyalty-points.levels.update'));
        $this->assertTrue(Route::has('loyalty-points.levels.destroy'));
        $this->assertTrue(Route::has('loyalty-points.transactions.index'));
        $this->assertTrue(Route::has('loyalty-points.members.index'));
        $this->assertTrue(Route::has('loyalty-points.members.show'));
        $this->assertTrue(Route::has('loyalty-points.members.adjust.store'));
        $this->assertTrue(Route::has('loyalty-points.settings.index'));
        $this->assertTrue(Route::has('loyalty-points.settings.update'));
    }

    public function test_unauthenticated_cannot_access_admin(): void
    {
        $response = $this->get(route('loyalty-points.index'));

        $response->assertRedirect();
    }

    public function test_customer_balance_creation(): void
    {
        $customer = $this->createCustomer();

        $balance = CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 500,
            'lifetime_points' => 500,
        ]);

        $this->assertDatabaseHas('ec_customer_points_balances', [
            'customer_id' => $customer->id,
            'total_points' => 500,
            'lifetime_points' => 500,
        ]);
    }
}
