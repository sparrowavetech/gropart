<?php

namespace Skillcraft\ContactManager\Models;

use Illuminate\Support\Str;
use Botble\Base\Casts\SafeContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Skillcraft\Core\Models\CoreModel as BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Skillcraft\ContactManager\Enums\ContactTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static \Skillcraft\Base\Models\BaseQueryBuilder<static> query()
 */
class ContactManager extends BaseModel
{
    use SoftDeletes;

    protected $table = 'contact_manager';

    protected $fillable = [
        'group_id',
        'first_name',
        'last_name',
        'business_name',
        'type',
        'source'
    ];

    protected $casts = [
        'name' => SafeContent::class,
        'type' => ContactTypeEnum::class,
        'source' => SafeContent::class,
        'business_name' => SafeContent::class
    ];

    protected static function booted()
    {
        parent::boot();
        
        static::deleted(function ($contact) {
            $contact->phones()->delete();
            $contact->emails()->delete();
            $contact->addresses()->delete();
        });
    }

    public function modelInstallSchema():void
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('first_name', 120)->nullable();
            $table->string('last_name', 120)->nullable();
            $table->string('business_name', 255)->nullable();
            $table->string('type', 60)->default(ContactTypeEnum::CUSTOMER);
            $table->string('source', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContactTag::class, 'contacts_tags', 'contact_id', 'tag_id');
    }

    public function group()
    {
        return $this->hasOne(ContactGroup::class, 'id', 'group_id');
    }

    public function addresses():HasMany
    {
        return $this->hasMany(ContactAddress::class, 'contact_id', 'id');
    }

    public function emails():HasMany
    {
        return $this->hasMany(ContactEmail::class, 'contact_id', 'id');
    }

    public function phones():HasMany
    {
        return $this->hasMany(ContactPhone::class, 'contact_id', 'id');
    }

    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => ucfirst($value),
            set: fn (?string $value) => strtolower($value),
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => ucfirst($value),
            set: fn (?string $value) => strtolower($value),
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->firstName . ' ' . $this->lastName,
        );
    }
    
    public function scopeHasType($query, ContactTypeEnum $type): Builder
    {
        return $query->where('type', $type->getKey());
    }

    public function scopeHasAddressInfo($query, string $key, string $operator, string $value): Builder
    {
        return  $query = $query->whereHas('addresses', function (Builder $query) use ($key, $operator, $value) {
            $query->where($key, $operator, $value);
        });
    }

    public function scopeHasEmailInfo($query, string $key, string $operator, string $value): Builder
    {
        return  $query = $query->whereHas('emails', function (Builder $query) use ($key, $operator, $value) {
            $query->where($key, $operator, $value);
        });
    }

    public function scopeHasPhoneInfo($query, string $key, string $operator, string $value): Builder
    {
        return  $query = $query->whereHas('phones', function (Builder $query) use ($key, $operator, $value) {
            $query->where($key, $operator, $value);
        });
    }

    public function scopeHasContactTag($query, string $operator, string $value): Builder
    {
        return $query->whereExists(function ($query) use ($operator, $value) {
            $query->select(DB::raw(1))
                  ->from('contacts_tags')
                  ->whereRaw('contact_manager.id = contacts_tags.contact_id')
                  ->where('contacts_tags.tag_id', $operator, $value);
        });
    }
}
