<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\CustomerGroupAssignment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerGroupAssignmentModelTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_assignment_without_expiry_is_active(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Test Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $assignment = CustomerGroupAssignment::query()->create([
            'customer_id' => $customer->id,
            'customer_group_id' => $group->id,
            'assigned_at' => now(),
            'expires_at' => null,
        ]);

        $this->assertFalse($assignment->isExpired());
        $this->assertTrue($assignment->isActive());
    }

    public function test_assignment_with_future_expiry_is_active(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Test Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $assignment = CustomerGroupAssignment::query()->create([
            'customer_id' => $customer->id,
            'customer_group_id' => $group->id,
            'assigned_at' => now(),
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->assertFalse($assignment->isExpired());
        $this->assertTrue($assignment->isActive());
    }

    public function test_assignment_with_past_expiry_is_expired(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Test Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $assignment = CustomerGroupAssignment::query()->create([
            'customer_id' => $customer->id,
            'customer_group_id' => $group->id,
            'assigned_at' => now(),
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->assertTrue($assignment->isExpired());
        $this->assertFalse($assignment->isActive());
    }
}
