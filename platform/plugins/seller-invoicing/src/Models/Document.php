<?php

namespace SparroWave\SellerInvoicing\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends BaseModel
{
    protected $table = 'si_documents';

    protected $fillable = [
        'snapshot_id',
        'seller_id',
        'order_id',
        'kind',
        'status',
        'number',
        'currency',
        'original_document_id',
        'lines',
        'taxes',
        'subtotal',
        'tax_amount',
        'grand_total',
        'pdf_path',
        'sha256',
        'issued_at',
    ];

    protected $casts = [
        'lines' => 'array',
        'taxes' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'issued_at' => 'datetime',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(OrderSnapshot::class, 'snapshot_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'seller_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('seller-invoicing.download', $this->getKey());
    }

    public function getPrintUrlAttribute(): string
    {
        return route('seller-invoicing.print', $this->getKey());
    }

    public function getVendorDownloadUrlAttribute(): string
    {
        return route('marketplace.vendor.seller-invoicing.download', $this->getKey());
    }

    public function getVendorPrintUrlAttribute(): string
    {
        return route('marketplace.vendor.seller-invoicing.print', $this->getKey());
    }
}
