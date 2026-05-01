<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WholesaleApplicationModelTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_pending_status(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $app = WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $this->assertDatabaseHas('ws_wholesale_applications', [
            'id' => $app->id,
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $app->refresh();
        $this->assertEquals(ApplicationStatusEnum::PENDING, $app->status->getValue());
    }

    public function test_approved_status(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $app = WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::APPROVED,
        ]);

        $this->assertDatabaseHas('ws_wholesale_applications', [
            'id' => $app->id,
            'status' => ApplicationStatusEnum::APPROVED,
        ]);

        $app->refresh();
        $this->assertEquals(ApplicationStatusEnum::APPROVED, $app->status->getValue());
    }

    public function test_rejected_status(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $app = WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::REJECTED,
        ]);

        $this->assertDatabaseHas('ws_wholesale_applications', [
            'id' => $app->id,
            'status' => ApplicationStatusEnum::REJECTED,
        ]);

        $app->refresh();
        $this->assertEquals(ApplicationStatusEnum::REJECTED, $app->status->getValue());
    }
}
