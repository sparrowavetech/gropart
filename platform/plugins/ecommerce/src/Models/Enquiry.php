<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;

class Enquiry extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ec_product_enquiry';

    /**
     * @var array
     */
    protected $fillable = [
        'product_id',
        'name',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'address',
        'zip_code',
        'address',
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
