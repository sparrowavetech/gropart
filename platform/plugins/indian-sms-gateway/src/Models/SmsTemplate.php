<?php

namespace Ashikul\IndiaSmsGateway\Models;

use Botble\Base\Models\BaseModel;

class SmsTemplate extends BaseModel
{
    protected $table = 'india_sms_templates';

    protected $fillable = ['name', 'key', 'language', 'content', 'gateway', 'sender_id', 'is_active', 'variables', 'gateway_template_ids', 'gateway_sender_ids'];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array',
        'gateway_template_ids' => 'array',
        'gateway_sender_ids' => 'array',
    ];
}
