<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerDashboardWholesaleCardTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'dashboard-test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_filter_returns_empty_when_no_application(): void
    {
        $result = apply_filters('ecommerce_customer_overview_extra', '', $this->customer);

        $this->assertEmpty(trim($result));
    }

    public function test_filter_renders_pending_status_card(): void
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

        $result = apply_filters('ecommerce_customer_overview_extra', '', $this->customer);

        $this->assertStringContainsString('Wholesale Account', $result);
        $this->assertStringContainsString('Under Review', $result);
        $this->assertStringContainsString('bg-warning-lt', $result);
    }

    public function test_filter_renders_approved_status_with_group(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold Tier',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::APPROVED,
            'assigned_group_id' => $group->id,
            'reviewed_at' => now(),
        ]);

        $result = apply_filters('ecommerce_customer_overview_extra', '', $this->customer);

        $this->assertStringContainsString('Approved', $result);
        $this->assertStringContainsString('bg-success-lt', $result);
        $this->assertStringContainsString('Gold Tier', $result);
        $this->assertStringContainsString('15% off', $result);
        $this->assertStringContainsString('View Details', $result);
    }

    public function test_filter_renders_rejected_status(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::REJECTED,
            'reviewed_at' => now(),
            'rejection_reason' => 'Incomplete documentation',
        ]);

        $result = apply_filters('ecommerce_customer_overview_extra', '', $this->customer);

        $this->assertStringContainsString('Rejected', $result);
        $this->assertStringContainsString('bg-danger-lt', $result);
    }

    public function test_filter_returns_empty_for_null_customer(): void
    {
        $result = apply_filters('ecommerce_customer_overview_extra', '', null);

        $this->assertEmpty(trim($result));
    }
}
