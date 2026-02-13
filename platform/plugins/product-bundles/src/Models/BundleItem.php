<?php

namespace Botble\ProductBundles\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleItem extends BaseModel
{
    protected $table = 'product_bundle_items';

    protected $fillable = [
        'bundle_id',
        'product_id',
        'variation_id',
        'quantity',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'int',
        'sort_order' => 'int',
    ];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    public function product(): BelongsTo
    {
        $productClass = class_exists('Botble\\Ecommerce\\Models\\Product')
            ? 'Botble\\Ecommerce\\Models\\Product'
            : 'App\\Models\\Product';

        return $this->belongsTo($productClass, 'product_id');
    }
}
