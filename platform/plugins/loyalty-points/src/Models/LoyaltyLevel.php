<?php

namespace Botble\LoyaltyPoints\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Support\HtmlString;

class LoyaltyLevel extends BaseModel
{
    protected $table = 'loyalty_levels';

    protected $fillable = [
        'name',
        'badge',
        'min_points',
        'max_points',
        'earning_rate',
        'benefits',
        'is_default',
        'status',
        'order',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'max_points' => 'integer',
        'earning_rate' => 'decimal:2',
        'is_default' => 'boolean',
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    public function toHtml(): HtmlString
    {
        return new HtmlString(sprintf(
            '<span class="badge bg-primary text-white">%s</span>',
            e($this->name)
        ));
    }
}
