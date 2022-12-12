<?php

namespace Botble\Sms\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Sms\Enums\SmsEnum;
use Botble\Base\Models\BaseModel;

class Sms extends BaseModel
{
    use EnumCastable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'template',
        'template_id',
        'status',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SmsEnum::class,
    ];
}
