<?php

namespace Botble\Ecommerce\Models;

use Botble\Location\Models\City;
use Botble\Base\Models\BaseModel;
use Botble\Location\Models\State;
use Botble\Location\Models\Country;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return $this->belongsTo(Product::class,'product_id');
    }
    /**
     * @return BelongsTo
     */
    public function cityName(): BelongsTo
    {
        return $this->belongsTo(City::class,'city')->withDefault();
    }
    /**
     * @return BelongsTo
     */
    public function stateName(): BelongsTo
    {
        return $this->belongsTo(State::class,'state')->withDefault();
    }
    /**
     * @return BelongsTo
     */
    public function countryName(): BelongsTo
    {
        return $this->belongsTo(Country::class,'country')->withDefault();
    }
}
