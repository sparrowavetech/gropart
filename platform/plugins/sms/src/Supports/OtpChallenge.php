<?php

namespace Botble\Sms\Supports;

use Botble\Ecommerce\Models\Customer;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OtpChallenge
{
    private const LIFETIME_SECONDS = 600;
    private const MAX_SENDS = 3;
    private const MAX_ATTEMPTS = 5;

    private function key(string $purpose): string
    {
        return 'sms.otp.' . $purpose;
    }

    private function limitKey(string $action, string $purpose, Customer $customer): string
    {
        return 'sms:otp:' . $action . ':' . $purpose . ':' . $customer->getKey();
    }

    public function belongs(string $purpose, Customer $customer): bool
    {
        return $this->customerId($purpose) === (int) $customer->getKey();
    }

    public function customerId(string $purpose): ?int
    {
        $challenge = session($this->key($purpose));
        if (! is_array($challenge) || (int) ($challenge['expires_at'] ?? 0) <= time()) {
            return null;
        }

        $id = (int) ($challenge['customer_id'] ?? 0);

        return $id > 0 ? $id : null;
    }

    public function begin(string $purpose, Customer $customer, string $otp): void
    {
        $key = $this->limitKey('send', $purpose, $customer);
        if (RateLimiter::tooManyAttempts($key, self::MAX_SENDS)) {
            throw new HttpException(429);
        }
        RateLimiter::hit($key, self::LIFETIME_SECONDS);

        session()->put($this->key($purpose), [
            'customer_id' => (int) $customer->getKey(),
            'hash' => hash_hmac('sha256', $otp, (string) config('app.key')),
            'expires_at' => time() + self::LIFETIME_SECONDS,
        ]);
    }

    public function verify(string $purpose, Customer $customer, string $otp): bool
    {
        $key = $this->limitKey('verify', $purpose, $customer);
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new HttpException(429);
        }
        $challenge = session($this->key($purpose));

        if (! $this->belongs($purpose, $customer)
            || ! hash_equals((string) ($challenge['hash'] ?? ''), hash_hmac('sha256', $otp, (string) config('app.key')))) {
            RateLimiter::hit($key, self::LIFETIME_SECONDS);

            return false;
        }

        $this->clear($purpose);
        RateLimiter::clear($key);

        return true;
    }

    public function clear(string $purpose): void
    {
        session()->forget($this->key($purpose));
    }

    public function restore(string $purpose, ?array $previous): void
    {
        if ($previous) {
            session()->put($this->key($purpose), $previous);

            return;
        }

        $this->clear($purpose);
    }
}
