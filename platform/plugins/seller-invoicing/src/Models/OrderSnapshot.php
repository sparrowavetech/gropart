<?php

namespace SparroWave\SellerInvoicing\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderSnapshot extends BaseModel
{
    protected $table = 'si_order_snapshots';

    protected $guarded = ['id'];

    protected $casts = [
        'amounts' => 'array',
        'issuer' => 'array',
        'recipient' => 'array',
        'evidence' => 'array',
    ];

    public function document(): HasOne
    {
        return $this->hasOne(Document::class, 'snapshot_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'seller_id');
    }
}
