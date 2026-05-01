<?php

namespace Botble\LoyaltyPoints\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends BaseModel
{
    protected $table = 'ec_customer_points_transactions';

    public $timestamps = false;

    public const UPDATED_AT = null;

    protected $fillable = [
        'customer_id',
        'order_id',
        'type',
        'points',
        'note',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'order_id' => 'integer',
        'points' => 'integer',
        'type' => TransactionTypeEnum::class,
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const TYPE_EARN = 'earn';
    public const TYPE_REDEEM = 'redeem';
    public const TYPE_ADJUST = 'adjust';
    public const TYPE_REVERSE = 'reverse';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_EARN => trans('plugins/loyalty-points::loyalty-points.transaction_types.earn'),
            self::TYPE_REDEEM => trans('plugins/loyalty-points::loyalty-points.transaction_types.redeem'),
            self::TYPE_ADJUST => trans('plugins/loyalty-points::loyalty-points.transaction_types.adjust'),
            self::TYPE_REVERSE => trans('plugins/loyalty-points::loyalty-points.transaction_types.reverse'),
        ];
    }

    public function getFormattedPointsAttribute(): string
    {
        return ($this->points > 0 ? '+' : '') . number_format($this->points);
    }

    public function getTypeLabel(): string
    {
        return $this->type->label();
    }
}
