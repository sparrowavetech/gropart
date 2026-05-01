<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\ProductMOQ;
use Botble\EcommerceWholesale\Services\MOQValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MOQValidationServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected MOQValidationService $service;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MOQValidationService();
        $this->product = Product::query()->create([
            'name' => 'Bulk Product',
            'price' => 50,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    public function test_default_moq_for_non_wholesale_customer(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Regular',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $moq = $this->service->getProductMOQ($this->product, $customer);

        $this->assertEquals(1, $moq['min_quantity']);
        $this->assertEquals(1, $moq['quantity_increment']);
        $this->assertEquals('default', $moq['source']);
    }

    public function test_default_moq_for_guest(): void
    {
        $moq = $this->service->getProductMOQ($this->product, null);

        $this->assertEquals(1, $moq['min_quantity']);
        $this->assertEquals(1, $moq['quantity_increment']);
        $this->assertEquals('default', $moq['source']);
    }

    public function test_product_moq_for_wholesale_customer(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'min_order_quantity' => 20,
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

        ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 10,
            'quantity_increment' => 5,
        ]);

        $moq = $this->service->getProductMOQ($this->product, $customer);

        $this->assertEquals(10, $moq['min_quantity']);
        $this->assertEquals(5, $moq['quantity_increment']);
        $this->assertEquals('product', $moq['source']);
    }

    public function test_group_moq_when_no_product_moq(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'min_order_quantity' => 25,
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

        $moq = $this->service->getProductMOQ($this->product, $customer);

        $this->assertEquals(25, $moq['min_quantity']);
        $this->assertEquals('group', $moq['source']);
    }

    public function test_validate_quantity_below_minimum(): void
    {
        $result = $this->service->validateQuantity($this->product, 0, null);

        // Default MOQ is 1, so 0 is invalid
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_validate_quantity_at_minimum(): void
    {
        $result = $this->service->validateQuantity($this->product, 1, null);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_min_order_value_for_non_wholesale(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Regular',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $minValue = $this->service->getMinOrderValue($customer);

        $this->assertEqualsWithDelta(0.0, $minValue, 0.01);
    }

    public function test_min_order_value_for_wholesale_customer(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'min_order_value' => 500,
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

        $minValue = $this->service->getMinOrderValue($customer);

        $this->assertEqualsWithDelta(500.0, $minValue, 0.01);
    }

    public function test_validate_quantity_with_increment_violation(): void
    {
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

        ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 10,
            'quantity_increment' => 5,
        ]);

        // 12 is above min (10) but not on increment (10, 15, 20...)
        $result = $this->service->validateQuantity($this->product, 12, $customer);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_validate_quantity_passes_on_valid_increment(): void
    {
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

        ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 10,
            'quantity_increment' => 5,
        ]);

        $result = $this->service->validateQuantity($this->product, 15, $customer);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_group_specific_moq_takes_priority_over_default(): void
    {
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

        // Default MOQ (null group)
        ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 5,
            'quantity_increment' => 1,
        ]);

        // Group-specific MOQ
        ProductMOQ::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => $group->id,
            'min_quantity' => 20,
            'quantity_increment' => 10,
        ]);

        $moq = $this->service->getProductMOQ($this->product, $customer);

        // Group-specific MOQ should win (orderByRaw prioritizes non-null)
        $this->assertEquals(20, $moq['min_quantity']);
        $this->assertEquals(10, $moq['quantity_increment']);
        $this->assertEquals('product', $moq['source']);
    }
}
