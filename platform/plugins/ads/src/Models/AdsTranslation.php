<?php

namespace Botble\Ads\Models;

use Botble\Base\Models\BaseModel;

class AdsTranslation extends BaseModel
{
    protected $table = 'ads_translations';

    protected $fillable = [
        'lang_code',
        'ads_id',
        'name',
        'image',
        'url',
    ];

    public $timestamps = false;
}
