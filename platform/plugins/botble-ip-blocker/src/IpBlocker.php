<?php

namespace ArchiElite\IpBlocker;

use Botble\Base\Supports\Helper;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class IpBlocker
{
    public static function getSettingKey(string $name): string
    {
        return "ip_blocker_$name";
    }

    public static function getSettings(string $key = null, $default = null): array|string|null
    {
        $settings = [
            'ip' => self::getSetting('addresses'),
            'ip_range' => self::getSetting('addresses_range'),
            'allowed_countries' => self::getSetting('available_countries'),
            'secret_key' => self::getSetting('secret_key'),
            'rate_limits_at' => self::getSetting('rate_limits_at'),
        ];

        if ($key) {
            return Arr::get($settings, $key, $default);
        }

        return $settings;
    }

    public static function getSetting(string $name, mixed $default = null): mixed
    {
        return setting(self::getSettingKey($name), $default);
    }

    public static function setSetting(string $name, mixed $value = null, bool $saveNow = false): void
    {
        setting()->set(self::getSettingKey($name), $value);

        if ($saveNow) {
            self::saveSetting();
        }
    }

    public static function saveSetting(): void
    {
        setting()->save();
    }

    public static function checkwildcardIPAddress(): bool
    {
        $ipRange = self::getSettings('ip_range');

        if (! $ipRange) {
            return true;
        }

        $ipRange = json_decode($ipRange, true);

        if (! $ipRange) {
            return true;
        }

        $clientIp = request()->ip();

        $explodeClientIp = explode('.', $clientIp);

        $formatClientIp = implode('.', [
            $explodeClientIp[0],
            $explodeClientIp[1],
        ]);

        foreach ($ipRange as $ip) {
            if (str_starts_with($formatClientIp, substr($ip, 0, -2))) {
                return false;
            }
        }

        return true;
    }

    public static function checkIpsByCountryCode(): bool
    {
        $systemCountriesCode = array_keys(Helper::countries());

        $allowedCountries = self::getSettings('allowed_countries', []);

        if (! $allowedCountries) {
            return true;
        }

        $allowedCountries = json_decode($allowedCountries, true);

        if (! $allowedCountries) {
            return true;
        }

        if (empty(array_diff($systemCountriesCode, $allowedCountries))) {
            return true;
        }

        $sessionKey = 'ip_blocker_response_cache_' . md5(json_encode(self::getSettings()));

        if (Session::has($sessionKey)) {
            return Session::get($sessionKey);
        }

        $response = self::callAPI();

        if (! $response) {
            Session::put($sessionKey, true);

            return true;
        }

        $isBlocked = in_array($response['country'], $allowedCountries, true);

        Session::put($sessionKey, $isBlocked);

        return $isBlocked;
    }

    public static function callAPI(): array
    {
        $secretKey = self::getSettings('secret_key');

        if (! $secretKey) {
            return [];
        }

        $cacheKey = 'ip_blocker_cache_responses_' . md5(json_encode(self::getSettings()));

        if (Session::has($cacheKey) && $data = Session::get($cacheKey)) {
            return $data;
        }

        if ($rateLimitAt = self::getSettings('rate_limits_at')) {
            $rateLimitDateTime = Carbon::parse($rateLimitAt);

            $firstDayOfMonth = $rateLimitDateTime->firstOfMonth();
            $lastDayOfMonth = $rateLimitDateTime->clone()->lastOfMonth();

            if (! Carbon::now()->between($firstDayOfMonth, $lastDayOfMonth)) {
                self::setSetting('rate_limits_at', null, true);
                $rateLimitAt = null;
            }
        }

        if ($rateLimitAt) {
            return [
                'hasRateLimit' => true,
            ];
        }

        $response = self::checkApiResponse($secretKey);

        if ($response->json('status') === 429) {
            self::setSetting('rate_limits_at', Carbon::now()->toIso8601String(), true);
        }

        if ($response->failed()) {
            return [];
        }

        $data = $response->json();

        Session::put($cacheKey, $data);

        return $data;
    }

    public static function checkApiResponse(string $secretKey = null): Response
    {
        return Http::withoutVerifying()->asJson()->get("https://ipinfo.io?token=$secretKey");
    }
}
