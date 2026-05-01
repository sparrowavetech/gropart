<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Botble\EcommerceWholesale\Services\PricingRuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PricingRuleServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected PricingRuleService $service;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PricingRuleService();
        $this->product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    public function test_get_tiered_prices_returns_all_tiers(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'max_quantity' => 9,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $tiers = $this->service->getTieredPricesForProduct($this->product);

        $this->assertCount(2, $tiers);
        $this->assertEqualsWithDelta(90.0, $tiers[0]['price'], 0.01);
        $this->assertEqualsWithDelta(80.0, $tiers[1]['price'], 0.01);
    }

    public function test_get_tiered_prices_filters_by_group(): void
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

        // Rule for Gold
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => $group->id,
            'min_quantity' => 5,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Rule for Silver
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => $otherGroup->id,
            'min_quantity' => 5,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Global rule (null group)
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $tiers = $this->service->getTieredPricesForProduct($this->product, $group);

        // Should return Gold rule + global rule, not Silver rule
        $this->assertCount(2, $tiers);
    }

    public function test_get_best_price_for_quantity(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'max_quantity' => 9,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 15);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(75.0, $result['final_price'], 0.01);
        $this->assertEqualsWithDelta(25.0, $result['discount'], 0.01);
    }

    public function test_no_matching_rule_returns_null(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 50,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 5);

        $this->assertNull($result);
    }

    public function test_draft_rules_are_excluded(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 50,
            'status' => CustomerGroupStatusEnum::DRAFT,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 10);

        $this->assertNull($result);
    }

    public function test_best_price_picks_lowest_among_multiple_rules(): void
    {
        // Global rule: 10% off
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 5,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Another global rule: fixed $30 off
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 5,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::FIXED,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 10);

        $this->assertNotNull($result);
        // $30 fixed discount gives $70, 10% gives $90, so $70 is best
        $this->assertEqualsWithDelta(70.0, $result['final_price'], 0.01);
    }

    public function test_get_rules_for_product(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'max_quantity' => 9,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $rules = $this->service->getRulesForProduct($this->product->id);

        $this->assertCount(1, $rules);
        $this->assertEquals($this->product->id, $rules->first()->product_id);
    }

    public function test_get_tiered_prices_for_customer_with_group_ids(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Group-specific rule
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => $group->id,
            'min_quantity' => 5,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Global rule
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $tiers = $this->service->getTieredPricesForCustomer($this->product, [$group->id]);

        // Should include the group-specific rule + global rule
        $this->assertCount(2, $tiers);
    }

    public function test_get_tiered_prices_for_customer_without_group_returns_all(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 20,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $tiers = $this->service->getTieredPricesForCustomer($this->product, []);

        $this->assertCount(2, $tiers);
    }

    public function test_get_best_price_with_store_id(): void
    {
        // Store-specific rule
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'store_id' => 42,
            'min_quantity' => 1,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Global rule
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'store_id' => null,
            'min_quantity' => 1,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 5, [], 42);

        $this->assertNotNull($result);
        // Store rule 30% off = $70 beats global 10% off = $90
        $this->assertEqualsWithDelta(70.0, $result['final_price'], 0.01);
    }

    public function test_get_best_price_with_custom_base_price(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Use base price of 200 instead of product's $100
        $result = $this->service->getBestPriceForQuantity($this->product, 1, [], null, 200.0);

        $this->assertNotNull($result);
        $this->assertEqualsWithDelta(180.0, $result['final_price'], 0.01);
        $this->assertEqualsWithDelta(200.0, $result['original_price'], 0.01);
    }

    public function test_fixed_price_rule_type(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 60,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $result = $this->service->getBestPriceForQuantity($this->product, 1);

        $this->assertNotNull($result);
        // FIXED_PRICE sets the price to $60
        $this->assertEqualsWithDelta(60.0, $result['final_price'], 0.01);
    }
}
