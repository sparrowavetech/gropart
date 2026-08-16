<?php

namespace Ashikul\IndiaSmsGateway\Models;

use Botble\Base\Models\BaseModel;

class SmsOtp extends BaseModel
{
    protected $table = 'india_sms_otps';

    protected $fillable = [
        'uuid',
        'phone_hash',
        'phone_encrypted',
        'purpose',
        'token_hash',
        'verification_token_hash',
        'attempts',
        'max_attempts',
        'status',
        'request_ip_hash',
        'metadata',
        'resend_available_at',
        'expires_at',
        'verified_at',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'resend_available_at' => 'datetime',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }
}
