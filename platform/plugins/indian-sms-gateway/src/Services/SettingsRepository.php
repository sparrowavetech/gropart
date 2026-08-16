<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Illuminate\Support\Facades\Crypt;
use Throwable;

class SettingsRepository
{
    public function get(string $key, mixed $default = null): mixed
    {
        return setting('india_sms_' . $key, $default);
    }

    public function bool(string $key, bool $default = false): bool
    {
        return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function set(array $values): void
    {
        // In current Botble versions the setting() helper only accepts a
        // single string key. Calling setting($array) causes a TypeError.
        // Calling it without arguments returns the underlying SettingStore.
        $store = setting();

        foreach ($values as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $store->set('india_sms_' . $key, $value);
        }

        $store->save();
    }

    public function secret(string $key, ?string $default = null): ?string
    {
        $value = $this->get('secret_' . $key);
        if (! is_string($value) || $value === '') {
            return $default;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }

    public function setSecret(string $key, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }

        $this->set(['secret_' . $key => Crypt::encryptString(trim($value))]);
    }
}
