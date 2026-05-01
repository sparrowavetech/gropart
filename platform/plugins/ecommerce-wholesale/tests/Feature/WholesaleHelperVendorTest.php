<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class WholesaleHelperVendorTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->forgetAll();

        // Re-activate plugins after clearing settings
        $this->activatePlugins();
    }

    public function test_is_vendor_dashboard_enabled_defaults_to_false(): void
    {
        $this->assertFalse(WholesaleHelper::isVendorDashboardEnabled());
    }

    public function test_is_vendor_dashboard_enabled_respects_setting(): void
    {
        setting()->set('wholesale_enable_vendor_dashboard', '1')->save();

        $this->assertTrue(WholesaleHelper::isVendorDashboardEnabled());
    }

    public function test_is_in_vendor_panel_returns_false_by_default(): void
    {
        $this->assertFalse(WholesaleHelper::isInVendorPanel());
    }

    public function test_is_in_vendor_panel_detects_vendor_url_segment(): void
    {
        $vendorDir = config('plugins.marketplace.general.vendor_panel_dir', 'vendor');

        // Simulate being in vendor panel by setting request with vendor URL
        $request = Request::create("/{$vendorDir}/wholesale-products", 'GET');
        $this->app->instance('request', $request);

        $this->assertTrue(WholesaleHelper::isInVendorPanel());
    }

    public function test_is_in_vendor_panel_returns_false_for_admin_url(): void
    {
        $request = Request::create('/admin/products', 'GET');
        $this->app->instance('request', $request);

        $this->assertFalse(WholesaleHelper::isInVendorPanel());
    }

    public function test_is_in_vendor_panel_returns_false_for_frontend_url(): void
    {
        $request = Request::create('/products/test-product', 'GET');
        $this->app->instance('request', $request);

        $this->assertFalse(WholesaleHelper::isInVendorPanel());
    }

    public function test_get_vendor_store_id_returns_null_when_not_logged_in(): void
    {
        $this->assertNull(WholesaleHelper::getVendorStoreId());
    }

    public function test_get_vendor_store_id_returns_null_for_customer_without_store(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $customer->forceFill(['confirmed_at' => now()])->save();

        $this->actingAs($customer, 'customer');

        $this->assertNull(WholesaleHelper::getVendorStoreId());
    }

    public function test_get_vendor_store_id_returns_store_id_for_vendor(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Vendor Customer',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $customer->forceFill([
            'confirmed_at' => now(),
            'is_vendor' => true,
            'vendor_verified_at' => now(),
        ])->save();

        $store = Store::query()->create([
            'name' => 'Vendor Store',
            'customer_id' => $customer->id,
            'status' => 'published',
        ]);

        $this->actingAs($customer, 'customer');

        $this->assertEquals($store->id, WholesaleHelper::getVendorStoreId());
    }
}
