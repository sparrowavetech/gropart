<?php
namespace SparroWave\EnquiryProducts\Models;
use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SparroWave\EnquiryProducts\Support\Catalog;

class Enquiry extends BaseModel
{
    protected $table = 'ec_product_enquiry';
    protected $guarded = ['id'];
    public const STATUSES = ['pending', 'contacted', 'not_available', 'rejected'];
    protected static function booted(): void
    {
        static::created(function (self $enquiry): void {
            if (! $enquiry->code) {
                $enquiry->code = (string) setting('ep_reference_prefix', '#GPEQ-') . $enquiry->id . (string) setting('ep_reference_suffix', '');
                $enquiry->saveQuietly();
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope(Catalog::SCOPE);
    }
}
