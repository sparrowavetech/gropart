<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\CustomerGroupAssignment;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerWholesaleDashboardTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::query()->create([
            'name' => 'Dashboard Test Customer',
            'email' => 'dashboard-test@example.com',
            'password' => bcrypt('password'),
            'confirmed_at' => now(),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);
    }

    public function test_guest_redirected_from_wholesale_dashboard(): void
    {
        $response = $this->get(route('customer.wholesale.index'));

        $response->assertRedirect();
    }

    public function test_customer_without_application_gets_404(): void
    {
        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.index'));

        $response->assertNotFound();
    }

    public function test_customer_with_pending_application_sees_dashboard(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.index'));

        $response->assertOk();
        $response->assertSee('Application Timeline');
        $response->assertSee('Under Review');
        $response->assertSee('Test Co');
    }

    public function test_approved_customer_sees_group_details(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold Tier',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $admin = User::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin-test@example.com',
            'password' => bcrypt('password'),
        ]);

        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Gold Corp',
            'business_type' => 'distributor',
            'expected_volume' => '5000+',
            'status' => ApplicationStatusEnum::APPROVED,
            'assigned_group_id' => $group->id,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        CustomerGroupAssignment::query()->create([
            'customer_id' => $this->customer->id,
            'customer_group_id' => $group->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.index'));

        $response->assertOk();
        $response->assertSee('Approved');
        $response->assertSee('Gold Tier');
        $response->assertSee('20');
        $response->assertSee('Gold Corp');
    }

    public function test_rejected_customer_sees_rejection_reason(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Rejected Inc',
            'business_type' => 'other',
            'expected_volume' => '100-500',
            'status' => ApplicationStatusEnum::REJECTED,
            'reviewed_at' => now(),
            'rejection_reason' => 'Insufficient documentation provided',
        ]);

        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.index'));

        $response->assertOk();
        $response->assertSee('Rejected');
        $response->assertSee('Insufficient documentation provided');
        $response->assertDontSee('Wholesale Group');
    }

    public function test_other_customer_cannot_see_different_application(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Original Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $otherCustomer = Customer::query()->create([
            'name' => 'Other Customer',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'confirmed_at' => now(),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $this->actingAs($otherCustomer, 'customer');

        $response = $this->get(route('customer.wholesale.index'));

        $response->assertNotFound();
    }
}
