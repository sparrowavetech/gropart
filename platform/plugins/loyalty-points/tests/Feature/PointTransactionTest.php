<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PointTransactionTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createOrder(Customer $customer, float $amount = 100): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'amount' => $amount,
            'sub_total' => $amount,
            'status' => OrderStatusEnum::COMPLETED,
            'shipping_method' => ShippingMethodEnum::DEFAULT,
            'is_finished' => true,
            'completed_at' => now(),
        ]);
    }

    public function test_can_create_earn_transaction(): void
    {
        $customer = $this->createCustomer();

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Earned from order',
        ]);

        $this->assertDatabaseHas('ec_customer_points_transactions', [
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
        ]);
    }

    public function test_can_create_redeem_transaction(): void
    {
        $customer = $this->createCustomer();

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REDEEM,
            'points' => -50,
            'note' => 'Redeemed for discount',
        ]);

        $this->assertEquals(PointTransaction::TYPE_REDEEM, $transaction->type);
        $this->assertEquals(-50, $transaction->points);
    }

    public function test_can_create_adjust_transaction(): void
    {
        $customer = $this->createCustomer();

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_ADJUST,
            'points' => 25,
            'note' => 'Admin adjustment',
        ]);

        $this->assertEquals(PointTransaction::TYPE_ADJUST, $transaction->type);
    }

    public function test_can_create_reverse_transaction(): void
    {
        $customer = $this->createCustomer();

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REVERSE,
            'points' => -100,
            'note' => 'Order cancelled',
        ]);

        $this->assertEquals(PointTransaction::TYPE_REVERSE, $transaction->type);
    }

    public function test_transaction_belongs_to_customer(): void
    {
        $customer = $this->createCustomer();

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Test',
        ]);

        $this->assertEquals($customer->id, $transaction->customer->id);
        $this->assertEquals('Test Customer', $transaction->customer->name);
    }

    public function test_transaction_can_have_order(): void
    {
        $customer = $this->createCustomer();
        $order = $this->createOrder($customer);

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Earned from order',
        ]);

        $this->assertNotNull($transaction->order);
        $this->assertEquals($order->id, $transaction->order->id);
    }

    public function test_transaction_without_order(): void
    {
        $customer = $this->createCustomer();

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Registration bonus',
        ]);

        $this->assertNull($transaction->order);
    }

    public function test_transaction_formatted_points_positive(): void
    {
        $customer = $this->createCustomer();

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Test',
        ]);

        $this->assertEquals('+100', $transaction->formatted_points);
    }

    public function test_transaction_formatted_points_negative(): void
    {
        $customer = $this->createCustomer();

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REDEEM,
            'points' => -50,
            'note' => 'Test',
        ]);

        $this->assertEquals('-50', $transaction->formatted_points);
    }

    public function test_transaction_with_expiry_date(): void
    {
        $customer = $this->createCustomer();
        $expiresAt = now()->addMonths(12);

        $transaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Test',
            'expires_at' => $expiresAt,
        ]);

        $this->assertNotNull($transaction->expires_at);
        $this->assertEquals($expiresAt->format('Y-m-d'), $transaction->expires_at->format('Y-m-d'));
    }

    public function test_can_filter_transactions_by_type(): void
    {
        $customer = $this->createCustomer();

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Earn 1',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Earn 2',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REDEEM,
            'points' => -50,
            'note' => 'Redeem',
        ]);

        $earnTransactions = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->get();

        $redeemTransactions = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REDEEM)
            ->get();

        $this->assertCount(2, $earnTransactions);
        $this->assertCount(1, $redeemTransactions);
    }

    public function test_can_filter_transactions_by_customer(): void
    {
        $customer1 = $this->createCustomer();
        $customer2 = Customer::query()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@example.com',
            'password' => bcrypt('password'),
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer1->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Customer 1 transaction 1',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer1->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Customer 1 transaction 2',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer2->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 150,
            'note' => 'Customer 2 transaction',
        ]);

        $customer1Transactions = PointTransaction::query()->where('customer_id', $customer1->id)->get();
        $customer2Transactions = PointTransaction::query()->where('customer_id', $customer2->id)->get();

        $this->assertCount(2, $customer1Transactions);
        $this->assertCount(1, $customer2Transactions);
    }

    public function test_transaction_type_label(): void
    {
        $customer = $this->createCustomer();

        $earnTransaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Test',
        ]);

        $redeemTransaction = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REDEEM,
            'points' => -50,
            'note' => 'Test',
        ]);

        $this->assertNotEmpty($earnTransaction->getTypeLabel());
        $this->assertNotEmpty($redeemTransaction->getTypeLabel());
    }

    public function test_transactions_ordered_by_created_at(): void
    {
        $customer = $this->createCustomer();

        $transaction1 = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'First',
            'created_at' => now()->subDays(2),
        ]);

        $transaction2 = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Second',
            'created_at' => now()->subDay(),
        ]);

        $transaction3 = PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 300,
            'note' => 'Third',
            'created_at' => now(),
        ]);

        $transactions = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        $this->assertEquals($transaction3->id, $transactions->first()->id);
        $this->assertEquals($transaction1->id, $transactions->last()->id);
    }

    public function test_sum_total_points_for_customer(): void
    {
        $customer = $this->createCustomer();

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 100,
            'note' => 'Earn 1',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => 200,
            'note' => 'Earn 2',
        ]);

        PointTransaction::query()->create([
            'customer_id' => $customer->id,
            'type' => PointTransaction::TYPE_REDEEM,
            'points' => -50,
            'note' => 'Redeem',
        ]);

        $totalPoints = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->sum('points');

        $this->assertEquals(250, $totalPoints);
    }
}
