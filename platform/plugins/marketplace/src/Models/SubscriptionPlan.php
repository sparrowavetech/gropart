<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Marketplace\Enums\SubscriptionDurationUnitEnum;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends BaseModel
{
    protected $table = 'mp_subscription_plans';

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_value',
        'duration_unit',
        'options',
        'is_default',
        'order',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'options' => 'array',
        'price' => 'float',
        'duration_value' => 'int',
        'order' => 'int',
        'is_default' => 'bool',
    ];

    /**
     * Decoded options merged over the defaults, memoised for the lifetime of the instance.
     * Reset whenever the underlying attribute changes.
     */
    protected ?array $resolvedOptions = null;

    /**
     * Every quota and feature flag a plan can carry. Adding a key here back-fills
     * every existing plan without a migration. -1 means unlimited.
     */
    public static function defaultOptions(): array
    {
        return [
            'product_limit' => -1,
            'featured_product_limit' => 0,
            'allow_digital_products' => 0,
            'allow_coupons' => 0,
            'allow_product_import' => 0,
            'listing_priority' => 0,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $plan): void {
            $plan->resolvedOptions = null;
        });

        static::saved(function (self $plan): void {
            // Only one plan may be the default, otherwise the lazy free-plan assignment
            // becomes non-deterministic.
            if ($plan->is_default) {
                static::query()
                    ->where('id', '!=', $plan->getKey())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class, 'subscription_plan_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->orderBy('order')
            ->orderBy('price');
    }

    public function getOptions(): array
    {
        if ($this->resolvedOptions === null) {
            $this->resolvedOptions = array_merge(static::defaultOptions(), (array) $this->options);
        }

        return $this->resolvedOptions;
    }

    public function getOption(string $key): int
    {
        $options = $this->getOptions();

        if (! array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('Unknown subscription plan option [%s].', $key));
        }

        return (int) $options[$key];
    }

    public function fillOptions(array $options): static
    {
        $this->options = array_intersect_key(
            array_map(fn ($value) => (int) $value, $options),
            static::defaultOptions()
        );

        $this->resolvedOptions = null;

        return $this;
    }

    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    public function isLifetime(): bool
    {
        return $this->duration_unit === SubscriptionDurationUnitEnum::LIFETIME;
    }

    /**
     * End of a period starting at $from. Null for lifetime plans.
     */
    public function calculateEndsAt(CarbonInterface $from): ?Carbon
    {
        if ($this->isLifetime()) {
            return null;
        }

        return SubscriptionDurationUnitEnum::addTo(
            $this->duration_unit,
            max(1, $this->duration_value),
            $from
        );
    }

    /**
     * The immutable copy stored on a subscription at purchase time.
     */
    public function toSnapshot(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'duration_value' => $this->duration_value,
            'duration_unit' => $this->duration_unit,
            'options' => $this->getOptions(),
        ];
    }
}
