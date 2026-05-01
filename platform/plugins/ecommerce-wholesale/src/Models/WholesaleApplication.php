<?php

namespace Botble\EcommerceWholesale\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WholesaleApplication extends BaseModel
{
    protected $table = 'ws_wholesale_applications';

    protected $fillable = [
        'customer_id',
        'email',
        'name',
        'company_name',
        'tax_id',
        'phone',
        'business_type',
        'expected_volume',
        'notes',
        'status',
        'assigned_group_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatusEnum::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'assigned_group_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status->getValue() === ApplicationStatusEnum::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status->getValue() === ApplicationStatusEnum::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status->getValue() === ApplicationStatusEnum::REJECTED;
    }
}
