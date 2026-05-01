<?php

namespace Botble\EcommerceWholesale\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMOQ extends BaseModel
{
    protected $table = 'ws_product_moq';

    protected $fillable = [
        'product_id',
        'customer_group_id',
        'min_quantity',
        'quantity_increment',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'quantity_increment' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function isValidQuantity(int $quantity): bool
    {
        if ($quantity < $this->min_quantity) {
            return false;
        }

        if ($this->quantity_increment > 1) {
            $remainder = ($quantity - $this->min_quantity) % $this->quantity_increment;

            return $remainder === 0;
        }

        return true;
    }

    public function getNextValidQuantity(int $quantity): int
    {
        if ($quantity < $this->min_quantity) {
            return $this->min_quantity;
        }

        if ($this->quantity_increment > 1) {
            $remainder = ($quantity - $this->min_quantity) % $this->quantity_increment;
            if ($remainder !== 0) {
                return $quantity - $remainder + $this->quantity_increment;
            }
        }

        return $quantity;
    }
}
