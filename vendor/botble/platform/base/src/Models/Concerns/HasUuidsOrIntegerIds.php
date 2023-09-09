<?php

namespace Botble\Base\Models\Concerns;

use Illuminate\Support\Str;

trait HasUuidsOrIntegerIds
{
    public static function bootHasUuidsOrIntegerIds(): void
    {
        static::creating(static function (self $model): void {
            if (! self::determineIfUsingUuidsForId()) {
                return;
            }

            $model->{$model->getKeyName()} = $model->newUniqueId();
        });
    }

    public function newUniqueId(): string
    {
        return (string) Str::orderedUuid();
    }

    public function getKeyType(): string
    {
        if (self::determineIfUsingUuidsForId()) {
            return 'string';
        }

        return $this->keyType;
    }

    public function getIncrementing(): bool
    {
        if (self::determineIfUsingUuidsForId()) {
            return false;
        }

        return $this->incrementing;
    }

    public static function determineIfUsingUuidsForId(): bool
    {
        return (bool)config('core.base.general.using_uuids_for_id', false);
    }

    public static function determineIfUsingUlidsForId(): bool
    {
        return (bool)config('core.base.general.using_ulids_for_id', false);
    }

    public static function getTypeOfId(): string
    {
        if (self::determineIfUsingUuidsForId()) {
            return 'UUID';
        }

        if (self::determineIfUsingUlidsForId()) {
            return 'ULID';
        }

        return strtoupper(config('core.base.general.type_id', 'BIGINT'));
    }
}
