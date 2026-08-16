<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

class RateLimitService
{
    public function enforceSms(string $phone): void
    {
        $perHour = (int) setting('india_sms_max_per_recipient_hour', 20);
        $perMinute = (int) setting('india_sms_global_per_minute', 100);
        $this->hit('india-sms:phone:' . hash('sha256', $phone) . ':' . now()->format('YmdH'), $perHour, 3600, 'Recipient hourly SMS limit exceeded.');
        $this->hit('india-sms:global:' . now()->format('YmdHi'), $perMinute, 120, 'Global SMS rate limit exceeded.');
    }

    public function enforceOtp(string $phone, string $ip): void
    {
        $phoneLimit = (int) setting('india_sms_otp_requests_phone_hour', 5);
        $ipLimit = (int) setting('india_sms_otp_requests_ip_hour', 20);
        $this->hit('india-sms:otp:phone:' . hash('sha256', $phone) . ':' . now()->format('YmdH'), $phoneLimit, 3600, 'Too many OTP requests for this phone number.');
        $this->hit('india-sms:otp:ip:' . hash('sha256', $ip) . ':' . now()->format('YmdH'), $ipLimit, 3600, 'Too many OTP requests from this network.');
    }

    private function hit(string $key, int $limit, int $ttl, string $message): void
    {
        if ($limit <= 0) { return; }
        if (! Cache::has($key)) { Cache::put($key, 0, $ttl); }
        if ((int) Cache::increment($key) > $limit) { throw new RuntimeException($message); }
    }
}
