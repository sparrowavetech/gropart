<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Listeners\SaveOrderWholesalePrices;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaveOrderWholesalePricesTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected Customer $customer;

    protected CustomerGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->set('wholesale_enabled', '1')->save();

        $this->product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 140,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->group = CustomerGroup::query()->create([
            'name' => 'Wholesale Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 0,
            'priority' => 1,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->customer = Customer::query()->create([
            'name' => 'Wholesale Buyer',
            'email' => 'wholesale@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->customer->wholesaleGroups()->attach($this->group->id, [
            'assigned_at' => now(),
        ]);
    }

    public function test_listener_updates_order_product_price_to_wholesale(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = $this->createOrderWithProduct(140);

        $this->dispatchListener($order);

        $orderProduct = $order->products()->first();
        $this->assertEqualsWithDelta(110.0, $orderProduct->price, 0.01);
    }

    public function test_listener_updates_order_totals_when_price_changes(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = $this->createOrderWithProduct(140);

        $this->dispatchListener($order);

        $order->refresh();
        $this->assertEqualsWithDelta(110.0, $order->sub_total, 0.01);
        $this->assertEqualsWithDelta(110.0, $order->amount, 0.01);
    }

    public function test_listener_fixes_amount_when_subtotal_already_wholesale_but_amount_is_retail(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // Simulate the bug: order product and sub_total already have wholesale price,
        // but amount still has the retail price (e.g., from rawTotalByItems including tax)
        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => 140,
            'sub_total' => 110,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 1,
            'price' => 110,
            'tax_amount' => 0,
            'options' => [],
        ]);

        $this->dispatchListener($order);

        $order->refresh();
        $this->assertEqualsWithDelta(110.0, $order->sub_total, 0.01);
        $this->assertEqualsWithDelta(110.0, $order->amount, 0.01, 'Order amount should be corrected to match sub_total + tax + shipping - discount + payment_fee');
    }

    public function test_listener_fixes_amount_with_shipping_when_subtotal_already_wholesale(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => 160,
            'sub_total' => 110,
            'tax_amount' => 0,
            'shipping_amount' => 20,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 1,
            'price' => 110,
            'tax_amount' => 0,
            'options' => [],
        ]);

        $this->dispatchListener($order);

        $order->refresh();
        $this->assertEqualsWithDelta(130.0, $order->amount, 0.01, 'Amount should be 110 sub_total + 20 shipping = 130');
    }

    public function test_listener_fixes_amount_with_tax_and_shipping(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => 200,
            'sub_total' => 110,
            'tax_amount' => 15,
            'shipping_amount' => 25,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 1,
            'price' => 110,
            'tax_amount' => 15,
            'options' => [],
        ]);

        $this->dispatchListener($order);

        $order->refresh();
        $this->assertEqualsWithDelta(150.0, $order->amount, 0.01, 'Amount should be 110 + 15 tax + 25 shipping = 150');
    }

    public function test_listener_does_not_update_when_amount_already_correct(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => 110,
            'sub_total' => 110,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 1,
            'price' => 110,
            'tax_amount' => 0,
            'options' => [],
        ]);

        $originalUpdatedAt = $order->updated_at;

        $this->dispatchListener($order);

        $order->refresh();
        $this->assertEqualsWithDelta(110.0, $order->amount, 0.01);
    }

    public function test_listener_skips_when_wholesale_disabled(): void
    {
        setting()->set('wholesale_enabled', '0')->save();

        $order = $this->createOrderWithProduct(140);
        $originalAmount = $order->amount;

        $this->dispatchListener($order);

        $order->refresh();
        $this->assertEqualsWithDelta($originalAmount, $order->amount, 0.01);
    }

    public function test_listener_skips_when_customer_not_wholesale(): void
    {
        $regularCustomer = Customer::query()->create([
            'name' => 'Regular Buyer',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);

        $order = Order::query()->create([
            'user_id' => $regularCustomer->id,
            'amount' => 140,
            'sub_total' => 140,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 1,
            'price' => 140,
            'tax_amount' => 0,
            'options' => [],
        ]);

        $this->dispatchListener($order);

        $order->refresh();
        $this->assertEqualsWithDelta(140.0, $order->amount, 0.01, 'Non-wholesale customer order should not be modified');
    }

    public function test_listener_applies_percentage_discount(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = $this->createOrderWithProduct(140);

        $this->dispatchListener($order);

        $order->refresh();
        $orderProduct = $order->products()->first();

        // 20% off 140 = 112
        $this->assertEqualsWithDelta(112.0, $orderProduct->price, 0.01);
        $this->assertEqualsWithDelta(112.0, $order->sub_total, 0.01);
        $this->assertEqualsWithDelta(112.0, $order->amount, 0.01);
    }

    public function test_listener_applies_fixed_discount(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = $this->createOrderWithProduct(140);

        $this->dispatchListener($order);

        $order->refresh();
        $orderProduct = $order->products()->first();

        // 140 - 30 = 110
        $this->assertEqualsWithDelta(110.0, $orderProduct->price, 0.01);
        $this->assertEqualsWithDelta(110.0, $order->sub_total, 0.01);
        $this->assertEqualsWithDelta(110.0, $order->amount, 0.01);
    }

    public function test_listener_updates_invoice_when_amount_changes(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = $this->createOrderWithProduct(140);

        Invoice::query()->create([
            'reference_id' => $order->id,
            'reference_type' => get_class($order),
            'sub_total' => 140,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'amount' => 140,
            'code' => 'INV-001',
            'status' => 'completed',
        ]);

        $this->dispatchListener($order);

        $invoice = Invoice::query()
            ->where('reference_id', $order->id)
            ->where('reference_type', get_class($order))
            ->first();

        $this->assertNotNull($invoice);
        $this->assertEqualsWithDelta(110.0, $invoice->sub_total, 0.01);
        $this->assertEqualsWithDelta(110.0, $invoice->amount, 0.01);
    }

    public function test_listener_updates_invoice_when_only_amount_inconsistent(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        // sub_total already wholesale, but amount is wrong
        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => 140,
            'sub_total' => 110,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 1,
            'price' => 110,
            'tax_amount' => 0,
            'options' => [],
        ]);

        Invoice::query()->create([
            'reference_id' => $order->id,
            'reference_type' => get_class($order),
            'sub_total' => 110,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'amount' => 140,
            'code' => 'INV-002',
            'status' => 'completed',
        ]);

        $this->dispatchListener($order);

        $invoice = Invoice::query()
            ->where('reference_id', $order->id)
            ->where('reference_type', get_class($order))
            ->first();

        $this->assertNotNull($invoice);
        $this->assertEqualsWithDelta(110.0, $invoice->amount, 0.01, 'Invoice amount should be corrected');
    }

    public function test_listener_handles_multiple_order_products(): void
    {
        $product2 = Product::query()->create([
            'name' => 'Second Product',
            'price' => 200,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        GroupPricingRule::query()->create([
            'product_id' => $product2->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => 340,
            'sub_total' => 340,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 1,
            'price' => 140,
            'tax_amount' => 0,
            'options' => [],
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'qty' => 1,
            'price' => 200,
            'tax_amount' => 0,
            'options' => [],
        ]);

        $this->dispatchListener($order);

        $order->refresh();

        // Product 1: fixed_price 110, Product 2: 25% off 200 = 150
        $this->assertEqualsWithDelta(260.0, $order->sub_total, 0.01);
        $this->assertEqualsWithDelta(260.0, $order->amount, 0.01);
    }

    public function test_listener_adjusts_tax_proportionally(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 100,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => 168,
            'sub_total' => 140,
            'tax_amount' => 28,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 1,
            'price' => 140,
            'tax_amount' => 28,
            'options' => [],
        ]);

        $this->dispatchListener($order);

        $order->refresh();
        $orderProduct = $order->products()->first();

        $this->assertEqualsWithDelta(100.0, $orderProduct->price, 0.01);
        // Tax adjusted proportionally: 28 * (100 / 140) = 20
        $this->assertEqualsWithDelta(20.0, $orderProduct->tax_amount, 0.01);
        $this->assertEqualsWithDelta(100.0, $order->sub_total, 0.01);
        $this->assertEqualsWithDelta(20.0, $order->tax_amount, 0.01);
        $this->assertEqualsWithDelta(120.0, $order->amount, 0.01);
    }

    public function test_listener_saves_wholesale_discount_info_to_order_product_options(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = $this->createOrderWithProduct(140);

        $this->dispatchListener($order);

        $orderProduct = $order->products()->first();
        $options = $orderProduct->options;

        $this->assertArrayHasKey('extras', $options);
        $this->assertArrayHasKey('wholesale_discount', $options['extras']);
        $this->assertEquals('20%', $options['extras']['wholesale_discount']);
    }

    public function test_listener_handles_order_with_discount_and_payment_fee(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 1,
            'discount_type' => PricingDiscountTypeEnum::FIXED_PRICE,
            'discount_value' => 110,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => 155,
            'sub_total' => 140,
            'tax_amount' => 0,
            'shipping_amount' => 20,
            'discount_amount' => 10,
            'payment_fee' => 5,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 1,
            'price' => 140,
            'tax_amount' => 0,
            'options' => [],
        ]);

        $this->dispatchListener($order);

        $order->refresh();
        // amount = 110 + 0 tax + 20 shipping - 10 discount + 5 payment_fee = 125
        $this->assertEqualsWithDelta(110.0, $order->sub_total, 0.01);
        $this->assertEqualsWithDelta(125.0, $order->amount, 0.01);
    }

    public function test_listener_with_quantity_based_pricing(): void
    {
        GroupPricingRule::query()->create([
            'product_id' => $this->product->id,
            'customer_group_id' => null,
            'min_quantity' => 5,
            'max_quantity' => null,
            'discount_type' => PricingDiscountTypeEnum::PERCENTAGE,
            'discount_value' => 30,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => 700,
            'sub_total' => 700,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => 5,
            'price' => 140,
            'tax_amount' => 0,
            'options' => [],
        ]);

        $this->dispatchListener($order);

        $order->refresh();
        $orderProduct = $order->products()->first();

        // 30% off 140 = 98 per unit, 5 * 98 = 490
        $this->assertEqualsWithDelta(98.0, $orderProduct->price, 0.01);
        $this->assertEqualsWithDelta(490.0, $order->sub_total, 0.01);
        $this->assertEqualsWithDelta(490.0, $order->amount, 0.01);
    }

    protected function createOrderWithProduct(float $price, int $qty = 1): Order
    {
        $total = $price * $qty;

        $order = Order::query()->create([
            'user_id' => $this->customer->id,
            'amount' => $total,
            'sub_total' => $total,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'payment_fee' => 0,
            'status' => OrderStatusEnum::PENDING,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => false,
        ]);

        OrderProduct::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'qty' => $qty,
            'price' => $price,
            'tax_amount' => 0,
            'options' => [],
        ]);

        return $order;
    }

    protected function dispatchListener(Order $order): void
    {
        $listener = app(SaveOrderWholesalePrices::class);
        $event = new OrderPlacedEvent($order);
        $listener->handle($event);
    }
}
