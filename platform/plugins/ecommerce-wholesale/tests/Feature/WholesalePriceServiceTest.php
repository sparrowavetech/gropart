<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Botble\EcommerceWholesale\Services\WholesalePriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WholesalePriceServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected WholesalePriceService $service;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WholesalePriceService::class);
        $this->product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        setting()->set('wholesale_enabled', '1')->save();
    }

    public function test_get_wholesale_price_returns_null_when_disabled(): void
    {
        setting()->set('wholesale_enabled', '0')->save();

        $result = $this->service->getWholesalePrice($this->product, 1);

        $this->assertNull($result);
    }

    public function test_get_wholesale_price_returns_null_for_non_wholesale_customer(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Regular',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $result = $this->service->getWholesalePrice($this->product, 1, $customer);

        $this->assertNull($result);
    }

    public function test_get_wholesale_price_with_pricing_rule(): void
    {
        $group = $this->createGroupWithCustomer();

        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getWholesalePrice($this->product, 5, $group['customer']);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(80.0, $result, 0.01);
    }

    public function test_get_wholesale_price_falls_back_to_group_discount(): void
    {
        $group = $this->createGroupWithCustomer(discountValue: 15);

        $result = $this->service->getWholesalePrice($this->product, 1, $group['customer']);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(85.0, $result, 0.01);
    }

    public function test_get_wholesale_price_returns_null_when_no_discount(): void
    {
        $group = $this->createGroupWithCustomer(discountValue: 0);

        $result = $this->service->getWholesalePrice($this->product, 1, $group['customer']);

        $this->assertNull($result);
    }

    public function test_calculate_price_with_quantity_applies_tiered_pricing(): void
    {
        $group = $this->createGroupWithCustomer();

        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->actingAs($group['customer'], 'customer');

        $price = $this->service->calculatePriceWithQuantity(100, $this->product, 15);

        $this->assertEqualsWithDelta(75.0, $price, 0.01);
    }

    public function test_calculate_price_returns_base_when_disabled(): void
    {
        setting()->set('wholesale_enabled', '0')->save();

        $price = $this->service->calculatePriceWithQuantity(100, $this->product, 10);

        $this->assertEqualsWithDelta(100.0, $price, 0.01);
    }

    public function test_guest_gets_default_group_pricing_when_enabled(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Default Guest',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        setting()->set('wholesale_enable_for_guests', '1')->save();
        setting()->set('wholesale_default_group', $group->id)->save();

        $result = $this->service->getWholesalePrice($this->product, 1);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(95.0, $result, 0.01);
    }

    public function test_guest_gets_null_when_guest_pricing_disabled(): void
    {
        setting()->set('wholesale_enable_for_guests', '0')->save();

        $result = $this->service->getWholesalePrice($this->product, 1);

        $this->assertNull($result);
    }

    public function test_guest_falls_back_to_first_group_when_no_default_set(): void
    {
        CustomerGroup::query()->create([
            'name' => 'Highest Priority',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        CustomerGroup::query()->create([
            'name' => 'Lower Priority',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'priority' => 5,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        setting()->set('wholesale_enable_for_guests', '1')->save();
        setting()->set('wholesale_default_group', null)->save();

        $result = $this->service->getWholesalePrice($this->product, 1);

        $this->assertNotNull($result);
        // Should use priority=1 group (10% discount)
        $this->assertEqualsWithDelta(90.0, $result, 0.01);
    }

    public function test_multiple_groups_picks_best_discount(): void
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
            'discount_value' => 25,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Multi-group',
            'email' => 'multi@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer->wholesaleGroups()->attach([
            $gold->id => ['assigned_at' => now()],
            $platinum->id => ['assigned_at' => now()],
        ]);

        $result = $this->service->getWholesalePrice($this->product, 1, $customer);

        $this->assertNotNull($result);
        // Platinum gives 25% off => $75 is best
        $this->assertEqualsWithDelta(75.0, $result, 0.01);
    }

    public function test_pricing_rule_beats_group_discount_when_better(): void
    {
        $group = $this->createGroupWithCustomer(discountValue: 10);

        // Pricing rule gives 30% off at qty 5+
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getWholesalePrice($this->product, 10, $group['customer']);

        $this->assertNotNull($result);
        // Rule: 30% off = $70, Group: 10% off = $90. Rule wins.
        $this->assertEqualsWithDelta(70.0, $result, 0.01);
    }

    public function test_get_wholesale_price_returns_null_when_qty_below_min_tier(): void
    {
        $group = $this->createGroupWithCustomer(discountValue: 15);

        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'max_quantity' => 9,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getWholesalePrice($this->product, 1, $group['customer']);

        $this->assertNull($result);
    }

    public function test_non_group_customer_uses_guest_default_when_enabled(): void
    {
        $defaultGroup = CustomerGroup::query()->create([
            'name' => 'Default',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        setting()->set('wholesale_enable_for_guests', '1')->save();
        setting()->set('wholesale_default_group', $defaultGroup->id)->save();

        $customer = Customer::query()->create([
            'name' => 'No Group',
            'email' => 'nogroup@example.com',
            'password' => bcrypt('password'),
        ]);

        // Customer has no wholesale groups - falls through to guest logic
        $result = $this->service->getWholesalePrice($this->product, 1, $customer);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(95.0, $result, 0.01);
    }

    protected function createGroupWithCustomer(float $discountValue = 10): array
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Test Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => $discountValue,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Wholesale Buyer',
            'email' => 'wholesale@example.com',
            'password' => bcrypt('password'),
        ]);

        $customer->wholesaleGroups()->attach($group->id, [
            'assigned_at' => now(),
        ]);

        return ['group' => $group, 'customer' => $customer];
    }
}
