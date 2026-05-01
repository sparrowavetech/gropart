<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyProfileEditTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::query()->create([
            'name' => 'Profile Test Customer',
            'email' => 'profile-test@example.com',
            'password' => bcrypt('password'),
            'confirmed_at' => now(),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);
    }

    public function test_customer_can_access_edit_form(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000',
            'status' => ApplicationStatusEnum::APPROVED,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.profile.edit'));

        $response->assertOk();
        $response->assertSee('Edit Company Profile');
        $response->assertSee('1234567890');
    }

    public function test_customer_without_application_gets_404(): void
    {
        $this->actingAs($this->customer, 'customer');

        $response = $this->get(route('customer.wholesale.profile.edit'));

        $response->assertNotFound();
    }

    public function test_customer_can_update_editable_fields(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Test Co',
            'business_type' => 'retailer',
            'expected_volume' => '1000',
            'notes' => 'Old notes',
            'status' => ApplicationStatusEnum::APPROVED,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($this->customer, 'customer');

        $response = $this->post(route('customer.wholesale.profile.update'), [
            'phone' => '9999999999',
            'business_type' => 'distributor',
            'expected_volume' => '5000+',
            'notes' => 'Updated notes',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ws_wholesale_applications', [
            'customer_id' => $this->customer->id,
            'phone' => '9999999999',
            'business_type' => 'distributor',
            'expected_volume' => '5000+',
            'notes' => 'Updated notes',
            'company_name' => 'Test Co',
        ]);
    }

    public function test_guest_cannot_access_edit_form(): void
    {
        $response = $this->get(route('customer.wholesale.profile.edit'));

        $response->assertRedirect();
    }

    public function test_other_customer_cannot_edit_different_profile(): void
    {
        WholesaleApplication::query()->create([
            'customer_id' => $this->customer->id,
            'email' => $this->customer->email,
            'name' => $this->customer->name,
            'phone' => '1234567890',
            'company_name' => 'Original Co',
            'status' => ApplicationStatusEnum::APPROVED,
            'reviewed_at' => now(),
        ]);

        $otherCustomer = Customer::query()->create([
            'name' => 'Other Customer',
            'email' => 'other-profile@example.com',
            'password' => bcrypt('password'),
            'confirmed_at' => now(),
            'status' => CustomerStatusEnum::ACTIVATED,
        ]);

        $this->actingAs($otherCustomer, 'customer');

        $response = $this->get(route('customer.wholesale.profile.edit'));

        $response->assertNotFound();
    }
}
