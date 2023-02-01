<?php

namespace Botble\Pickrr\Models;

use Botble\Base\Models\BaseModel;

class PickrrTranslation extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pickrrs_translations';

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'pickrrs_id',
        'name',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
