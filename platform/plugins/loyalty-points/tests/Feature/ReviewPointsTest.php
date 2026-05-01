<?php

namespace Botble\LoyaltyPoints\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Review;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Services\LoyaltyPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class ReviewPointsTest extends BaseTestCase
{
    use RefreshDatabase;

    protected LoyaltyPointService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LoyaltyPointService::class);
        $this->enableLoyaltyProgram();
    }

    protected function enableLoyaltyProgram(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', true)->save();
        setting()->forceSet('loyalty_points_points_for_review', 50)->save();
        setting()->forceSet('loyalty_points_points_for_photo_review', 100)->save();
        setting()->forceSet('loyalty_points_points_expiry_months', 12)->save();
    }

    protected function createCustomer(): Customer
    {
        return Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createProduct(): Product
    {
        return Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    protected function createReview(Customer $customer, Product $product, array $attributes = []): Review
    {
        return Review::query()->create(array_merge([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'star' => 5,
            'comment' => 'Great product!',
            'status' => BaseStatusEnum::PUBLISHED,
        ], $attributes));
    }

    protected function createRequest(): Request
    {
        return new Request();
    }

    public function test_customer_receives_points_for_text_review(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance);
        $this->assertEquals(50, $balance->total_points);
    }

    public function test_customer_receives_more_points_for_photo_review(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product, [
            'images' => ['image1.jpg', 'image2.jpg'],
        ]);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNotNull($balance);
        $this->assertEquals(100, $balance->total_points);
    }

    public function test_no_points_for_pending_review(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product, [
            'status' => BaseStatusEnum::PENDING,
        ]);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNull($balance);
    }

    public function test_points_awarded_when_review_published(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product, [
            'status' => BaseStatusEnum::PENDING,
        ]);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNull($balance);

        $review->update(['status' => BaseStatusEnum::PUBLISHED]);

        event(new UpdatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($balance);
        $this->assertEquals(50, $balance->total_points);
    }

    public function test_no_duplicate_points_for_same_review(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));
        event(new UpdatedContentEvent('review', $this->createRequest(), $review));
        event(new UpdatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(50, $balance->total_points);

        $transactionCount = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('note', 'LIKE', '%review #' . $review->id . '%')
            ->count();

        $this->assertEquals(1, $transactionCount);
    }

    public function test_no_points_when_program_disabled(): void
    {
        setting()->forceSet('loyalty_points_enable_loyalty_program', false)->save();

        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNull($balance);
    }

    public function test_no_points_when_review_points_zero(): void
    {
        setting()->forceSet('loyalty_points_points_for_review', 0)->save();

        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNull($balance);
    }

    public function test_no_points_when_photo_review_points_zero(): void
    {
        setting()->forceSet('loyalty_points_points_for_photo_review', 0)->save();

        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product, [
            'images' => ['image1.jpg'],
        ]);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertNull($balance);
    }

    public function test_guest_review_no_points(): void
    {
        $product = $this->createProduct();

        $review = Review::query()->create([
            'customer_id' => null,
            'product_id' => $product->id,
            'star' => 5,
            'comment' => 'Great product!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $transactionCount = PointTransaction::query()
            ->where('type', PointTransaction::TYPE_EARN)
            ->where('note', 'LIKE', '%review%')
            ->count();

        $this->assertEquals(0, $transactionCount);
    }

    public function test_transaction_note_contains_review_id(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertNotNull($transaction);
        $this->assertStringContainsString('review #' . $review->id, $transaction->note);
    }

    public function test_multiple_reviews_accumulate_points(): void
    {
        $customer = $this->createCustomer();

        $product1 = $this->createProduct();
        $product2 = Product::query()->create([
            'name' => 'Test Product 2',
            'price' => 200,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $review1 = $this->createReview($customer, $product1);
        $review2 = Review::query()->create([
            'customer_id' => $customer->id,
            'product_id' => $product2->id,
            'star' => 4,
            'comment' => 'Good product!',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        event(new CreatedContentEvent('review', $this->createRequest(), $review1));
        event(new CreatedContentEvent('review', $this->createRequest(), $review2));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(100, $balance->total_points);
    }

    public function test_custom_review_points_amount(): void
    {
        setting()->forceSet('loyalty_points_points_for_review', 75)->save();

        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(75, $balance->total_points);
    }

    public function test_custom_photo_review_points_amount(): void
    {
        setting()->forceSet('loyalty_points_points_for_photo_review', 150)->save();

        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product, [
            'images' => ['photo.jpg'],
        ]);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();

        $this->assertEquals(150, $balance->total_points);
    }

    public function test_review_points_have_expiry(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $transaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->first();

        $this->assertNotNull($transaction->expires_at);
    }

    public function test_points_reversed_when_review_unpublished(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(50, $balance->total_points);

        // Unpublish the review
        $review->update(['status' => BaseStatusEnum::PENDING]);

        event(new UpdatedContentEvent('review', $this->createRequest(), $review));

        $balance = $balance->fresh();
        $this->assertEquals(0, $balance->total_points);
    }

    public function test_points_reversed_when_review_deleted(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(50, $balance->total_points);

        // Delete the review
        event(new DeletedContentEvent('review', $this->createRequest(), $review));

        $balance = $balance->fresh();
        $this->assertEquals(0, $balance->total_points);
    }

    public function test_reverse_transaction_created_when_review_unpublished(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        // Unpublish the review
        $review->update(['status' => BaseStatusEnum::PENDING]);
        event(new UpdatedContentEvent('review', $this->createRequest(), $review));

        $reverseTransaction = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REVERSE)
            ->first();

        $this->assertNotNull($reverseTransaction);
        $this->assertEquals(-50, $reverseTransaction->points);
        $this->assertStringContainsString('review #' . $review->id, $reverseTransaction->note);
    }

    public function test_no_double_reversal_for_same_review(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        // Unpublish multiple times
        $review->update(['status' => BaseStatusEnum::PENDING]);
        event(new UpdatedContentEvent('review', $this->createRequest(), $review));
        event(new UpdatedContentEvent('review', $this->createRequest(), $review));
        event(new UpdatedContentEvent('review', $this->createRequest(), $review));

        $reverseCount = PointTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('type', PointTransaction::TYPE_REVERSE)
            ->count();

        $this->assertEquals(1, $reverseCount);
    }

    public function test_unpublish_without_prior_points_no_effect(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        // Create review as pending (no points awarded)
        $review = $this->createReview($customer, $product, [
            'status' => BaseStatusEnum::PENDING,
        ]);

        event(new CreatedContentEvent('review', $this->createRequest(), $review));

        // No points awarded
        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertNull($balance);

        // Try to unpublish (set to draft)
        $review->update(['status' => BaseStatusEnum::DRAFT]);
        event(new UpdatedContentEvent('review', $this->createRequest(), $review));

        // No reversal transactions
        $reverseCount = PointTransaction::query()
            ->where('type', PointTransaction::TYPE_REVERSE)
            ->count();

        $this->assertEquals(0, $reverseCount);
    }

    public function test_republish_review_awards_points_again(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $review = $this->createReview($customer, $product);

        // Award points
        event(new CreatedContentEvent('review', $this->createRequest(), $review));
        $balance = CustomerPointBalance::query()->where('customer_id', $customer->id)->first();
        $this->assertEquals(50, $balance->total_points);

        // Unpublish - reverse points
        $review->update(['status' => BaseStatusEnum::PENDING]);
        event(new UpdatedContentEvent('review', $this->createRequest(), $review));
        $balance = $balance->fresh();
        $this->assertEquals(0, $balance->total_points);

        // Republish - award points again
        $review->update(['status' => BaseStatusEnum::PUBLISHED]);
        event(new UpdatedContentEvent('review', $this->createRequest(), $review));
        $balance = $balance->fresh();
        $this->assertEquals(50, $balance->total_points);
    }

    public function test_deleted_review_listener_registered(): void
    {
        $events = app('events');
        $listeners = $events->getListeners(DeletedContentEvent::class);

        $this->assertGreaterThan(0, count($listeners));
    }
}
