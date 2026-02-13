<?php

namespace Botble\ContentInjector\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class ContentInjector extends BaseModel
{
    protected $table = 'content_injectors';

    protected $fillable = [
        'name',
        'value',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];
}
