<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CustomerGroupFeatureTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_can_create_customer_group(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'description' => 'Gold tier',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'min_order_value' => 1000,
            'min_order_quantity' => 20,
            'priority' => 2,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertDatabaseHas('ws_customer_groups', [
            'name' => 'Gold',
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);
        $this->assertEquals(DiscountTypeEnum::PERCENTAGE, $group->discount_type->getValue());
    }

    public function test_can_assign_customer_to_group(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Silver',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        DB::table('ws_customer_group_assignments')->insert([
            'customer_id' => $customer->id,
            'customer_group_id' => $group->id,
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('ws_customer_group_assignments', [
            'customer_id' => $customer->id,
            'customer_group_id' => $group->id,
        ]);
    }

    public function test_customer_group_has_customers_relationship(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Bronze',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
        ]);

        $group->customers()->attach($customer->id, [
            'assigned_at' => now(),
        ]);

        $this->assertEquals(1, $group->customers()->count());
        $this->assertEquals($customer->id, $group->customers()->first()->id);
    }

    public function test_draft_groups_are_separate_from_published(): void
    {
        CustomerGroup::query()->create([
            'name' => 'Active Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        CustomerGroup::query()->create([
            'name' => 'Draft Group',
            'discount_type' => DiscountTypeEnum::FIXED,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::DRAFT,
        ]);

        $published = CustomerGroup::query()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->count();

        $draft = CustomerGroup::query()
            ->where('status', CustomerGroupStatusEnum::DRAFT)
            ->count();

        $this->assertEquals(1, $published);
        $this->assertEquals(1, $draft);
    }
}
