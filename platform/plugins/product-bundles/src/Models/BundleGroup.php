<?php

namespace Botble\ProductBundles\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BundleGroup extends BaseModel
{
    protected $table = 'product_bundle_groups';

    protected $fillable = [
        'bundle_id',
        'name',
        'choose_min',
        'choose_max',
        'sort_order',
    ];

    protected $casts = [
        'choose_min' => 'int',
        'choose_max' => 'int',
        'sort_order' => 'int',
    ];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BundleGroupItem::class, 'group_id')->orderBy('sort_order');
    }
}
