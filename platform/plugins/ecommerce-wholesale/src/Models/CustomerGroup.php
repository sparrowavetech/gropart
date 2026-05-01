<?php

namespace Botble\EcommerceWholesale\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\DiscountTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerGroup extends BaseModel
{
    protected $table = 'ws_customer_groups';

    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'discount_value',
        'priority',
        'min_order_quantity',
        'min_order_value',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerGroupStatusEnum::class,
            'discount_type' => DiscountTypeEnum::class,
            'discount_value' => 'float',
            'priority' => 'integer',
            'min_order_quantity' => 'integer',
            'min_order_value' => 'float',
        ];
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(
            Customer::class,
            'ws_customer_group_assignments',
            'customer_group_id',
            'customer_id'
        )->withPivot(['assigned_at', 'expires_at'])->withTimestamps();
    }

    public function calculateDiscount(float $originalPrice): float
    {
        if ($this->discount_type->getValue() === DiscountTypeEnum::PERCENTAGE) {
            return $originalPrice * ($this->discount_value / 100);
        }

        return min($this->discount_value, $originalPrice);
    }

    public function calculateFinalPrice(float $originalPrice): float
    {
        return $originalPrice - $this->calculateDiscount($originalPrice);
    }
}
