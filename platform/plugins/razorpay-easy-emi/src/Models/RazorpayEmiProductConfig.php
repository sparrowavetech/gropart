<?php

namespace SparroWave\RazorpayEasyEmi\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SparroWave\RazorpayEasyEmi\Enums\EmiSchemeEnum;
use SparroWave\RazorpayEasyEmi\Enums\SubventionPayerEnum;

class RazorpayEmiProductConfig extends BaseModel
{
    protected $table = 'razorpay_emi_product_configs';

    protected $fillable = [
        'product_id',
        'status',
        'emi_type',
        'offer_mode',
        'offer_id',
        'subvention_payer',
        'custom_badge',
        'min_amount',
    ];

    protected $casts = [
        'emi_type' => EmiSchemeEnum::class,
        'subvention_payer' => SubventionPayerEnum::class,
        'min_amount' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getOfferIdsAttribute(): array
    {
        if (empty($this->offer_id)) {
            return [];
        }

        $raw = (string) $this->offer_id;
        if (str_starts_with($raw, '[') && str_ends_with($raw, ']')) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    public function setOfferIdsAttribute(array|string|null $value): void
    {
        if (is_array($value)) {
            $this->attributes['offer_id'] = json_encode(array_values(array_filter($value)));
        } else {
            $this->attributes['offer_id'] = $value;
        }
    }
}
