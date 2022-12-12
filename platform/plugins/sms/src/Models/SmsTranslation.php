<?php

namespace Botble\Sms\Models;

use Botble\Base\Models\BaseModel;

class SmsTranslation extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms_translations';

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'sms_id',
        'name',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
