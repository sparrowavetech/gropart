<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Supports\BaseTestCase;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerGroupModelTest extends BaseTestCase
{
    use RefreshDatabase;

    public function test_percentage_discount_calculation(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Test Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 15,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(15.0, $group->calculateDiscount(100), 0.01);
        $this->assertEqualsWithDelta(85.0, $group->calculateFinalPrice(100), 0.01);
    }

    public function test_fixed_discount_calculation(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Test Group',
            'discount_type' => DiscountTypeEnum::FIXED,
            'discount_value' => 50,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(50.0, $group->calculateDiscount(200), 0.01);
        $this->assertEqualsWithDelta(150.0, $group->calculateFinalPrice(200), 0.01);
    }

    public function test_fixed_discount_cannot_exceed_original_price(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Test Group',
            'discount_type' => DiscountTypeEnum::FIXED,
            'discount_value' => 150,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(100.0, $group->calculateDiscount(100), 0.01);
        $this->assertEqualsWithDelta(0.0, $group->calculateFinalPrice(100), 0.01);
    }

    public function test_percentage_discount_on_zero_price(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Test Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 20,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(0.0, $group->calculateDiscount(0), 0.01);
        $this->assertEqualsWithDelta(0.0, $group->calculateFinalPrice(0), 0.01);
    }

    public function test_one_hundred_percent_discount(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Test Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 100,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $this->assertEqualsWithDelta(100.0, $group->calculateDiscount(100), 0.01);
        $this->assertEqualsWithDelta(0.0, $group->calculateFinalPrice(100), 0.01);
    }

    public function test_percentage_discount_scales_with_price(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $group = CustomerGroup::query()->find($group->id);

        $this->assertEqualsWithDelta(20.0, $group->calculateDiscount(200), 0.01);
        $this->assertEqualsWithDelta(180.0, $group->calculateFinalPrice(200), 0.01);

        $this->assertEqualsWithDelta(50.0, $group->calculateDiscount(500), 0.01);
        $this->assertEqualsWithDelta(450.0, $group->calculateFinalPrice(500), 0.01);
    }

    public function test_percentage_discount_not_treated_as_fixed_amount(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Gold',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $group = CustomerGroup::query()->find($group->id);

        $discount = $group->calculateDiscount(200);

        $this->assertNotEquals(10.0, $discount, 'Percentage discount should not be treated as fixed amount');
        $this->assertEqualsWithDelta(20.0, $discount, 0.01, '10% of 200 should be 20, not 10');
    }

    public function test_fixed_discount_stays_constant_regardless_of_price(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Fixed Group',
            'discount_type' => DiscountTypeEnum::FIXED,
            'discount_value' => 25,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $group = CustomerGroup::query()->find($group->id);

        $this->assertEqualsWithDelta(25.0, $group->calculateDiscount(200), 0.01);
        $this->assertEqualsWithDelta(25.0, $group->calculateDiscount(500), 0.01);
        $this->assertEqualsWithDelta(25.0, $group->calculateDiscount(1000), 0.01);
    }

    public function test_percentage_and_fixed_give_different_results_on_same_price(): void
    {
        $percentageGroup = CustomerGroup::query()->create([
            'name' => 'Percentage Group',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $fixedGroup = CustomerGroup::query()->create([
            'name' => 'Fixed Group',
            'discount_type' => DiscountTypeEnum::FIXED,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $percentageGroup = CustomerGroup::query()->find($percentageGroup->id);
        $fixedGroup = CustomerGroup::query()->find($fixedGroup->id);

        $price = 200;

        $percentageDiscount = $percentageGroup->calculateDiscount($price);
        $fixedDiscount = $fixedGroup->calculateDiscount($price);

        $this->assertEqualsWithDelta(20.0, $percentageDiscount, 0.01, '10% of 200 = 20');
        $this->assertEqualsWithDelta(10.0, $fixedDiscount, 0.01, 'Fixed 10 off 200 = 10');
        $this->assertNotEquals($percentageDiscount, $fixedDiscount, 'Percentage and fixed should produce different discounts');
    }

    public function test_enum_cast_preserves_discount_type_after_db_reload(): void
    {
        $group = CustomerGroup::query()->create([
            'name' => 'Reload Test',
            'discount_type' => DiscountTypeEnum::PERCENTAGE,
            'discount_value' => 10,
            'status' => CustomerGroupStatusEnum::PUBLISHED,
        ]);

        $reloaded = CustomerGroup::query()->find($group->id);

        $this->assertInstanceOf(DiscountTypeEnum::class, $reloaded->discount_type);
        $this->assertEquals(DiscountTypeEnum::PERCENTAGE, $reloaded->discount_type->getValue());
        $this->assertEqualsWithDelta(20.0, $reloaded->calculateDiscount(200), 0.01);
    }
}
