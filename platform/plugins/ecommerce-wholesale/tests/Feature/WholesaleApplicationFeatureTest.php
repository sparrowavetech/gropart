<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WholesaleApplicationFeatureTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_can_create_application(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Applicant',
            'email' => 'applicant@example.com',
            'password' => bcrypt('password'),
        ]);

        $application = WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => '1234567890',
            'company_name' => 'My Company',
            'tax_id' => '1234567890',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $this->assertDatabaseHas('ws_wholesale_applications', [
            'email' => 'applicant@example.com',
            'company_name' => 'My Company',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $application->refresh();
        $this->assertEquals(ApplicationStatusEnum::PENDING, $application->status->getValue());
    }

    public function test_application_belongs_to_customer(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
        ]);

        $application = WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => '1234567890',
            'company_name' => 'Buyer Corp',
            'business_type' => 'distributor',
            'expected_volume' => '5000-10000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $this->assertEquals($customer->id, $application->customer->id);
    }

    public function test_application_without_customer(): void
    {
        $application = WholesaleApplication::query()->create([
            'customer_id' => null,
            'email' => 'guest@example.com',
            'name' => 'Guest Applicant',
            'phone' => '0987654321',
            'company_name' => 'Guest LLC',
            'business_type' => 'reseller',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        $this->assertNull($application->customer);

        $application->refresh();
        $this->assertEquals(ApplicationStatusEnum::PENDING, $application->status->getValue());
    }

    public function test_query_by_status(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => 'pending@example.com',
            'name' => 'Pending',
            'phone' => '111',
            'company_name' => 'Pending Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::PENDING,
        ]);

        WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => 'approved@example.com',
            'name' => 'Approved',
            'phone' => '222',
            'company_name' => 'Approved Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::APPROVED,
        ]);

        WholesaleApplication::query()->create([
            'customer_id' => $customer->id,
            'email' => 'rejected@example.com',
            'name' => 'Rejected',
            'phone' => '333',
            'company_name' => 'Rejected Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000-5000',
            'status' => ApplicationStatusEnum::REJECTED,
        ]);

        $pending = WholesaleApplication::query()
            ->where('status', ApplicationStatusEnum::PENDING)
            ->count();

        $this->assertEquals(1, $pending);

        $total = WholesaleApplication::query()->count();
        $this->assertEquals(3, $total);
    }
}
