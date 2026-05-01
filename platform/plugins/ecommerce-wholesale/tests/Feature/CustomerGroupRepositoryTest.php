<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Repositories\Interfaces\CustomerGroupInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerGroupRepositoryTest extends BaseTestCase
{
    use RefreshDatabase;

    protected CustomerGroupInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(CustomerGroupInterface::class);
    }

    public function test_get_active_groups_returns_published_only(): void
    {
        CustomerGroup::query()->create([
            'name' => 'Published',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        CustomerGroup::query()->create([
            'name' => 'Draft',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'priority' => 2,
            'status' => CustomerGroupStatusEnum::DRAFT,
        ]);

        $active = $this->repository->getActiveGroups();

        $this->assertCount(1, $active);
        $this->assertEquals('Published', $active->first()->name);
    }

    public function test_get_active_groups_ordered_by_priority(): void
    {
        CustomerGroup::query()->create([
            'name' => 'Low Priority',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'priority' => 5,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        CustomerGroup::query()->create([
            'name' => 'High Priority',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $active = $this->repository->getActiveGroups();

        $this->assertCount(2, $active);
        $this->assertEquals('High Priority', $active->first()->name);
        $this->assertEquals('Low Priority', $active->last()->name);
    }

    public function test_get_active_groups_returns_empty_when_none(): void
    {
        $active = $this->repository->getActiveGroups();

        $this->assertTrue($active->isEmpty());
    }

    public function test_get_groups_for_customer(): void
    {
        $gold = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'priority' => 2,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $silver = CustomerGroup::query()->create([
            'name' => 'Silver',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'priority' => 3,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer->wholesaleGroups()->attach($gold->id, [
            'assigned_at' => now(),
        ]);

        $groups = $this->repository->getGroupsForCustomer($customer->id);

        $this->assertCount(1, $groups);
        $this->assertEquals('Gold', $groups->first()->name);
    }

    public function test_get_groups_for_customer_excludes_draft(): void
    {
        $draft = CustomerGroup::query()->create([
            'name' => 'Draft',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'status' => CustomerGroupStatusEnum::DRAFT,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer->wholesaleGroups()->attach($draft->id, [
            'assigned_at' => now(),
        ]);

        $groups = $this->repository->getGroupsForCustomer($customer->id);

        $this->assertTrue($groups->isEmpty());
    }

    public function test_get_groups_for_customer_returns_empty_for_unassigned(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Regular',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $groups = $this->repository->getGroupsForCustomer($customer->id);

        $this->assertTrue($groups->isEmpty());
    }
}
