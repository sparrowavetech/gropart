<?php

namespace Ashikul\IndiaSmsGateway\Models;

use Botble\Base\Models\BaseModel;

class WebhookEvent extends BaseModel
{
    protected $table = 'india_sms_webhook_events';

    protected $fillable = ['gateway', 'event_id', 'provider_message_id', 'status', 'payload', 'processed_at'];

    protected $casts = ['payload' => 'array', 'processed_at' => 'datetime'];
}
