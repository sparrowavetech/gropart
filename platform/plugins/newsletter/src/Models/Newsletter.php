<?php

namespace Botble\Newsletter\Models;

use Botble\Base\Models\BaseModel;
use Botble\Newsletter\Enums\NewsletterStatusEnum;

class Newsletter extends BaseModel
{
    protected $table = 'newsletters';

    protected $fillable = [
        'email',
        'name',
        'status',
    ];

    protected $casts = [
        'status' => NewsletterStatusEnum::class,
    ];
}
