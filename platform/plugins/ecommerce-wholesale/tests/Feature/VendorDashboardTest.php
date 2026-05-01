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
use Botble\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VendorDashboardTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Customer $vendor;

    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->forgetAll();
        $this->activatePlugins();
        setting()->set('wholesale_enable_vendor_dashboard', '1')->save();
        setting()->set('marketplace_verify_vendor', '0')->save();

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
    }

    protected function vendorUrl(string $path = ''): string
    {
        $prefix = config('plugins.marketplace.general.vendor_panel_dir', 'vendor');

        return "/{$prefix}/{$path}";
    }

    protected function createProduct(array $attributes = []): Product
    {
        $storeId = $attributes['store_id'] ?? null;
        unset($attributes['store_id']);

        $product = Product::query()->create($attributes);

        if ($storeId) {
            $product->forceFill(['store_id' => $storeId])->save();
        }

        return $product->refresh();
    }

    public function test_guest_is_redirected_from_vendor_wholesale_page(): void
    {
        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertRedirect();
    }

    public function test_non_vendor_customer_is_redirected(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Regular Customer',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $customer->forceFill(['confirmed_at' => now()])->save();

        $this->actingAs($customer, 'customer');

        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertRedirect();
    }

    public function test_vendor_can_access_wholesale_products_page(): void
    {
        $this->actingAs($this->vendor, 'customer');

        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertOk();
        $response->assertSee(trans('plugins/ecommerce-wholesale::wholesale.wholesale_products'));
    }

    public function test_vendor_sees_404_when_feature_disabled(): void
    {
        setting()->set('wholesale_enable_vendor_dashboard', '0')->save();

        $this->actingAs($this->vendor, 'customer');

        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertNotFound();
    }

    public function test_vendor_sees_own_products_with_pricing_rules(): void
    {
        $product = $this->createProduct([
            'name' => 'Vendor Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
            'store_id' => $this->store->id,
        ]);

        GroupPricingRule::query()->create([
            'product_id' => $product->id,
            'store_id' => $this->store->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->actingAs($this->vendor, 'customer');

        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertOk();
        $response->assertSee('Vendor Product');
        $response->assertSee('15%');
    }

    public function test_vendor_does_not_see_other_vendor_products(): void
    {
        $otherVendor = Customer::query()->create([
            'name' => 'Other Vendor',
            'email' => 'other-vendor@example.com',
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

        $otherProduct = $this->createProduct([
            'name' => 'Other Vendor Product',
            'price' => 200,
            'status' => BaseStatusEnum::PUBLISHED,
            'store_id' => $otherStore->id,
        ]);

        GroupPricingRule::query()->create([
            'product_id' => $otherProduct->id,
            'store_id' => $otherStore->id,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->actingAs($this->vendor, 'customer');

        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertOk();
        $response->assertDontSee('Other Vendor Product');
    }

    public function test_vendor_does_not_see_admin_global_rules(): void
    {
        $product = $this->createProduct([
            'name' => 'Shared Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
            'store_id' => $this->store->id,
        ]);

        // Admin-created global rule (store_id = null)
        GroupPricingRule::query()->create([
            'product_id' => $product->id,
            'store_id' => null,
            'min_quantity' => 5,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->actingAs($this->vendor, 'customer');

        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertOk();
        // Product should NOT appear because it has no vendor-scoped rules
        $response->assertDontSee('Shared Product');
    }

    public function test_vendor_sees_empty_state_when_no_products(): void
    {
        $this->actingAs($this->vendor, 'customer');

        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertOk();
        $response->assertSee(trans('plugins/ecommerce-wholesale::wholesale.products.no_products'));
    }

    public function test_vendor_product_edit_link_points_to_vendor_route(): void
    {
        $product = $this->createProduct([
            'name' => 'Editable Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
            'store_id' => $this->store->id,
        ]);

        GroupPricingRule::query()->create([
            'product_id' => $product->id,
            'store_id' => $this->store->id,
            'min_quantity' => 10,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->actingAs($this->vendor, 'customer');

        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertOk();
        $response->assertSee(route('marketplace.vendor.products.edit', $product->id));
    }

    public function test_vendor_without_store_gets_404(): void
    {
        $vendorNoStore = Customer::query()->create([
            'name' => 'Storeless Vendor',
            'email' => 'storeless@example.com',
            'password' => bcrypt('password'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $vendorNoStore->forceFill([
            'confirmed_at' => now(),
            'is_vendor' => true,
            'vendor_verified_at' => now(),
        ])->save();

        $this->actingAs($vendorNoStore, 'customer');

        $response = $this->get($this->vendorUrl('wholesale-products'));

        $response->assertNotFound();
    }
}
