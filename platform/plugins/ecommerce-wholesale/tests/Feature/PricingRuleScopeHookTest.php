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
use Botble\EcommerceWholesale\Providers\HookServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class PricingRuleScopeHookTest extends BaseTestCase
{
    use RefreshDatabase;

    protected HookServiceProvider $hookProvider;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hookProvider = new HookServiceProvider($this->app);

        $this->product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    protected function simulateAdminContext(): void
    {
        $adminPrefix = config('core.base.general.admin_dir', 'admin');
        $request = Request::create("/{$adminPrefix}/products/{$this->product->id}", 'POST');
        $this->app->instance('request', $request);
    }

    protected function makeRequest(array $rules = []): Request
    {
        return new Request([
            'has_wholesale_pricing_rules' => '1',
            'wholesale_pricing_rules' => $rules,
        ]);
    }

    public function test_save_product_pricing_rules_sets_scope_product(): void
    {
        $this->simulateAdminContext();

        $rules = [
            [
                'id' => '',
                'min_quantity' => 5,
                'max_quantity' => '',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'customer_group_id' => '',
            ],
        ];

        $request = $this->makeRequest($rules);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        $savedRule = GroupPricingRule::query()
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertNotNull($savedRule);
        $this->assertEquals(PricingRuleScopeEnum::PRODUCT, $savedRule->scope->getValue());
    }

    public function test_save_product_pricing_rules_does_not_delete_category_scope_rules(): void
    {
        $category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $categoryRule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'category_id' => $category->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateAdminContext();

        // Save empty product rules
        $request = $this->makeRequest([]);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        $this->assertDatabaseHas('ws_group_pricing_rules', [
            'id' => $categoryRule->id,
            'scope' => PricingRuleScopeEnum::CATEGORY,
        ]);
    }

    public function test_save_product_pricing_rules_does_not_delete_global_scope_rules(): void
    {
        $globalRule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateAdminContext();

        $request = $this->makeRequest([]);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        $this->assertDatabaseHas('ws_group_pricing_rules', [
            'id' => $globalRule->id,
            'scope' => PricingRuleScopeEnum::GLOBAL,
        ]);
    }

    public function test_save_only_deletes_product_scope_rules_for_this_product(): void
    {
        $otherProduct = Product::query()->create([
            'name' => 'Other Product',
            'price' => 200,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Product rule for other product
        $otherProductRule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $otherProduct->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Product rule for current product (will be deleted)
        $currentProductRule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $this->product->id,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateAdminContext();

        $request = $this->makeRequest([]);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        // Other product's rule should still exist
        $this->assertDatabaseHas('ws_group_pricing_rules', ['id' => $otherProductRule->id]);

        // Current product's rule should be deleted
        $this->assertDatabaseMissing('ws_group_pricing_rules', ['id' => $currentProductRule->id]);
    }

    public function test_new_product_rules_get_scope_product(): void
    {
        $this->simulateAdminContext();

        $rules = [
            [
                'id' => '',
                'min_quantity' => 5,
                'max_quantity' => 9,
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'customer_group_id' => '',
            ],
            [
                'id' => '',
                'min_quantity' => 10,
                'max_quantity' => '',
                'discount_type' => 'fixed',
                'discount_value' => 20,
                'customer_group_id' => '',
            ],
        ];

        $request = $this->makeRequest($rules);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        $savedRules = GroupPricingRule::query()
            ->where('product_id', $this->product->id)
            ->get();

        $this->assertCount(2, $savedRules);

        foreach ($savedRules as $rule) {
            $this->assertEquals(PricingRuleScopeEnum::PRODUCT, $rule->scope->getValue());
        }
    }

    public function test_update_existing_product_rule_preserves_scope(): void
    {
        $existingRule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateAdminContext();

        $rules = [
            [
                'id' => $existingRule->id,
                'min_quantity' => 10,
                'max_quantity' => '',
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'customer_group_id' => '',
            ],
        ];

        $request = $this->makeRequest($rules);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        $existingRule->refresh();

        $this->assertEquals(PricingRuleScopeEnum::PRODUCT, $existingRule->scope->getValue());
        $this->assertEquals(10, $existingRule->min_quantity);
        $this->assertEqualsWithDelta(25.0, $existingRule->discount_value, 0.01);
    }

    public function test_existing_rules_query_only_finds_product_scope(): void
    {
        // Create product-scope rule
        $productRule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Create global-scope rule that happens to match the product_id column
        $globalRule = GroupPricingRule::query()->create([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'product_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateAdminContext();

        // Re-save with no rules — should only delete product-scope rules
        $request = $this->makeRequest([]);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        // Product rule should be deleted
        $this->assertDatabaseMissing('ws_group_pricing_rules', ['id' => $productRule->id]);

        // Global rule should remain
        $this->assertDatabaseHas('ws_group_pricing_rules', ['id' => $globalRule->id]);
    }
}
