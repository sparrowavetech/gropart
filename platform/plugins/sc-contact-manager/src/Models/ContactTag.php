<?php

namespace Skillcraft\ContactManager\Models;

use Botble\ACL\Models\User;
use Skillcraft\Core\Models\CoreModel as BaseModel;
use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Botble\Base\Models\BaseQueryBuilder;

/**
 * @method static BaseQueryBuilder<static> query()
 */
class ContactTag extends BaseModel
{
    protected $table = 'contact_tags';

    protected $fillable = [
        'name',
        'description',
        'status',
        'author_id',
        'author_type',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'description' => SafeContent::class,
    ];

    public function modelInstallSchema(): void
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->integer('author_id');
            $table->string('author_type', 255)->default(addslashes(User::class));
            $table->string('description', 400)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(ContactManager::class, 'contacts_tags', 'id', 'contact_id');
    }

    protected static function boot()
    {
        parent::boot();

        self::deleting(function (ContactTag $tag) {
            $tag->contacts()->detach();
        });
    }
}
