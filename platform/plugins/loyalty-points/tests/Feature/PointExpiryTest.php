<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PointExpiryTest extends BaseTestCase
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
        setting()->forceSet('loyalty_points_points_expiry_months', 12)->save();
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_new_transaction_has_expiry_date(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 100, 'Test bonus');

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->first();

        $this->assertNotNull($transaction->expires_at);
    }

    public function test_expiry_date_is_12_months_from_creation(): void
    {
        $customer = $this->createCustomer();

        $this->service->awardBonusPoints($customer->id, 100, 'Test bonus');

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->first();

        $expectedExpiry = now()->addMonths(12);

        $this->assertEquals(
            $expectedExpiry->format('Y-m-d'),
            $transaction->expires_at->format('Y-m-d')
        );
    }

    public function test_custom_expiry_months(): void
    {
        setting()->forceSet('loyalty_points_points_expiry_months', 6)->save();

        $service = app(LoyaltyPointService::class);
        $customer = $this->createCustomer();

        $service->awardBonusPoints($customer->id, 100, 'Test bonus');

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->first();

        $expectedExpiry = now()->addMonths(6);

        $this->assertEquals(
            $expectedExpiry->format('Y-m-d'),
            $transaction->expires_at->format('Y-m-d')
        );
    }

    public function test_no_expiry_when_months_zero(): void
    {
        setting()->forceSet('loyalty_points_points_expiry_months', 0)->save();

        $service = app(LoyaltyPointService::class);
        $customer = $this->createCustomer();

        $service->awardBonusPoints($customer->id, 100, 'Test bonus');

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->first();

        $this->assertNull($transaction->expires_at);
    }

    public function test_redeem_transaction_no_expiry(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 500,
            'lifetime_points' => 500,
        ]);

        $this->service->redeemPoints($customer->id, 100);

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->first();

        $this->assertNull($transaction->expires_at);
    }

    public function test_expire_old_points(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Old points',
            'created_at' => Carbon::now()->subMonths(13),
            'expires_at' => Carbon::now()->subMonth(),
        ]);

        $expiredCount = $this->service->expireOldPoints();

        $this->assertEquals(1, $expiredCount);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(0, $balance->total_points);
    }

    public function test_unexpired_points_not_affected(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Future points',
            'created_at' => now(),
            'expires_at' => Carbon::now()->addMonths(12),
        ]);

        $expiredCount = $this->service->expireOldPoints();

        $this->assertEquals(0, $expiredCount);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(100, $balance->total_points);
    }

    public function test_expire_creates_reverse_transaction(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Expiring points',
            'created_at' => Carbon::now()->subMonths(13),
            'expires_at' => Carbon::now()->subMonth(),
        ]);

        $this->service->expireOldPoints();

        $reverseTransaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REVERSE)
            ->first();

        $this->assertNotNull($reverseTransaction);
        $this->assertLessThan(0, $reverseTransaction->points);
    }

    public function test_no_expiry_when_program_disabled(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', false)->save();
        setting()->forceSet('loyalty_points_points_expiry_months', 0)->save();

        $service = app(LoyaltyPointService::class);

        $expiredCount = $service->expireOldPoints();

        $this->assertEquals(0, $expiredCount);
    }

    public function test_multiple_transactions_expire(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 300,
            'lifetime_points' => 300,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            PointTransaction::query()->create([
                'customer_id' => $customer->id,
                'type' => PointTransaction::TYPE_EARN,
                'points' => 100,
                'note' => "Expiring points {$i}",
                'created_at' => Carbon::now()->subMonths(13),
                'expires_at' => Carbon::now()->subMonth(),
            ]);
        }

        $expiredCount = $this->service->expireOldPoints();

        $this->assertEquals(3, $expiredCount);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(0, $balance->total_points);
    }

    public function test_partial_expiry_with_mixed_transactions(): void
    {
        $customer = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer->id,
            'total_points' => 200,
            'lifetime_points' => 200,
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Expired points',
            'created_at' => Carbon::now()->subMonths(13),
            'expires_at' => Carbon::now()->subMonth(),
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Valid points',
            'created_at' => now(),
            'expires_at' => Carbon::now()->addMonths(12),
        ]);

        $expiredCount = $this->service->expireOldPoints();

        $this->assertEquals(1, $expiredCount);

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(100, $balance->total_points);
    }
}
