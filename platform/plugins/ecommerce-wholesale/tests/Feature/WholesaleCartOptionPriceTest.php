<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Botble\EcommerceWholesale\Services\WholesalePriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WholesaleCartOptionPriceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::instance('cart')->destroy();
        setting()->set('wholesale_enabled', '1')->save();
    }

    protected function tearDown(): void
    {
        Cart::instance('cart')->destroy();

        parent::tearDown();
    }

    protected function makeProductOptionData(float $affectPrice, int $affectType = 0): array
    {
        return [
            'optionCartValue' => [
                1 => [
                    [
                        'option_value' => 'Warranty 2 Year',
                        'affect_price' => $affectPrice,
                        'affect_type' => $affectType,
                        'option_type' => 'select',
                    ],
                ],
            ],
            'optionInfo' => [
                1 => 'Warranty',
            ],
        ];
    }

    protected function makeMultiOptionData(array $options): array
    {
        $optionCartValue = [];
        $optionInfo = [];

        foreach ($options as $index => $option) {
            $key = $index + 1;
            $optionCartValue[$key] = [
                [
                    'option_value' => $option['value'],
                    'affect_price' => $option['affect_price'],
                    'affect_type' => $option['affect_type'] ?? 0,
                    'option_type' => $option['option_type'] ?? 'select',
                ],
            ];
            $optionInfo[$key] = $option['name'];
        }

        return [
            'optionCartValue' => $optionCartValue,
            'optionInfo' => $optionInfo,
        ];
    }

    protected function createWholesaleCustomer(float $discountValue = 10): array
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Wholesale Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => $discountValue,
            'priority' => 1,
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

        return ['group' => $group, 'customer' => $customer];
    }

    /**
     * Trigger applyWholesalePricesToCart() by calling rawSubTotal
     * which fires the ecommerce_cart_raw_subtotal filter.
     */
    protected function triggerWholesaleCartPricing(): void
    {
        Cart::instance('cart')->rawSubTotal();
    }

    public function test_wholesale_cart_update_preserves_fixed_option_price(): void
    {
        $data = $this->createWholesaleCustomer(20);
        $this->actingAs($data['customer'], 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(10.00);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            100.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        // Group discount: $20 off $100 = $80, option: +$10 = $90
        $updatedItem = Cart::instance('cart')->content()->first();

        $this->assertNotNull($updatedItem);
        $this->assertEquals(90.00, $updatedItem->price);
    }

    public function test_wholesale_cart_update_preserves_percentage_option_price(): void
    {
        // Use addQuietly + manual wholesale trigger to isolate from cross-sale interference
        $data = $this->createWholesaleCustomer(10);
        $this->actingAs($data['customer'], 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 200.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // 15% of base price as option surcharge
        $productOptions = $this->makeProductOptionData(15, 1);

        Cart::instance('cart')->addQuietly(
            $product->id,
            $product->name,
            1,
            200.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        // Verify initial price: $200 + 15% = $230
        $item = Cart::instance('cart')->content()->first();
        $this->assertEquals(230.00, $item->price);

        // Trigger wholesale pricing
        $this->triggerWholesaleCartPricing();

        // Wholesale base price from service, then option re-applied
        $service = app(WholesalePriceService::class);
        $freshProduct = Product::query()->find($product->id);
        $wholesaleBase = $service->getWholesalePrice($freshProduct, 1, $data['customer']);
        $this->assertNotNull($wholesaleBase);

        $expectedWithOptions = Cart::instance('cart')->getPriceByOptions($wholesaleBase, $productOptions);

        $updatedItem = Cart::instance('cart')->content()->first();
        $this->assertNotNull($updatedItem);
        $this->assertEquals($expectedWithOptions, $updatedItem->price);
        $this->assertLessThan(230.00, $updatedItem->price);
    }

    public function test_wholesale_cart_update_preserves_multiple_options(): void
    {
        $data = $this->createWholesaleCustomer(20);
        $this->actingAs($data['customer'], 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeMultiOptionData([
            ['name' => 'Warranty', 'value' => '2 Year', 'affect_price' => 10.00],
            ['name' => 'Color', 'value' => 'Gold', 'affect_price' => 5.00],
        ]);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            100.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        // Group discount: $20 off $100 = $80, options: +$10 +$5 = $95
        $updatedItem = Cart::instance('cart')->content()->first();

        $this->assertNotNull($updatedItem);
        $this->assertEquals(95.00, $updatedItem->price);
    }

    public function test_wholesale_cart_update_without_options_still_works(): void
    {
        $data = $this->createWholesaleCustomer(20);
        $this->actingAs($data['customer'], 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            100.00,
            [
                'taxRate' => 0,
            ]
        );

        // Group discount: $20 off $100 = $80, no options
        $updatedItem = Cart::instance('cart')->content()->first();

        $this->assertNotNull($updatedItem);
        $this->assertEquals(80.00, $updatedItem->price);
    }

    public function test_wholesale_bulk_apply_preserves_option_prices(): void
    {
        $data = $this->createWholesaleCustomer(10);
        $this->actingAs($data['customer'], 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(10.00);

        // Add item without triggering events (simulating pre-existing cart)
        Cart::instance('cart')->addQuietly(
            $product->id,
            $product->name,
            2,
            100.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        // Verify initial price includes options: $100 + $10 = $110
        $item = Cart::instance('cart')->content()->first();
        $this->assertEquals(110.00, $item->price);

        // Trigger wholesale pricing via rawSubTotal filter
        $this->triggerWholesaleCartPricing();

        // Wholesale applies group discount, then re-applies options
        $service = app(WholesalePriceService::class);
        $freshProduct = Product::query()->find($product->id);
        $wholesaleBase = $service->getWholesalePrice($freshProduct, 2, $data['customer']);
        $this->assertNotNull($wholesaleBase);

        $expectedWithOptions = Cart::instance('cart')->getPriceByOptions($wholesaleBase, $productOptions);

        $updatedItem = Cart::instance('cart')->content()->first();
        $this->assertNotNull($updatedItem);
        $this->assertEquals($expectedWithOptions, $updatedItem->price);
        $this->assertLessThan(110.00, $updatedItem->price);
    }

    public function test_wholesale_multiple_items_with_different_options(): void
    {
        // Use addQuietly to avoid cross-sale service re-pricing previous items
        $data = $this->createWholesaleCustomer(20);
        $this->actingAs($data['customer'], 'customer');

        $product1 = Product::query()->create([
            'name' => 'Product A',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $product2 = Product::query()->create([
            'name' => 'Product B',
            'price' => 50.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $options1 = $this->makeProductOptionData(10.00);
        $options2 = $this->makeProductOptionData(5.00);

        Cart::instance('cart')->addQuietly(
            $product1->id,
            $product1->name,
            1,
            100.00,
            [
                'options' => $options1,
                'taxRate' => 0,
            ]
        );

        Cart::instance('cart')->addQuietly(
            $product2->id,
            $product2->name,
            1,
            50.00,
            [
                'options' => $options2,
                'taxRate' => 0,
            ]
        );

        // Verify initial prices include options
        $items = Cart::instance('cart')->content();
        $initialPrices = $items->pluck('price')->sort()->values()->toArray();
        $this->assertEquals([55.00, 110.00], $initialPrices);

        // Trigger wholesale pricing
        $this->triggerWholesaleCartPricing();

        // Both items should have wholesale discount applied with options preserved
        $updatedItems = Cart::instance('cart')->content();
        $updatedPrices = $updatedItems->pluck('price')->sort()->values()->toArray();

        // Verify both prices decreased from initial
        $this->assertLessThan(55.00, $updatedPrices[0]);
        $this->assertLessThan(110.00, $updatedPrices[1]);

        // Verify options are still included (prices should be greater than wholesale base)
        $service = app(WholesalePriceService::class);
        $product1Fresh = Product::query()->find($product1->id);
        $product2Fresh = Product::query()->find($product2->id);
        $wholesale1 = $service->getWholesalePrice($product1Fresh, 1, $data['customer']);
        $wholesale2 = $service->getWholesalePrice($product2Fresh, 1, $data['customer']);

        $this->assertNotNull($wholesale1);
        $this->assertNotNull($wholesale2);

        // Each price should be wholesale base + option surcharge
        $expected1 = Cart::instance('cart')->getPriceByOptions($wholesale1, $options1);
        $expected2 = Cart::instance('cart')->getPriceByOptions($wholesale2, $options2);
        $expectedPrices = [$expected2, $expected1];
        sort($expectedPrices);

        $this->assertEquals($expectedPrices, $updatedPrices);
    }

    public function test_wholesale_with_pricing_rule_preserves_option_price(): void
    {
        $data = $this->createWholesaleCustomer(5);
        $this->actingAs($data['customer'], 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Pricing rule: 30% off at qty 1+
        GroupPricingRule::query()->create([
            'product_id' => $product->id,
            'min_quantity' => 1,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(10.00);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            100.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        // Pricing rule: 30% off $100 = $70, option: +$10 = $80
        $updatedItem = Cart::instance('cart')->content()->first();

        $this->assertNotNull($updatedItem);
        $this->assertEquals(80.00, $updatedItem->price);
    }

    public function test_non_wholesale_customer_keeps_full_price_with_options(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Regular Buyer',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($customer, 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(10.00);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            100.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        // No wholesale discount, just base + option = $110
        $item = Cart::instance('cart')->content()->first();

        $this->assertNotNull($item);
        $this->assertEquals(110.00, $item->price);
    }

    public function test_wholesale_array_add_preserves_option_price(): void
    {
        $data = $this->createWholesaleCustomer(20);
        $this->actingAs($data['customer'], 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(10.00);

        Cart::instance('cart')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 100.00,
            'options' => [
                'options' => $productOptions,
                'taxRate' => 0,
            ],
        ]);

        // Group discount: $20 off $100 = $80, option: +$10 = $90
        $updatedItem = Cart::instance('cart')->content()->first();

        $this->assertNotNull($updatedItem);
        $this->assertEquals(90.00, $updatedItem->price);
    }

    public function test_wholesale_with_zero_option_price_not_affected(): void
    {
        $data = $this->createWholesaleCustomer(20);
        $this->actingAs($data['customer'], 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $productOptions = $this->makeProductOptionData(0);

        Cart::instance('cart')->add(
            $product->id,
            $product->name,
            1,
            100.00,
            [
                'options' => $productOptions,
                'taxRate' => 0,
            ]
        );

        // Group discount: $20 off $100 = $80, option: +$0 = $80
        $updatedItem = Cart::instance('cart')->content()->first();

        $this->assertNotNull($updatedItem);
        $this->assertEquals(80.00, $updatedItem->price);
    }

    public function test_apply_option_price_to_wholesale_with_fixed_option(): void
    {
        $basePrice = 100.00;
        $wholesalePrice = 80.00;
        $optionPrice = 10.00;

        $options = $this->makeProductOptionData($optionPrice);

        $result = Cart::instance('cart')->getPriceByOptions($wholesalePrice, $options);

        $this->assertEquals(90.00, $result);
    }

    public function test_apply_option_price_to_wholesale_with_percentage_option(): void
    {
        $wholesalePrice = 180.00;
        $percentageOption = 15;

        $options = $this->makeProductOptionData($percentageOption, 1);

        $result = Cart::instance('cart')->getPriceByOptions($wholesalePrice, $options);

        // 15% of $180 = $27 → $180 + $27 = $207
        $this->assertEquals(207.00, $result);
    }

    public function test_apply_option_price_to_wholesale_with_multiple_options(): void
    {
        $wholesalePrice = 80.00;

        $options = $this->makeMultiOptionData([
            ['name' => 'Warranty', 'value' => '2 Year', 'affect_price' => 10.00],
            ['name' => 'Color', 'value' => 'Gold', 'affect_price' => 5.00],
        ]);

        $result = Cart::instance('cart')->getPriceByOptions($wholesalePrice, $options);

        // $80 + $10 + $5 = $95
        $this->assertEquals(95.00, $result);
    }

    public function test_wholesale_price_service_returns_discounted_price(): void
    {
        $data = $this->createWholesaleCustomer(20);
        $this->actingAs($data['customer'], 'customer');

        $product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $service = app(WholesalePriceService::class);
        $wholesalePrice = $service->getWholesalePrice($product, 1, $data['customer']);

        $this->assertNotNull($wholesalePrice);
        $this->assertLessThan(100.00, $wholesalePrice);
    }
}
