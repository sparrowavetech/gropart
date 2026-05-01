<?php

namespace Botble\LoyaltyPoints\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerPointBalance extends BaseModel
{
    protected $table = 'ec_customer_points_balances';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'total_points',
        'lifetime_points',
        'level_id',
        'level_updated_at',
        'updated_at',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'lifetime_points' => 'integer',
        'level_updated_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(LoyaltyLevel::class, 'level_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'customer_id', 'customer_id');
    }

    public function addPoints(int $points): void
    {
        $this->increment('total_points', $points);
        $this->touch();
    }

    public function deductPoints(int $points): void
    {
        $this->decrement('total_points', $points);
        $this->touch();
    }

    public function hasEnoughPoints(int $points): bool
    {
        return $this->total_points >= $points;
    }
}
