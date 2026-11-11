<?php

namespace SparroWave\RazorpayEasyEmi\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SparroWave\RazorpayEasyEmi\Enums\EmiRequestStatusEnum;
use SparroWave\RazorpayEasyEmi\Enums\EmiSchemeEnum;
use SparroWave\RazorpayEasyEmi\Enums\SubventionPayerEnum;

class RazorpayEmiVendorRequest extends BaseModel
{
    protected $table = 'razorpay_emi_vendor_requests';

    protected $fillable = [
        'product_id',
        'store_id',
        'requested_scheme',
        'subvention_willingness',
        'notes',
        'status',
        'admin_remarks',
        'approved_offer_id',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'requested_scheme' => EmiSchemeEnum::class,
        'subvention_willingness' => SubventionPayerEnum::class,
        'status' => EmiRequestStatusEnum::class,
        'reviewed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function store(): BelongsTo
    {
        $storeClass = '\Botble\Marketplace\Models\Store';
        if (class_exists($storeClass)) {
            return $this->belongsTo($storeClass, 'store_id');
        }

        return $this->belongsTo(Product::class, 'store_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

