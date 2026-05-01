<?php

namespace Botble\EcommerceWholesale\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductGroupAccess extends BaseModel
{
    protected $table = 'ws_product_group_access';

    protected $fillable = [
        'product_id',
        'customer_group_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
