<?php

namespace Skillcraft\ContactManager\Models;

use Illuminate\Support\Str;
use Skillcraft\Core\Models\CoreModel as BaseModel;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;
use Botble\Base\Models\BaseQueryBuilder;

/**
 * @method static BaseQueryBuilder<static> query()
 */
class ContactGroup extends BaseModel
{
    use SoftDeletes;

    protected $table = 'contact_groups';

    protected $fillable = [
        'uuid',
        'name',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
    ];

    protected static function booted():void
    {
        parent::booted();

        static::creating(function ($group) {
            $group->uuid = (string) Str::orderedUuid();
        });
    }

    public function modelInstallSchema(): void
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name', 120)->nullable();
            $table->string('status', 60)->default(BaseStatusEnum::PUBLISHED());
            $table->timestamps();
            $table->softDeletes();
        });
    }

}
