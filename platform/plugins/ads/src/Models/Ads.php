<?php

namespace Botble\Ads\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Traits\EnumCastable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Ads extends BaseModel
{
    use EnumCastable;

    protected $table = 'ads';

    protected $fillable = [
        'name',
        'key',
        'status',
        'expired_at',
        'location',
        'image',
        'url',
        'clicked',
        'order',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'expired_at' => 'datetime',
    ];

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->whereDate('expired_at', '>=', Carbon::now()->toDateString());
        });
    }

    public function getExpiredAtAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->format('m/d/Y');
    }
}
