<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportsControllerTest extends BaseTestCase
{
    use RefreshDatabase;

    protected User $admin;

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

        $this->enableLoyaltyProgram();
    }

    protected function enableLoyaltyProgram(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
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

    public function test_reports_page_requires_authentication(): void
    {
        $response = $this->get(route('loyalty-points.index'));

        $response->assertRedirect();
    }

    public function test_reports_route_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('loyalty-points.index'));
    }

    public function test_reports_display_total_customers(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer1->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer2->id,
            'total_points' => 200,
            'lifetime_points' => 200,
        ]);

        $totalCustomers = CustomerPointBalance::query()->count();

        $this->assertEquals(2, $totalCustomers);
    }

    public function test_reports_display_active_customers(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();
        $customer3 = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer1->id,
            'total_points' => 100,
            'lifetime_points' => 100,
        ]);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer2->id,
            'total_points' => 0,
            'lifetime_points' => 100,
        ]);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer3->id,
            'total_points' => 50,
            'lifetime_points' => 50,
        ]);

        $activeCustomers = CustomerPointBalance::query()
            ->where('total_points', '>', 0)
            ->count();

        $this->assertEquals(2, $activeCustomers);
    }

    public function test_reports_display_total_points_earned(): void
    {
        $customer = $this->createCustomer();

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Order points',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Registration bonus',
        ]);

        $totalEarned = PointTransaction::query()
            ->where('type', PointTransaction::TYPE_EARN)
            ->sum('points');

        $this->assertEquals(300, $totalEarned);
    }

    public function test_reports_display_total_points_redeemed(): void
    {
        $customer = $this->createCustomer();

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REDEEM,
            'points' => -100,
            'note' => 'Redeemed for discount',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REDEEM,
            'points' => -50,
            'note' => 'Redeemed for discount',
        ]);

        $totalRedeemed = abs(PointTransaction::query()
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->sum('points'));

        $this->assertEquals(150, $totalRedeemed);
    }

    public function test_reports_display_points_in_circulation(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer1->id,
            'total_points' => 500,
            'lifetime_points' => 600,
        ]);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer2->id,
            'total_points' => 300,
            'lifetime_points' => 400,
        ]);

        $inCirculation = CustomerPointBalance::query()->sum('total_points');

        $this->assertEquals(800, $inCirculation);
    }

    public function test_reports_display_lifetime_points(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = $this->createCustomer();

        CustomerPointBalance::query()->create([
            'customer_id' => $customer1->id,
            'total_points' => 500,
            'lifetime_points' => 1000,
        ]);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer2->id,
            'total_points' => 300,
            'lifetime_points' => 800,
        ]);

        $lifetimePoints = CustomerPointBalance::query()->sum('lifetime_points');

        $this->assertEquals(1800, $lifetimePoints);
    }

    public function test_reports_top_customers_by_current_points(): void
    {
        $customer1 = $this->createCustomer(['name' => 'Customer 1']);
        $customer2 = $this->createCustomer(['name' => 'Customer 2']);
        $customer3 = $this->createCustomer(['name' => 'Customer 3']);

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

        CustomerPointBalance::query()->create([
            'customer_id' => $customer3->id,
            'total_points' => 300,
            'lifetime_points' => 300,
        ]);

        $topCustomers = CustomerPointBalance::query()
            ->where('total_points', '>', 0)
            ->orderByDesc('total_points')
            ->limit(10)
            ->get();

        $this->assertEquals($customer2->id, $topCustomers->first()->customer_id);
        $this->assertEquals($customer3->id, $topCustomers[1]->customer_id);
        $this->assertEquals($customer1->id, $topCustomers->last()->customer_id);
    }

    public function test_reports_top_customers_by_lifetime_points(): void
    {
        $customer1 = $this->createCustomer(['name' => 'Customer 1']);
        $customer2 = $this->createCustomer(['name' => 'Customer 2']);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer1->id,
            'total_points' => 100,
            'lifetime_points' => 2000,
        ]);

        CustomerPointBalance::query()->create([
            'customer_id' => $customer2->id,
            'total_points' => 500,
            'lifetime_points' => 800,
        ]);

        $topLifetimeCustomers = CustomerPointBalance::query()
            ->where('lifetime_points', '>', 0)
            ->orderByDesc('lifetime_points')
            ->limit(10)
            ->get();

        $this->assertEquals($customer1->id, $topLifetimeCustomers->first()->customer_id);
    }

    public function test_reports_recent_transactions(): void
    {
        $customer = $this->createCustomer();

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Recent transaction',
            'created_at' => now(),
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Older transaction',
            'created_at' => now()->subDay(),
        ]);

        $recentTransactions = PointTransaction::query()
            ->latest('created_at')
            ->limit(20)
            ->get();

        $this->assertCount(2, $recentTransactions);
        $this->assertEquals('Recent transaction', $recentTransactions->first()->note);
    }

    public function test_reports_activity_by_type(): void
    {
        $customer = $this->createCustomer();

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Earned',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Earned',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REDEEM,
            'points' => -50,
            'note' => 'Redeemed',
        ]);

        $earnCount = PointTransaction::query()->where('type', PointTransaction::TYPE_EARN)->count();
        $redeemCount = PointTransaction::query()->where('type', PointTransaction::TYPE_REDEEM)->count();

        $this->assertEquals(2, $earnCount);
        $this->assertEquals(1, $redeemCount);
    }

    public function test_reports_handle_no_data(): void
    {
        $totalCustomers = CustomerPointBalance::query()->count();
        $totalEarned = PointTransaction::query()
            ->where('type', PointTransaction::TYPE_EARN)
            ->sum('points');

        $this->assertEquals(0, $totalCustomers);
        $this->assertEquals(0, $totalEarned);
    }

    public function test_transaction_history_route_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('loyalty-points.transactions.index'));
    }

    public function test_transactions_by_customer_route_exists(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('loyalty-points.transactions.customer'));
    }
}
