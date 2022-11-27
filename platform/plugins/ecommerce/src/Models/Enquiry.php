<?php

namespace Botble\Ecommerce\Models;

use Botble\Location\Models\City;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Enums\EnquiryStatusEnum;
use Botble\Base\Traits\EnumCastable;
use Botble\Location\Models\State;
use Botble\Location\Models\Country;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends BaseModel
{
    use EnumCastable;
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
        'status',
        'description',
        'attachment'
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function (Enquiry $enquiry) {
            $enquiry->code = static::generateUniqueCode();
        });
    }
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
     * @var array
     */
    protected $casts = [
        'status' => EnquiryStatusEnum::class,
    ];

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
    public static function generateUniqueCode(): string
    {
        $nextInsertId = static::query()->max('id') + 1;

        do {
            $code = get_enquiry_code($nextInsertId);
            $nextInsertId++;
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }
}
