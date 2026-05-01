<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\ProductVisibilityEnum;
use Botble\EcommerceWholesale\Models\ProductVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductVisibilityModelTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_public_visibility(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $visibility = ProductVisibility::query()->create([
            'product_id' => $product->id,
            'visibility_type' => ProductVisibilityEnum::PRODUCT_PUBLIC,
        ]);

        $this->assertDatabaseHas('ws_product_visibility', [
            'product_id' => $product->id,
            'visibility_type' => ProductVisibilityEnum::PRODUCT_PUBLIC,
        ]);

        $visibility->refresh();
        $this->assertEquals(ProductVisibilityEnum::PRODUCT_PUBLIC, $visibility->visibility_type->getValue());
    }

    public function test_wholesale_only_visibility(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $visibility = ProductVisibility::query()->create([
            'product_id' => $product->id,
            'visibility_type' => ProductVisibilityEnum::WHOLESALE_ONLY,
        ]);

        $this->assertDatabaseHas('ws_product_visibility', [
            'product_id' => $product->id,
            'visibility_type' => ProductVisibilityEnum::WHOLESALE_ONLY,
        ]);

        $visibility->refresh();
        $this->assertEquals(ProductVisibilityEnum::WHOLESALE_ONLY, $visibility->visibility_type->getValue());
    }

    public function test_specific_groups_visibility(): void
    {
        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $visibility = ProductVisibility::query()->create([
            'product_id' => $product->id,
            'visibility_type' => ProductVisibilityEnum::SPECIFIC_GROUPS,
        ]);

        $this->assertDatabaseHas('ws_product_visibility', [
            'product_id' => $product->id,
            'visibility_type' => ProductVisibilityEnum::SPECIFIC_GROUPS,
        ]);

        $visibility->refresh();
        $this->assertEquals(ProductVisibilityEnum::SPECIFIC_GROUPS, $visibility->visibility_type->getValue());
    }
}
