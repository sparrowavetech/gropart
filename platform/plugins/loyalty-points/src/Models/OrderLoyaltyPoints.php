<?php

namespace Botble\LoyaltyPoints\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLoyaltyPoints extends BaseModel
{
    protected $table = 'ec_order_loyalty_points';

    protected $fillable = [
        'order_id',
        'customer_id',
        'points_redeemed',
        'discount_amount',
        'points_to_earn',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'customer_id' => 'integer',
        'points_redeemed' => 'integer',
        'discount_amount' => 'decimal:2',
        'points_to_earn' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
