<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Botble\EcommerceWholesale\Services\ApplicationApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplicationApprovalServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected ApplicationApprovalService $service;

    protected CustomerGroup $group;

    protected User $reviewer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ApplicationApprovalService();

        $this->group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->reviewer = User::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_approve_application_with_existing_customer(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $application = WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $result = $this->service->approve($application, $this->group, $this->reviewer);

        $this->assertEquals($customer->id, $result->id);

        $application->refresh();
        $this->assertEquals(ApplicationStatusEnum::APPROVED, $application->status->getValue());
        $this->assertEquals($this->group->id, $application->assigned_group_id);
        $this->assertEquals($this->reviewer->id, $application->reviewed_by);
        $this->assertNotNull($application->reviewed_at);

        $this->assertDatabaseHas('ws_customer_group_assignments', [
            'customer_id' => $customer->id,
            'customer_group_id' => $this->group->id,
        ]);
    }

    public function test_approve_application_creates_customer_if_not_exists(): void
    {
        $application = WholesaleApplication::query()->create([
            'customer_id' => null,
            'email' => 'new@example.com',
            'name' => 'New Applicant',
            'phone' => '9876543210',
            'company_name' => 'New Co',
            'business_type' => 'distributor',
            'expected_volume' => '5000-10000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $result = $this->service->approve($application, $this->group, $this->reviewer);

        $this->assertNotNull($result->id);
        $this->assertEquals('new@example.com', $result->email);
        $this->assertEquals('New Applicant', $result->name);

        $this->assertDatabaseHas('ws_customer_group_assignments', [
            'customer_id' => $result->id,
            'customer_group_id' => $this->group->id,
        ]);
    }

    public function test_reject_application(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $application = WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $result = $this->service->reject($application, 'Insufficient documentation', $this->reviewer);

        $result->refresh();
        $this->assertEquals(ApplicationStatusEnum::REJECTED, $result->status->getValue());
        $this->assertEquals('Insufficient documentation', $result->rejection_reason);
        $this->assertEquals($this->reviewer->id, $result->reviewed_by);
        $this->assertNotNull($result->reviewed_at);
    }

    public function test_approving_does_not_duplicate_group_assignment(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Pre-assign
        $customer->wholesaleGroups()->attach($this->group->id, [
            'assigned_at' => now(),
            'assigned_by' => $this->reviewer->id,
        ]);

        $application = WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $this->service->approve($application, $this->group, $this->reviewer);

        // syncWithoutDetaching should not duplicate
        $this->assertEquals(1, $customer->wholesaleGroups()->count());
    }
}
