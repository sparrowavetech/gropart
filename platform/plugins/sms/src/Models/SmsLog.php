<?php

namespace Botble\Sms\Models;

use Botble\Base\Models\BaseModel;

class SmsLog extends BaseModel
{
    protected $table = 'sms_logs';

    protected $fillable = [
        'job_id',
        'message_id',
        'template',
        'template_id',
        'recipient',
        'message',
        'status',
        'sent_at',
        'delivered_at',
        'delivery_error_code',
        'cost',
        'op_cr',
        'response_payload',
        'delivery_payload',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'response_payload' => 'array',
        'delivery_payload' => 'array',
        'cost' => 'float',
    ];
}
