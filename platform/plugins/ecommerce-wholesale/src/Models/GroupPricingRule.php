<?php

namespace Botble\EcommerceWholesale\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupPricingRule extends BaseModel
{
    protected $table = 'ws_group_pricing_rules';

    protected $fillable = [
        'scope',
        'product_id',
        'category_id',
        'customer_group_id',
        'store_id',
        'min_quantity',
        'max_quantity',
        'discount_type',
        'discount_value',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scope' => PricingRuleScopeEnum::class,
            'discount_type' => PricingDiscountTypeEnum::class,
            'discount_value' => 'float',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'status' => CustomerGroupStatusEnum::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function appliesToQuantity(int $quantity): bool
    {
        if ($quantity < $this->min_quantity) {
            return false;
        }

        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $basePrice): float
    {
        $basePrice = max(0, $basePrice);

        return match ($this->discount_type->getValue()) {
            PricingDiscountTypeEnum::PERCENTAGE => $basePrice * ($this->discount_value / 100),
            PricingDiscountTypeEnum::FIXED => min($this->discount_value, $basePrice),
            PricingDiscountTypeEnum::FIXED_PRICE => max(0, $basePrice - $this->discount_value),
            default => 0,
        };
    }

    public function calculateFinalPrice(float $basePrice): float
    {
        $basePrice = max(0, $basePrice);

        return match ($this->discount_type->getValue()) {
            PricingDiscountTypeEnum::PERCENTAGE => $basePrice * (1 - $this->discount_value / 100),
            PricingDiscountTypeEnum::FIXED => max(0, $basePrice - $this->discount_value),
            PricingDiscountTypeEnum::FIXED_PRICE => max(0, $this->discount_value),
            default => $basePrice,
        };
    }

    public function getQuantityRangeAttribute(): string
    {
        if ($this->max_quantity) {
            return $this->min_quantity . '-' . $this->max_quantity;
        }

        return $this->min_quantity . '+';
    }
}
