<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistrationPointsTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableLoyaltyProgram();
    }

    protected function enableLoyaltyProgram(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
        setting()->forceSet('loyalty_points_points_for_registration', 100)->save();
        setting()->forceSet('loyalty_points_points_expiry_months', 12)->save();
    }

    public function test_customer_receives_points_on_registration(): void
    {
        $customer = Customer::query()->create([
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new Registered($customer));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance);
        $this->assertEquals(100, $balance->total_points);
        $this->assertEquals(100, $balance->lifetime_points);
    }

    public function test_registration_creates_earn_transaction(): void
    {
        $customer = Customer::query()->create([
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new Registered($customer));

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals(100, $transaction->points);
        $this->assertStringContainsString('registration', strtolower($transaction->note));
    }

    public function test_no_points_when_program_disabled(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', false)->save();

        $customer = Customer::query()->create([
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new Registered($customer));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNull($balance);
    }

    public function test_no_points_when_registration_points_zero(): void
    {
        setting()->forceSet('loyalty_points_points_for_registration', 0)->save();

        $customer = Customer::query()->create([
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new Registered($customer));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNull($balance);
    }

    public function test_no_duplicate_registration_points(): void
    {
        $customer = Customer::query()->create([
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new Registered($customer));
        event(new Registered($customer));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(100, $balance->total_points);

        $transactionCount = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->where('note', 'LIKE', '%registration%')
            ->count();

        $this->assertEquals(1, $transactionCount);
    }

    public function test_customer_gets_referral_code_on_registration(): void
    {
        $customer = Customer::query()->create([
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new Registered($customer));

        $customer = $customer->fresh();

        $this->assertNotNull($customer->referral_code);
        $this->assertEquals(8, strlen($customer->referral_code));
    }

    public function test_referral_codes_are_unique(): void
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

        event(new Registered($customer1));
        event(new Registered($customer2));

        $customer1 = $customer1->fresh();
        $customer2 = $customer2->fresh();

        $this->assertNotEquals($customer1->referral_code, $customer2->referral_code);
    }

    public function test_custom_registration_points_amount(): void
    {
        setting()->forceSet('loyalty_points_points_for_registration', 500)->save();

        $customer = Customer::query()->create([
            'name' => 'VIP Customer',
            'email' => 'vip@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new Registered($customer));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(500, $balance->total_points);
        $this->assertEquals(500, $balance->lifetime_points);
    }

    public function test_registration_points_have_expiry(): void
    {
        $customer = Customer::query()->create([
            'name' => 'New Customer',
            'email' => 'new@example.com',
            'password' => bcrypt('password'),
        ]);

        event(new Registered($customer));

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertNotNull($transaction->expires_at);
    }

    public function test_non_customer_registered_event_ignored(): void
    {
        $adminUser = new \stdClass();
        $adminUser->id = 999;
        $adminUser->name = 'Admin User';

        event(new Registered($adminUser));

        $balance = CustomerPointBalance::query()->where('customer_id', 999)->first();

        $this->assertNull($balance);
    }
}
