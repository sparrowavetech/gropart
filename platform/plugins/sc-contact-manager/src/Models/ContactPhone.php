<?php

namespace Skillcraft\ContactManager\Models;

use Skillcraft\Core\Models\CoreModel as BaseModel;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Illuminate\Support\Facades\Schema;
use Botble\Base\Supports\Database\Blueprint;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Skillcraft\ContactManager\Enums\ContactDataTypeEnum;

/**
 * @method static \Skillcraft\Base\Models\BaseQueryBuilder<static> query()
 */
class ContactPhone extends BaseModel
{
    protected $table = 'contact_phones';

    protected $fillable = [
        'contact_id',
        'phone',
        'type',
    ];

    protected $casts = [
        'type' => ContactDataTypeEnum::class,
        'phone' => SafeContent::class,
    ];

    public function modelInstallSchema(): void
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->index()
                ->onDelete('cascade');
            $table->string('phone', 120)->nullable();
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
