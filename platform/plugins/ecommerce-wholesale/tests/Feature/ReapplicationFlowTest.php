<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Botble\EcommerceWholesale\Services\ReapplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReapplicationFlowTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::query()->create([
            'name' => 'Reapply Test Customer',
            'email' => 'reapply-test@example.com',
            'password' => bcrypt('password'),
            'confirmed_at' => now(),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);
    }

    public function test_rejected_customer_can_access_reapply_form(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Rejected Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000',
            'status' => ApplicationStatusEnum::REJECTED,
            'reviewed_at' => now(),
            'rejection_reason' => 'Incomplete docs',
        ]);

        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.reapply.form'));

        $response->assertOk();
        $response->assertSee('Reapply for Wholesale Account');
        $response->assertSee('Incomplete docs');
        $response->assertSee('Rejected Co');
    }

    public function test_pending_customer_cannot_access_reapply_form(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Pending Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.reapply.form'));

        $response->assertRedirect(route('customer.wholesale.index'));
    }

    public function test_approved_customer_cannot_access_reapply_form(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Approved Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000',
            'status' => ApplicationStatusEnum::APPROVED,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.reapply.form'));

        $response->assertRedirect(route('customer.wholesale.index'));
    }

    public function test_rejected_customer_can_submit_reapplication(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Old Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000',
            'status' => ApplicationStatusEnum::REJECTED,
            'reviewed_at' => now(),
            'rejection_reason' => 'Bad docs',
        ]);

        $this->actingAs($this->customer, 'customer');

        $response = $this->post(route('customer.wholesale.reapply.submit'), [
            'company_name' => 'New Co',
            'phone' => '9876543210',
            'business_type' => 'distributor',
            'expected_volume' => '5000+',
            'notes' => 'Updated application',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ws_wholesale_applications', [
            'customer_id' => $this->customer->id,
            'company_name' => 'New Co',
            'phone' => '9876543210',
            'status' => ApplicationStatusEnum::PENDING,
        ]);
    }

    public function test_customer_without_application_cannot_reapply(): void
    {
        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.reapply.form'));

        $response->assertRedirect(route('customer.wholesale.index'));
    }

    public function test_reapplication_service_can_reapply_returns_correct_values(): void
    {
        $service = app(ReapplicationService::class);

        $this->assertFalse($service->canReapply($this->customer));

        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '123',
            'company_name' => 'Co',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $this->assertFalse($service->canReapply($this->customer));

        WholesaleApplication::query()
            ->where('customer_id', $this->customer->id)
            ->update(['status' => ApplicationStatusEnum::REJECTED]);

        $this->assertTrue($service->canReapply($this->customer));
    }

    public function test_reapplication_prevents_duplicate_pending(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '123',
            'company_name' => 'Co',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $service = app(ReapplicationService::class);

        $this->expectException(\RuntimeException::class);

        $service->createReapplication($this->customer, [
            'company_name' => 'New Co',
        ]);
    }
}
