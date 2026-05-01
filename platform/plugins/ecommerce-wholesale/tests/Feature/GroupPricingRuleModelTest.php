<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupPricingRuleModelTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    public function test_applies_to_quantity_within_range(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'max_quantity' => 50,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertTrue($rule->appliesToQuantity(10));
        $this->assertTrue($rule->appliesToQuantity(25));
        $this->assertTrue($rule->appliesToQuantity(50));
    }

    public function test_does_not_apply_below_min_quantity(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'max_quantity' => 50,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertFalse($rule->appliesToQuantity(5));
        $this->assertFalse($rule->appliesToQuantity(9));
    }

    public function test_does_not_apply_above_max_quantity(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'max_quantity' => 50,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertFalse($rule->appliesToQuantity(51));
        $this->assertFalse($rule->appliesToQuantity(100));
    }

    public function test_applies_with_null_max_quantity(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertTrue($rule->appliesToQuantity(10));
        $this->assertTrue($rule->appliesToQuantity(1000));
    }

    public function test_percentage_discount_calculation(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(20.0, $rule->calculateDiscount(100), 0.01);
        $this->assertEqualsWithDelta(80.0, $rule->calculateFinalPrice(100), 0.01);
    }

    public function test_fixed_discount_calculation(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::FIXED,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(30.0, $rule->calculateDiscount(100), 0.01);
        $this->assertEqualsWithDelta(70.0, $rule->calculateFinalPrice(100), 0.01);
    }

    public function test_fixed_discount_capped_at_base_price(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::FIXED,
            'discount_value' => 200,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(100.0, $rule->calculateDiscount(100), 0.01);
        $this->assertEqualsWithDelta(0.0, $rule->calculateFinalPrice(100), 0.01);
    }

    public function test_fixed_price_discount_calculation(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 75,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(25.0, $rule->calculateDiscount(100), 0.01);
        $this->assertEqualsWithDelta(75.0, $rule->calculateFinalPrice(100), 0.01);
    }

    public function test_fixed_price_higher_than_base_gives_zero_discount(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 150,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(0.0, $rule->calculateDiscount(100), 0.01);
        $this->assertEqualsWithDelta(150.0, $rule->calculateFinalPrice(100), 0.01);
    }

    public function test_negative_base_price_treated_as_zero(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(0.0, $rule->calculateDiscount(-50), 0.01);
        $this->assertEqualsWithDelta(0.0, $rule->calculateFinalPrice(-50), 0.01);
    }

    public function test_quantity_range_attribute_with_max(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'max_quantity' => 50,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('10-50', $rule->quantity_range);
    }

    public function test_quantity_range_attribute_without_max(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 100,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEquals('100+', $rule->quantity_range);
    }

    public function test_percentage_discount_scales_with_price(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $rule = GroupPricingRule::query()->find($rule->id);

        $this->assertEqualsWithDelta(20.0, $rule->calculateDiscount(200), 0.01, '10% of 200 = 20');
        $this->assertEqualsWithDelta(180.0, $rule->calculateFinalPrice(200), 0.01);

        $this->assertEqualsWithDelta(50.0, $rule->calculateDiscount(500), 0.01, '10% of 500 = 50');
        $this->assertEqualsWithDelta(450.0, $rule->calculateFinalPrice(500), 0.01);
    }

    public function test_percentage_discount_not_treated_as_fixed_amount(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $rule = GroupPricingRule::query()->find($rule->id);

        $discount = $rule->calculateDiscount(200);

        $this->assertNotEquals(10.0, $discount, 'Percentage discount should not be treated as fixed amount');
        $this->assertEqualsWithDelta(20.0, $discount, 0.01, '10% of 200 should be 20, not 10');
    }

    public function test_enum_cast_preserves_discount_type_after_db_reload(): void
    {
        $rule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $reloaded = GroupPricingRule::query()->find($rule->id);

        $this->assertInstanceOf(PricingDiscountTypeEnum::class, $reloaded->discount_type);
        $this->assertEquals(PricingDiscountTypeEnum::PERCENTAGE, $reloaded->discount_type->getValue());
        $this->assertEqualsWithDelta(30.0, $reloaded->calculateDiscount(200), 0.01);
    }
}
