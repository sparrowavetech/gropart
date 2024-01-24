<?php

namespace Skillcraft\ContactManager\Models;

use Skillcraft\Core\Models\CoreModel as BaseModel;
use Botble\Base\Casts\SafeContent;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Skillcraft\ContactManager\Enums\ContactDataTypeEnum;

/**
 * @method static \Skillcraft\Base\Models\BaseQueryBuilder<static> query()
 */
class ContactAddress extends BaseModel
{
    use SoftDeletes;

    protected $table = 'contact_addresses';

    protected $fillable = [
        'contact_id',
        'address',
        'address2',
        'city',
        'state',
        'postalcode',
        'type',
    ];

    protected $casts = [
        'type' => ContactDataTypeEnum::class,
        'address' => SafeContent::class,
        'address2' => SafeContent::class,
        'city' => SafeContent::class,
        'state' => SafeContent::class,
        'postalcode' => SafeContent::class
    ];

    public function modelInstallSchema(): void
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->index()
                ->onDelete('cascade');
            $table->text('address')->nullable();
            $table->text('address2')->nullable();
            $table->text('city')->nullable();
            $table->text('state')->nullable();
            $table->text('postalcode')->nullable();
            $table->string('type', 60)->default(ContactDataTypeEnum::HOME);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function contact():BelongsTo
    {
        return $this->belongsTo(ContactManager::class);
    }
}
