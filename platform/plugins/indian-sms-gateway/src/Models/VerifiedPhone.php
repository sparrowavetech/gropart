<?php

namespace Ashikul\IndiaSmsGateway\Models;

use Botble\Base\Models\BaseModel;

class VerifiedPhone extends BaseModel
{
    protected $table = 'india_sms_verified_phones';

    protected $fillable = [
        'phone_hash',
        'phone_encrypted',
        'purpose',
        'subject_type',
        'subject_id',
        'verified_at',
        'expires_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }
}
