<?php

namespace Botble\EcommerceWholesale\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\ProductVisibilityEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVisibility extends BaseModel
{
    protected $table = 'ws_product_visibility';

    protected $fillable = [
        'product_id',
        'visibility_type',
    ];

    protected function casts(): array
    {
        return [
            'visibility_type' => ProductVisibilityEnum::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function groupAccess(): HasMany
    {
        return $this->hasMany(ProductGroupAccess::class, 'product_id', 'product_id');
    }

    public function isPublic(): bool
    {
        return $this->visibility_type->getValue() === ProductVisibilityEnum::PRODUCT_PUBLIC;
    }

    public function isWholesaleOnly(): bool
    {
        return $this->visibility_type->getValue() === ProductVisibilityEnum::WHOLESALE_ONLY;
    }

    public function isSpecificGroups(): bool
    {
        return $this->visibility_type->getValue() === ProductVisibilityEnum::SPECIFIC_GROUPS;
    }
}
