<?php

namespace Ashikul\IndiaSmsGateway\Models;

use Botble\Base\Models\BaseModel;

class SmsLog extends BaseModel
{
    protected $table = 'india_sms_logs';

    protected $fillable = [
        'uuid', 'gateway', 'recipient', 'sender_id', 'message', 'message_type', 'segments',
        'status', 'provider_message_id', 'error_code', 'error_message', 'request_payload',
        'response_payload', 'metadata', 'queued_at', 'sent_at', 'delivered_at', 'failed_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'metadata' => 'array',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
