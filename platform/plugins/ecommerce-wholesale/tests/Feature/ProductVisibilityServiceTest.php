<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\ProductVisibilityEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\ProductVisibility;
use Botble\EcommerceWholesale\Providers\HookServiceProvider;
use Botble\EcommerceWholesale\Services\ProductVisibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class ProductVisibilityServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected ProductVisibilityService $service;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ProductVisibilityService();
        $this->product = Product::query()->create([
            'name' => 'Wholesale Product',
            'price' => 200,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    public function test_set_public_visibility(): void
    {
        $visibility = $this->service->setVisibility($this->product, ProductVisibilityEnum::PRODUCT_PUBLIC());

        $this->assertDatabaseHas('ws_product_visibility', [
            'product_id' => $this->product->id,
            'visibility_type' => ProductVisibilityEnum::PRODUCT_PUBLIC,
        ]);

        $visibility->refresh();
        $this->assertEquals(ProductVisibilityEnum::PRODUCT_PUBLIC, $visibility->visibility_type->getValue());
    }

    public function test_set_wholesale_only_visibility(): void
    {
        $visibility = $this->service->setVisibility($this->product, ProductVisibilityEnum::WHOLESALE_ONLY());

        $this->assertDatabaseHas('ws_product_visibility', [
            'product_id' => $this->product->id,
            'visibility_type' => ProductVisibilityEnum::WHOLESALE_ONLY,
        ]);

        $visibility->refresh();
        $this->assertEquals(ProductVisibilityEnum::WHOLESALE_ONLY, $visibility->visibility_type->getValue());
    }

    public function test_set_specific_groups_visibility(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $visibility = $this->service->setVisibility(
            $this->product,
            ProductVisibilityEnum::SPECIFIC_GROUPS(),
            [$group->id]
        );

        $this->assertDatabaseHas('ws_product_visibility', [
            'product_id' => $this->product->id,
            'visibility_type' => ProductVisibilityEnum::SPECIFIC_GROUPS,
        ]);

        $this->assertDatabaseHas('ws_product_group_access', [
            'product_id' => $this->product->id,
            'customer_group_id' => $group->id,
        ]);

        $visibility->refresh();
        $this->assertEquals(ProductVisibilityEnum::SPECIFIC_GROUPS, $visibility->visibility_type->getValue());
    }

    public function test_remove_visibility(): void
    {
        $this->service->setVisibility($this->product, ProductVisibilityEnum::WHOLESALE_ONLY());
        $this->service->removeVisibility($this->product);

        $this->assertDatabaseMissing('ws_product_visibility', [
            'product_id' => $this->product->id,
        ]);
    }

    public function test_public_product_visible_to_everyone(): void
    {
        $this->service->setVisibility($this->product, ProductVisibilityEnum::PRODUCT_PUBLIC());

        $this->assertTrue($this->service->canCustomerViewProduct($this->product, null));

        $customer = Customer::query()->create([
            'name' => 'Any Customer',
            'email' => 'any@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertTrue($this->service->canCustomerViewProduct($this->product, $customer));
    }

    public function test_product_without_visibility_record_visible_to_everyone(): void
    {
        $this->assertTrue($this->service->canCustomerViewProduct($this->product, null));
    }

    public function test_wholesale_only_not_visible_to_guest(): void
    {
        $this->service->setVisibility($this->product, ProductVisibilityEnum::WHOLESALE_ONLY());

        $this->assertFalse($this->service->canCustomerViewProduct($this->product, null));
    }

    public function test_wholesale_only_not_visible_to_non_wholesale_customer(): void
    {
        $this->service->setVisibility($this->product, ProductVisibilityEnum::WHOLESALE_ONLY());

        $customer = Customer::query()->create([
            'name' => 'Regular Customer',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertFalse($this->service->canCustomerViewProduct($this->product, $customer));
    }

    public function test_wholesale_only_visible_to_wholesale_customer(): void
    {
        $this->service->setVisibility($this->product, ProductVisibilityEnum::WHOLESALE_ONLY());

        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
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

        $this->assertTrue($this->service->canCustomerViewProduct($this->product, $customer));
    }

    public function test_specific_groups_visible_only_to_allowed_group(): void
    {
        $allowedGroup = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $otherGroup = CustomerGroup::query()->create([
            'name' => 'Bronze',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 5,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->service->setVisibility(
            $this->product,
            ProductVisibilityEnum::SPECIFIC_GROUPS(),
            [$allowedGroup->id]
        );

        $allowedCustomer = Customer::query()->create([
            'name' => 'Gold Customer',
            'email' => 'gold@example.com',
            'password' => bcrypt('password'),
        ]);
        $allowedCustomer->wholesaleGroups()->attach($allowedGroup->id, [
            'assigned_at' => now(),
        ]);

        $otherCustomer = Customer::query()->create([
            'name' => 'Bronze Customer',
            'email' => 'bronze@example.com',
            'password' => bcrypt('password'),
        ]);
        $otherCustomer->wholesaleGroups()->attach($otherGroup->id, [
            'assigned_at' => now(),
        ]);

        $this->assertTrue($this->service->canCustomerViewProduct($this->product, $allowedCustomer));
        $this->assertFalse($this->service->canCustomerViewProduct($this->product, $otherCustomer));
    }

    public function test_specific_groups_not_visible_to_guest(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->service->setVisibility(
            $this->product,
            ProductVisibilityEnum::SPECIFIC_GROUPS(),
            [$group->id]
        );

        $this->assertFalse($this->service->canCustomerViewProduct($this->product, null));
    }

    public function test_updating_visibility_replaces_group_access(): void
    {
        $group1 = CustomerGroup::query()->create([
            'name' => 'Group A',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $group2 = CustomerGroup::query()->create([
            'name' => 'Group B',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->service->setVisibility(
            $this->product,
            ProductVisibilityEnum::SPECIFIC_GROUPS(),
            [$group1->id]
        );

        $this->assertDatabaseHas('ws_product_group_access', [
            'product_id' => $this->product->id,
            'customer_group_id' => $group1->id,
        ]);

        $this->service->setVisibility(
            $this->product,
            ProductVisibilityEnum::SPECIFIC_GROUPS(),
            [$group2->id]
        );

        $this->assertDatabaseMissing('ws_product_group_access', [
            'product_id' => $this->product->id,
            'customer_group_id' => $group1->id,
        ]);
        $this->assertDatabaseHas('ws_product_group_access', [
            'product_id' => $this->product->id,
            'customer_group_id' => $group2->id,
        ]);
    }

    public function test_model_is_public_returns_true_for_public_visibility(): void
    {
        $visibility = $this->service->setVisibility($this->product, ProductVisibilityEnum::PRODUCT_PUBLIC());
        $visibility->refresh();

        $this->assertTrue($visibility->isPublic());
        $this->assertFalse($visibility->isWholesaleOnly());
        $this->assertFalse($visibility->isSpecificGroups());
    }

    public function test_model_is_wholesale_only_returns_true_for_wholesale_visibility(): void
    {
        $visibility = $this->service->setVisibility($this->product, ProductVisibilityEnum::WHOLESALE_ONLY());
        $visibility->refresh();

        $this->assertFalse($visibility->isPublic());
        $this->assertTrue($visibility->isWholesaleOnly());
        $this->assertFalse($visibility->isSpecificGroups());
    }

    public function test_model_is_specific_groups_returns_true_for_specific_groups_visibility(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $visibility = $this->service->setVisibility(
            $this->product,
            ProductVisibilityEnum::SPECIFIC_GROUPS(),
            [$group->id]
        );
        $visibility->refresh();

        $this->assertFalse($visibility->isPublic());
        $this->assertFalse($visibility->isWholesaleOnly());
        $this->assertTrue($visibility->isSpecificGroups());
    }

    public function test_enum_can_be_constructed_from_string_value(): void
    {
        $enum = (new ProductVisibilityEnum())->make('wholesale_only');

        $this->assertEquals('wholesale_only', $enum->getValue());

        $enum2 = (new ProductVisibilityEnum())->make('specific_groups');

        $this->assertEquals('specific_groups', $enum2->getValue());
    }

    public function test_save_hook_persists_wholesale_only_visibility(): void
    {
        $hookProvider = app(HookServiceProvider::class, ['app' => app()]);

        $request = Request::create('/', 'POST', [
            'wholesale_visibility' => 'wholesale_only',
            'wholesale_group_access' => [],
        ]);

        $hookProvider->saveProductVisibility('', $request, $this->product);

        $this->assertDatabaseHas('ws_product_visibility', [
            'product_id' => $this->product->id,
            'visibility_type' => 'wholesale_only',
        ]);

        $visibility = ProductVisibility::query()
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertNotNull($visibility);
        $this->assertTrue($visibility->isWholesaleOnly());
    }

    public function test_save_hook_persists_specific_groups_visibility_with_groups(): void
    {
        $hookProvider = app(HookServiceProvider::class, ['app' => app()]);

        $group = CustomerGroup::query()->create([
            'name' => 'VIP',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $request = Request::create('/', 'POST', [
            'wholesale_visibility' => 'specific_groups',
            'wholesale_group_access' => [$group->id],
        ]);

        $hookProvider->saveProductVisibility('', $request, $this->product);

        $this->assertDatabaseHas('ws_product_visibility', [
            'product_id' => $this->product->id,
            'visibility_type' => 'specific_groups',
        ]);

        $this->assertDatabaseHas('ws_product_group_access', [
            'product_id' => $this->product->id,
            'customer_group_id' => $group->id,
        ]);
    }

    public function test_save_hook_removes_visibility_for_public(): void
    {
        $this->service->setVisibility($this->product, ProductVisibilityEnum::WHOLESALE_ONLY());

        $this->assertDatabaseHas('ws_product_visibility', [
            'product_id' => $this->product->id,
        ]);

        $hookProvider = app(HookServiceProvider::class, ['app' => app()]);

        $request = Request::create('/', 'POST', [
            'wholesale_visibility' => 'public',
        ]);

        $hookProvider->saveProductVisibility('', $request, $this->product);

        $this->assertDatabaseMissing('ws_product_visibility', [
            'product_id' => $this->product->id,
        ]);
    }

    public function test_save_hook_defaults_to_public_when_no_visibility_in_request(): void
    {
        $this->service->setVisibility($this->product, ProductVisibilityEnum::WHOLESALE_ONLY());

        $hookProvider = app(HookServiceProvider::class, ['app' => app()]);

        $request = Request::create('/', 'POST');

        $hookProvider->saveProductVisibility('', $request, $this->product);

        $this->assertDatabaseMissing('ws_product_visibility', [
            'product_id' => $this->product->id,
        ]);
    }

    public function test_changing_from_specific_groups_to_wholesale_only_clears_group_access(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->service->setVisibility(
            $this->product,
            ProductVisibilityEnum::SPECIFIC_GROUPS(),
            [$group->id]
        );

        $this->assertDatabaseHas('ws_product_group_access', [
            'product_id' => $this->product->id,
            'customer_group_id' => $group->id,
        ]);

        $this->service->setVisibility($this->product, ProductVisibilityEnum::WHOLESALE_ONLY());

        $this->assertDatabaseMissing('ws_product_group_access', [
            'product_id' => $this->product->id,
        ]);

        $visibility = $this->service->getVisibility($this->product);
        $this->assertTrue($visibility->isWholesaleOnly());
    }
}
