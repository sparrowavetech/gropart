<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WholesaleHelperTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->forgetAll();
    }

    public function test_is_enabled_defaults_to_true(): void
    {
        $this->assertTrue(WholesaleHelper::isEnabled());
    }

    public function test_is_enabled_respects_setting(): void
    {
        setting()->set('wholesale_enabled', '0')->save();

        $this->assertFalse(WholesaleHelper::isEnabled());
    }

    public function test_is_approval_required_defaults_to_true(): void
    {
        $this->assertTrue(WholesaleHelper::isApprovalRequired());
    }

    public function test_show_prices_to_guests_defaults_to_false(): void
    {
        $this->assertFalse(WholesaleHelper::showPricesToGuests());
    }

    public function test_show_prices_to_guests_respects_setting(): void
    {
        setting()->set('wholesale_show_prices_to_guests', '1')->save();

        $this->assertTrue(WholesaleHelper::showPricesToGuests());
    }

    public function test_is_enabled_for_guests_defaults_to_false(): void
    {
        $this->assertFalse(WholesaleHelper::isEnabledForGuests());
    }

    public function test_is_enabled_for_guests_respects_setting(): void
    {
        setting()->set('wholesale_enable_for_guests', '1')->save();

        $this->assertTrue(WholesaleHelper::isEnabledForGuests());
    }

    public function test_get_default_group_id_returns_null_when_not_set(): void
    {
        $this->assertNull(WholesaleHelper::getDefaultGroupId());
    }

    public function test_get_default_group_id_returns_int(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Default',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        setting()->set('wholesale_default_group', $group->id)->save();

        $this->assertEquals($group->id, WholesaleHelper::getDefaultGroupId());
    }

    public function test_is_wholesale_customer_returns_false_when_disabled(): void
    {
        setting()->set('wholesale_enabled', '0')->save();

        $customer = Customer::query()->create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertFalse(WholesaleHelper::isWholesaleCustomer($customer));
    }

    public function test_is_wholesale_customer_returns_false_for_null(): void
    {
        $this->assertFalse(WholesaleHelper::isWholesaleCustomer(null));
    }

    public function test_is_wholesale_customer_returns_false_without_group(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Regular',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertFalse(WholesaleHelper::isWholesaleCustomer($customer));
    }

    public function test_is_wholesale_customer_returns_true_with_published_group(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Wholesale',
            'email' => 'wholesale@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer->wholesaleGroups()->attach($group->id, [
            'assigned_at' => now(),
        ]);

        $this->assertTrue(WholesaleHelper::isWholesaleCustomer($customer));
    }

    public function test_is_wholesale_customer_returns_false_with_draft_group(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Draft Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::DRAFT,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer->wholesaleGroups()->attach($group->id, [
            'assigned_at' => now(),
        ]);

        $this->assertFalse(WholesaleHelper::isWholesaleCustomer($customer));
    }

    public function test_get_customer_groups_returns_empty_for_null(): void
    {
        $this->assertTrue(WholesaleHelper::getCustomerGroups(null)->isEmpty());
    }

    public function test_get_customer_groups_returns_published_groups_ordered(): void
    {
        $gold = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'priority' => 2,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $platinum = CustomerGroup::query()->create([
            'name' => 'Platinum',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $draft = CustomerGroup::query()->create([
            'name' => 'Draft',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'priority' => 3,
            'status' => CustomerGroupStatusEnum::DRAFT,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Multi',
            'email' => 'multi@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer->wholesaleGroups()->attach([
            $gold->id => ['assigned_at' => now()],
            $platinum->id => ['assigned_at' => now()],
            $draft->id => ['assigned_at' => now()],
        ]);

        $groups = WholesaleHelper::getCustomerGroups($customer);

        // Only published, ordered by priority
        $this->assertCount(2, $groups);
        $this->assertEquals('Platinum', $groups->first()->name);
        $this->assertEquals('Gold', $groups->last()->name);
    }

    public function test_show_pricing_table_defaults_to_true(): void
    {
        $this->assertTrue(WholesaleHelper::showPricingTable());
    }

    public function test_allow_multiple_groups(): void
    {
        setting()->set('wholesale_allow_multiple_groups', '1')->save();

        $this->assertTrue(WholesaleHelper::allowMultipleGroups());
    }
}
