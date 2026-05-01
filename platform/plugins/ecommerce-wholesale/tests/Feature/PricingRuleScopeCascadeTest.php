<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Botble\EcommerceWholesale\Services\PricingRuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PricingRuleScopeCascadeTest extends BaseTestCase
{
    use RefreshDatabase;

    protected PricingRuleService $service;

    protected Product $product;

    protected ProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PricingRuleService();

        $this->category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->product->categories()->attach($this->category->id);
    }

    public function test_product_scope_rules_take_priority_over_category(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $this->category->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 50,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNotNull($result);
        // Product rule: 10% off = $90 (not category's 50% = $50)
        $this->assertEqualsWithDelta(90.0, $result['final_price'], 0.01);
    }

    public function test_product_scope_rules_take_priority_over_global(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 80,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(85.0, $result['final_price'], 0.01);
    }

    public function test_category_scope_rules_take_priority_over_global(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $this->category->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 80,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNotNull($result);
        // Category 20% = $80 (not global 80% = $20)
        $this->assertEqualsWithDelta(80.0, $result['final_price'], 0.01);
    }

    public function test_global_scope_rules_used_when_no_product_or_category_rules(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(75.0, $result['final_price'], 0.01);
    }

    public function test_no_rules_at_any_scope_returns_null(): void
    {
        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNull($result);
    }

    public function test_category_rules_apply_via_parent_category(): void
    {
        $parentCategory = ProductCategory::query()->create([
            'name' => 'All Products',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->category->forceFill(['parent_id' => $parentCategory->id])->save();

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $parentCategory->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 12,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(88.0, $result['final_price'], 0.01);
    }

    public function test_best_category_rule_selected_across_direct_and_parent(): void
    {
        $parentCategory = ProductCategory::query()->create([
            'name' => 'All Products',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->category->forceFill(['parent_id' => $parentCategory->id])->save();

        // Direct category rule: 10% off
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $this->category->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Parent category rule: 20% off (better)
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $parentCategory->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNotNull($result);
        // Parent rule 20% off = $80 is best
        $this->assertEqualsWithDelta(80.0, $result['final_price'], 0.01);
    }

    public function test_category_rules_not_matched_for_unrelated_category(): void
    {
        $otherCategory = ProductCategory::query()->create([
            'name' => 'Clothing',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $otherCategory->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 50,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNull($result);
    }

    public function test_resolve_rules_respects_customer_group_filter(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $otherGroup = CustomerGroup::query()->create([
            'name' => 'Silver',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Global rule for Gold
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'customer_group_id' => $group->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Global rule for Silver (should be excluded)
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'customer_group_id' => $otherGroup->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 40,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1, [$group->id]);

        $this->assertNotNull($result);
        // Only Gold group rule 20% = $80
        $this->assertEqualsWithDelta(80.0, $result['final_price'], 0.01);
    }

    public function test_null_group_rules_included_alongside_group_specific(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Group-specific: 10% off
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'customer_group_id' => $group->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // All groups (null): 25% off (better)
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1, [$group->id]);

        $this->assertNotNull($result);
        // null-group rule 25% = $75 beats group-specific 10% = $90
        $this->assertEqualsWithDelta(75.0, $result['final_price'], 0.01);
    }

    public function test_draft_rules_excluded_from_cascade(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 50,
            'status' => CustomerGroupStatusEnum::DRAFT,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNotNull($result);
        // Draft product rule skipped, falls through to global 10% = $90
        $this->assertEqualsWithDelta(90.0, $result['final_price'], 0.01);
    }

    public function test_has_rules_for_product_returns_true_with_product_scope(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertTrue($this->service->hasRulesForProduct($this->product));
    }

    public function test_has_rules_for_product_returns_true_with_category_scope(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $this->category->id,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertTrue($this->service->hasRulesForProduct($this->product));
    }

    public function test_has_rules_for_product_returns_true_with_global_scope(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertTrue($this->service->hasRulesForProduct($this->product));
    }

    public function test_has_rules_for_product_returns_false_when_no_rules(): void
    {
        $this->assertFalse($this->service->hasRulesForProduct($this->product));
    }

    public function test_get_tiered_prices_for_customer_uses_cascade(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $this->category->id,
            'min_quantity' => 5,
            'max_quantity' => 9,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $this->category->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $tiers = $this->service->getTieredPricesForCustomer($this->product, []);

        $this->assertCount(2, $tiers);
        $this->assertEqualsWithDelta(90.0, $tiers[0]['price'], 0.01);
        $this->assertEqualsWithDelta(80.0, $tiers[1]['price'], 0.01);
    }

    public function test_get_tiered_prices_for_product_uses_cascade(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 1,
            'max_quantity' => 9,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $tiers = $this->service->getTieredPricesForProduct($this->product);

        $this->assertCount(2, $tiers);
        $this->assertEqualsWithDelta(95.0, $tiers[0]['price'], 0.01);
        $this->assertEqualsWithDelta(85.0, $tiers[1]['price'], 0.01);
    }

    public function test_get_rules_for_product_only_returns_product_scope(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $this->category->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $rules = $this->service->getRulesForProduct($this->product->id);

        $this->assertCount(1, $rules);
        $this->assertEquals(PricingRuleScopeEnum::PRODUCT, $rules->first()->scope->getValue());
    }

    public function test_product_without_categories_skips_to_global(): void
    {
        $productWithoutCategory = Product::query()->create([
            'name' => 'No Category Product',
            'price' => 200,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $this->category->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 50,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($productWithoutCategory, 1);

        $this->assertNotNull($result);
        // No categories, skips category scope, uses global 10% off = $180
        $this->assertEqualsWithDelta(180.0, $result['final_price'], 0.01);
    }

    public function test_quantity_filter_works_with_category_scope(): void
    {
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $this->category->id,
            'min_quantity' => 50,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Quantity 5 doesn't meet min_quantity=50
        $result = $this->service->getBestPriceForQuantity($this->product, 5);

        $this->assertNull($result);

        // Quantity 50 meets min_quantity=50
        $result = $this->service->getBestPriceForQuantity($this->product, 50);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(70.0, $result['final_price'], 0.01);
    }

    public function test_store_id_filter_works_with_global_scope(): void
    {
        // Store-specific global rule
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'store_id' => 42,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Non-store global rule
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'store_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1, [], 42);

        $this->assertNotNull($result);
        // Store rule 30% = $70 beats non-store 10% = $90
        $this->assertEqualsWithDelta(70.0, $result['final_price'], 0.01);
    }

    public function test_product_with_multiple_categories_matches_any(): void
    {
        $otherCategory = ProductCategory::query()->create([
            'name' => 'Sale Items',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->product->categories()->attach($otherCategory->id);

        // Rule for the other category only
        GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $otherCategory->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(85.0, $result['final_price'], 0.01);
    }
}
