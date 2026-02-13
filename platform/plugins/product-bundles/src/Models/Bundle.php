<?php

namespace Botble\ProductBundles\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Bundle extends BaseModel
{
    protected $table = 'product_bundles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'ecommerce_product_id',
        'type',
        'is_active',
        'is_featured',
        'pricing_type',
        'pricing_value',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'is_featured' => 'bool',
        'ecommerce_product_id' => 'int',
        'pricing_value' => 'float',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $bundle) {
            $slug = trim((string) $bundle->slug);
            if ($slug === '') {
                $slug = Str::slug((string) $bundle->name);
            }

            // Ensure uniqueness (best-effort). We only append a short suffix if collision detected.
            if ($slug !== '') {
                $base = $slug;
                $i = 0;
                while (self::query()
                    ->where('slug', $slug)
                    ->when($bundle->exists, fn ($q) => $q->where('id', '!=', $bundle->id))
                    ->exists()
                ) {
                    $i++;
                    $slug = $base . '-' . $i;
                    if ($i > 50) {
                        $slug = $base . '-' . Str::lower(Str::random(6));
                        break;
                    }
                }
                $bundle->slug = $slug;
            }
        });
    }

    // IMPORTANT:
    // Use numeric ID for internal/admin routes.
    // Public bundle pages bind by slug explicitly via route parameter {bundle:slug}.

    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class, 'bundle_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(BundleGroup::class, 'bundle_id')->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        $productClass = class_exists('Botble\\Ecommerce\\Models\\Product')
            ? 'Botble\\Ecommerce\\Models\\Product'
            : 'App\\Models\\Product';

        return $this->belongsToMany($productClass, 'product_bundle_products', 'bundle_id', 'product_id');
    }

    public function isInDateRange(): bool
    {
        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }
}
