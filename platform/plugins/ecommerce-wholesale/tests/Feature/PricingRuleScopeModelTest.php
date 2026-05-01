<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PricingRuleScopeModelTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_scope_defaults_to_product(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $rule = GroupPricingRule::query()->create([
            'product_id' => $product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $rule->refresh();

        $this->assertEquals(PricingRuleScopeEnum::PRODUCT, $rule->scope->getValue());
    }

    public function test_scope_cast_to_enum(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $rule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'product_id' => $product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $rule->refresh();

        $this->assertInstanceOf(PricingRuleScopeEnum::class, $rule->scope);
        $this->assertEquals(PricingRuleScopeEnum::CATEGORY, $rule->scope->getValue());
    }

    public function test_category_relationship(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $rule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $category->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertNotNull($rule->category);
        $this->assertEquals($category->id, $rule->category->id);
        $this->assertEquals('Electronics', $rule->category->name);
    }

    public function test_category_relationship_returns_null_when_not_set(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $rule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertNull($rule->category);
    }

    public function test_product_id_nullable_for_global_scope(): void
    {
        $rule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'product_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertNotNull($rule->id);
        $this->assertNull($rule->product_id);
        $this->assertEquals(PricingRuleScopeEnum::GLOBAL, $rule->scope->getValue());
    }

    public function test_product_id_nullable_for_category_scope(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $rule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'product_id' => null,
            'category_id' => $category->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertNotNull($rule->id);
        $this->assertNull($rule->product_id);
        $this->assertEquals($category->id, $rule->category_id);
    }

    public function test_scope_and_category_id_in_fillable(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $rule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $category->id,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::FIXED,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $rule->refresh();

        $this->assertEquals(PricingRuleScopeEnum::CATEGORY, $rule->scope->getValue());
        $this->assertEquals($category->id, $rule->category_id);
    }

    public function test_global_scope_rule_without_product_or_category(): void
    {
        $rule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'product_id' => null,
            'category_id' => null,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $rule->refresh();

        $this->assertEquals(PricingRuleScopeEnum::GLOBAL, $rule->scope->getValue());
        $this->assertNull($rule->product_id);
        $this->assertNull($rule->category_id);
        $this->assertNull($rule->product);
        $this->assertNull($rule->category);
    }
}
