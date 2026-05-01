<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Models\ProductMOQ;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductMOQModelTest extends BaseTestCase
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

    public function test_valid_quantity_at_minimum(): void
    {
        $moq = ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'quantity_increment' => 1,
        ]);

        $this->assertTrue($moq->isValidQuantity(10));
    }

    public function test_invalid_quantity_below_minimum(): void
    {
        $moq = ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'quantity_increment' => 1,
        ]);

        $this->assertFalse($moq->isValidQuantity(5));
    }

    public function test_valid_quantity_with_increment(): void
    {
        $moq = ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'quantity_increment' => 5,
        ]);

        $this->assertTrue($moq->isValidQuantity(10));
        $this->assertTrue($moq->isValidQuantity(15));
        $this->assertTrue($moq->isValidQuantity(20));
    }

    public function test_invalid_quantity_with_increment(): void
    {
        $moq = ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'quantity_increment' => 5,
        ]);

        $this->assertFalse($moq->isValidQuantity(12));
        $this->assertFalse($moq->isValidQuantity(13));
    }

    public function test_next_valid_quantity_below_min(): void
    {
        $moq = ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'quantity_increment' => 5,
        ]);

        $this->assertEquals(10, $moq->getNextValidQuantity(3));
    }

    public function test_next_valid_quantity_rounds_up_to_increment(): void
    {
        $moq = ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'quantity_increment' => 5,
        ]);

        $this->assertEquals(15, $moq->getNextValidQuantity(12));
        $this->assertEquals(20, $moq->getNextValidQuantity(16));
    }

    public function test_next_valid_quantity_already_valid(): void
    {
        $moq = ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 10,
            'quantity_increment' => 5,
        ]);

        $this->assertEquals(15, $moq->getNextValidQuantity(15));
    }

    public function test_increment_of_one_accepts_any_quantity_above_min(): void
    {
        $moq = ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'min_quantity' => 5,
            'quantity_increment' => 1,
        ]);

        $this->assertTrue($moq->isValidQuantity(5));
        $this->assertTrue($moq->isValidQuantity(7));
        $this->assertTrue($moq->isValidQuantity(999));
    }
}
