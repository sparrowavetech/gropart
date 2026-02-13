<?php

namespace Botble\ProductBundles\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleGroupItem extends BaseModel
{
    protected $table = 'product_bundle_group_items';

    protected $fillable = [
        'group_id',
        'product_id',
        'variation_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'int',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(BundleGroup::class, 'group_id');
    }

    public function product(): BelongsTo
    {
        $productClass = class_exists('Botble\\Ecommerce\\Models\\Product')
            ? 'Botble\\Ecommerce\\Models\\Product'
            : 'App\\Models\\Product';

        return $this->belongsTo($productClass, 'product_id');
    }
}
