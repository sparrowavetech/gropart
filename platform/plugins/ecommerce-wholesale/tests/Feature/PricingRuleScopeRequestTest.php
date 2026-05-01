<?php

namespace Botble\EcommerceWholesale\Tests\Feature;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseTestCase;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Botble\EcommerceWholesale\Http\Requests\PricingRuleRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class PricingRuleScopeRequestTest extends BaseTestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected ProductCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::query()->create([
            'name' => 'Test Product',
            'price' => 100,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        $this->category = ProductCategory::query()->create([
            'name' => 'Electronics',
            'status' => BaseStatusEnum::PUBLISHED,
        ]);
    }

    protected function baseData(array $overrides = []): array
    {
        return array_merge([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => $this->product->id,
            'category_id' => null,
            'min_quantity' => 1,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'status' => 'published',
        ], $overrides);
    }

    protected function getRules(array $data): array
    {
        $request = new PricingRuleRequest($data);
        $request->merge($data);

        return $request->rules();
    }

    public function test_scope_is_required(): void
    {
        $data = $this->baseData(['scope' => '']);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('scope', $validator->errors()->toArray());
    }

    public function test_scope_must_be_valid_value(): void
    {
        $data = $this->baseData(['scope' => 'invalid_scope']);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('scope', $validator->errors()->toArray());
    }

    public function test_product_scope_accepts_valid_values(): void
    {
        foreach (PricingRuleScopeEnum::values() as $scope) {
            $data = $this->baseData(['scope' => $scope]);

            if ($scope === PricingRuleScopeEnum::CATEGORY) {
                $data['category_id'] = $this->category->id;
                $data['product_id'] = null;
            } elseif ($scope === PricingRuleScopeEnum::GLOBAL) {
                $data['product_id'] = null;
            }

            $rules = $this->getRules($data);
            $validator = Validator::make($data, $rules);

            $this->assertTrue($validator->passes(), "Scope '{$scope}' should be valid");
        }
    }

    public function test_product_id_required_when_scope_is_product(): void
    {
        $data = $this->baseData([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => null,
        ]);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('product_id', $validator->errors()->toArray());
    }

    public function test_product_id_not_required_when_scope_is_category(): void
    {
        $data = $this->baseData([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'product_id' => null,
            'category_id' => $this->category->id,
        ]);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_product_id_not_required_when_scope_is_global(): void
    {
        $data = $this->baseData([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'product_id' => null,
        ]);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_category_id_required_when_scope_is_category(): void
    {
        $data = $this->baseData([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'product_id' => null,
            'category_id' => null,
        ]);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    public function test_category_id_not_required_when_scope_is_product(): void
    {
        $data = $this->baseData([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'category_id' => null,
        ]);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_category_id_not_required_when_scope_is_global(): void
    {
        $data = $this->baseData([
            'scope' => PricingRuleScopeEnum::GLOBAL,
            'product_id' => null,
            'category_id' => null,
        ]);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_category_id_must_exist_in_database(): void
    {
        $data = $this->baseData([
            'scope' => PricingRuleScopeEnum::CATEGORY,
            'product_id' => null,
            'category_id' => 99999,
        ]);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    public function test_product_id_must_exist_in_database(): void
    {
        $data = $this->baseData([
            'scope' => PricingRuleScopeEnum::PRODUCT,
            'product_id' => 99999,
        ]);
        $rules = $this->getRules($data);

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('product_id', $validator->errors()->toArray());
    }
}
