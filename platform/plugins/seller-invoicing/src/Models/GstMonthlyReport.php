<?php

namespace SparroWave\SellerInvoicing\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GstMonthlyReport extends BaseModel
{
    protected $table = 'si_gst_monthly_reports';

    protected $fillable = [
        'seller_id',
        'store_id',
        'tax_period',
        'financial_year',
        'month',
        'year',
        'order_count',
        'b2b_count',
        'b2b_taxable',
        'b2b_tax',
        'b2cs_count',
        'b2cs_taxable',
        'b2cs_tax',
        'total_taxable_sales',
        'total_output_tax',
        'itc_gropart_fees',
        'net_gst_payable',
        'hsn_summary',
        'gstr1_payload',
        'gstr3b_payload',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'order_count' => 'integer',
        'b2b_count' => 'integer',
        'b2b_taxable' => 'decimal:2',
        'b2b_tax' => 'decimal:2',
        'b2cs_count' => 'integer',
        'b2cs_taxable' => 'decimal:2',
        'b2cs_tax' => 'decimal:2',
        'total_taxable_sales' => 'decimal:2',
        'total_output_tax' => 'decimal:2',
        'itc_gropart_fees' => 'decimal:2',
        'net_gst_payable' => 'decimal:2',
        'hsn_summary' => 'array',
        'gstr1_payload' => 'array',
        'gstr3b_payload' => 'array',
        'generated_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'seller_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function getFormattedPeriodAttribute(): string
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1);

        return $date ? $date->format('F Y') : $this->tax_period;
    }
}

