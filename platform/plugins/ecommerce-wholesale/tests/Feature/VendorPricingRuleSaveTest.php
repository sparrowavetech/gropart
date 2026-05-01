<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Botble\EcommerceWholesale\Providers\HookServiceProvider;
use Botble\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class VendorPricingRuleSaveTest extends BaseTestCase
{
    use RefreshDatabase;

    protected HookServiceProvider $hookProvider;

    protected Product $product;

    protected Customer $vendor;

    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->forgetAll();
        $this->activatePlugins();
        setting()->set('wholesale_enable_vendor_dashboard', '1')->save();

        $this->hookProvider = new HookServiceProvider($this->app);

        $this->vendor = Customer::query()->create([
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $this->vendor->forceFill([
            'confirmed_at' => now(),
            'is_vendor' => true,
            'vendor_verified_at' => now(),
        ])->save();

        $this->vendor->refresh();

        $this->store = Store::query()->create([
            'name' => 'Test Store',
            'customer_id' => $this->vendor->id,
            'status' => 'published',
        ]);

        $this->product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->product->forceFill(['store_id' => $this->store->id])->save();
        $this->product->refresh();
    }

    protected function makeRequest(array $rules = [], array $extra = []): Request
    {
        $data = array_merge([
            'has_wholesale_pricing_rules' => '1',
            'wholesale_pricing_rules' => $rules,
        ], $extra);

        return new Request($data);
    }

    protected function simulateVendorContext(): void
    {
        $vendorDir = config('plugins.marketplace.general.vendor_panel_dir', 'vendor');

        // Simulate being in vendor panel by making a request with vendor prefix
        $this->actingAs($this->vendor, 'customer');

        // Set the request to have the vendor URL segment
        $request = Request::create("/{$vendorDir}/products/{$this->product->id}", 'POST');
        $this->app->instance('request', $request);
    }

    protected function simulateAdminContext(): void
    {
        $adminPrefix = config('core.base.general.admin_dir', 'admin');
        $request = Request::create("/{$adminPrefix}/products/{$this->product->id}", 'POST');
        $this->app->instance('request', $request);
    }

    public function test_vendor_save_sets_store_id_on_pricing_rules(): void
    {
        $this->simulateVendorContext();

        $rules = [
            [
                'id' => '',
                'min_quantity' => 10,
                'max_quantity' => '',
                'discount_type' => 'percentage',
                'discount_value' => 15,
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
        $this->assertEquals($this->store->id, $savedRule->store_id);
        $this->assertEquals(10, $savedRule->min_quantity);
        $this->assertEqualsWithDelta(15.0, $savedRule->discount_value, 0.01);
    }

    public function test_admin_save_keeps_store_id_null(): void
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
        $this->assertNull($savedRule->store_id);
    }

    public function test_vendor_save_does_not_delete_admin_rules(): void
    {
        // Admin creates a global rule first
        $adminRule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'store_id' => null,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateVendorContext();

        // Vendor saves their own rules (empty list = deletes vendor rules only)
        $request = $this->makeRequest([]);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        // Admin rule should still exist
        $this->assertDatabaseHas('ws_group_pricing_rules', [
            'id' => $adminRule->id,
            'store_id' => null,
        ]);
    }

    public function test_admin_save_does_not_delete_vendor_rules(): void
    {
        // Vendor creates a store-scoped rule first
        $vendorRule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateAdminContext();

        // Admin saves their own rules (empty list = deletes admin rules only)
        $request = $this->makeRequest([]);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        // Vendor rule should still exist
        $this->assertDatabaseHas('ws_group_pricing_rules', [
            'id' => $vendorRule->id,
            'store_id' => $this->store->id,
        ]);
    }

    public function test_vendor_can_update_own_existing_rule(): void
    {
        $existingRule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateVendorContext();

        $rules = [
            [
                'id' => $existingRule->id,
                'min_quantity' => 20,
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

        $updatedRule = GroupPricingRule::query()->find($existingRule->id);

        $this->assertNotNull($updatedRule);
        $this->assertEquals(20, $updatedRule->min_quantity);
        $this->assertEqualsWithDelta(25.0, $updatedRule->discount_value, 0.01);
        $this->assertEquals($this->store->id, $updatedRule->store_id);
    }

    public function test_vendor_cannot_update_other_vendor_rule(): void
    {
        $otherVendor = Customer::query()->create([
            'name' => 'Other Vendor',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $otherVendor->forceFill([
            'confirmed_at' => now(),
            'is_vendor' => true,
            'vendor_verified_at' => now(),
        ])->save();

        $otherStore = Store::query()->create([
            'name' => 'Other Store',
            'customer_id' => $otherVendor->id,
            'status' => 'published',
        ]);

        $otherVendorRule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'store_id' => $otherStore->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateVendorContext();

        // Attempt to update the other vendor's rule by ID
        $rules = [
            [
                'id' => $otherVendorRule->id,
                'min_quantity' => 5,
                'max_quantity' => '',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'customer_group_id' => '',
            ],
        ];

        $request = $this->makeRequest($rules);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        // Other vendor's rule should NOT be modified
        $otherVendorRule->refresh();
        $this->assertEquals($otherStore->id, $otherVendorRule->store_id);
        $this->assertEquals(10, $otherVendorRule->min_quantity);
        $this->assertEqualsWithDelta(30.0, $otherVendorRule->discount_value, 0.01);

        // A new rule should have been created for the current vendor instead
        $newRule = GroupPricingRule::query()
            ->where('product_id', $this->product->id)
            ->where('store_id', $this->store->id)
            ->first();

        $this->assertNotNull($newRule);
        $this->assertEquals(5, $newRule->min_quantity);
        $this->assertEqualsWithDelta(50.0, $newRule->discount_value, 0.01);
    }

    public function test_vendor_save_skipped_when_feature_disabled(): void
    {
        setting()->set('wholesale_enable_vendor_dashboard', '0')->save();

        $this->simulateVendorContext();

        $rules = [
            [
                'id' => '',
                'min_quantity' => 10,
                'max_quantity' => '',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'customer_group_id' => '',
            ],
        ];

        $request = $this->makeRequest($rules);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        $this->assertDatabaseMissing('ws_group_pricing_rules', [
            'product_id' => $this->product->id,
        ]);
    }

    public function test_save_skipped_for_non_product_model(): void
    {
        $request = $this->makeRequest([
            [
                'id' => '',
                'min_quantity' => 10,
                'max_quantity' => '',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'customer_group_id' => '',
            ],
        ]);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Customer',
            $request,
            $this->vendor
        );

        $this->assertDatabaseMissing('ws_group_pricing_rules', [
            'product_id' => $this->product->id,
        ]);
    }

    public function test_save_skipped_when_no_pricing_rules_flag(): void
    {
        $this->simulateAdminContext();

        $request = new Request([
            'wholesale_pricing_rules' => [
                [
                    'id' => '',
                    'min_quantity' => 10,
                    'max_quantity' => '',
                    'discount_type' => 'percentage',
                    'discount_value' => 15,
                    'customer_group_id' => '',
                ],
            ],
        ]);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        $this->assertDatabaseMissing('ws_group_pricing_rules', [
            'product_id' => $this->product->id,
        ]);
    }

    public function test_vendor_deletes_own_removed_rules_only(): void
    {
        // Admin global rule
        $adminRule = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'store_id' => null,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Vendor rule to keep
        $vendorRuleKeep = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Vendor rule to delete (not included in submission)
        $vendorRuleDelete = GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'min_quantity' => 50,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->simulateVendorContext();

        // Only submit the "keep" rule
        $rules = [
            [
                'id' => $vendorRuleKeep->id,
                'min_quantity' => 10,
                'max_quantity' => '',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'customer_group_id' => '',
            ],
        ];

        $request = $this->makeRequest($rules);

        $this->hookProvider->saveProductPricingRules(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        // Admin rule should still exist
        $this->assertDatabaseHas('ws_group_pricing_rules', ['id' => $adminRule->id]);

        // Kept vendor rule should still exist
        $this->assertDatabaseHas('ws_group_pricing_rules', ['id' => $vendorRuleKeep->id]);

        // Removed vendor rule should be deleted
        $this->assertDatabaseMissing('ws_group_pricing_rules', ['id' => $vendorRuleDelete->id]);
    }

    public function test_vendor_multiple_rules_saved_with_correct_store_id(): void
    {
        $this->simulateVendorContext();

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
                'max_quantity' => 49,
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'customer_group_id' => '',
            ],
            [
                'id' => '',
                'min_quantity' => 50,
                'max_quantity' => '',
                'discount_type' => 'fixed_price',
                'discount_value' => 75,
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
            ->where('store_id', $this->store->id)
            ->orderBy('min_quantity')
            ->get();

        $this->assertCount(3, $savedRules);

        $this->assertEquals(5, $savedRules[0]->min_quantity);
        $this->assertEquals(9, $savedRules[0]->max_quantity);
        $this->assertEqualsWithDelta(10.0, $savedRules[0]->discount_value, 0.01);

        $this->assertEquals(10, $savedRules[1]->min_quantity);
        $this->assertEquals(49, $savedRules[1]->max_quantity);
        $this->assertEqualsWithDelta(20.0, $savedRules[1]->discount_value, 0.01);

        $this->assertEquals(50, $savedRules[2]->min_quantity);
        $this->assertNull($savedRules[2]->max_quantity);
        $this->assertEqualsWithDelta(75.0, $savedRules[2]->discount_value, 0.01);
    }

    public function test_save_visibility_skipped_in_vendor_context(): void
    {
        $this->simulateVendorContext();

        $request = new Request([
            'wholesale_visibility' => 'wholesale_only',
            'wholesale_group_access' => [],
        ]);

        $this->hookProvider->saveProductVisibility(
            'Botble\Ecommerce\Models\Product',
            $request,
            $this->product
        );

        // No visibility record should be created for vendor context
        $this->assertDatabaseMissing('ws_product_visibility', [
            'product_id' => $this->product->id,
        ]);
    }
}
