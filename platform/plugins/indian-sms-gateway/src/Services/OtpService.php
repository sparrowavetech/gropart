<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\Models\SmsOtp;
use Ashikul\IndiaSmsGateway\Models\VerifiedPhone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class OtpService
{
    public function __construct(
        private PhoneNormalizer $phones,
        private RateLimitService $limits,
        private TemplateRenderer $templates,
        private SmsManager $sms,
        private SettingsRepository $settings,
        private DatabaseInstaller $database,
    ) {
    }

    public function request(string $phone, string $purpose = 'otp', string $ip = 'unknown', array $metadata = []): SmsOtp
    {
        $this->database->ensure();
        if (! $this->settings->bool('otp_enabled', true)) {
            throw new RuntimeException('OTP service is disabled.');
        }

        $phone = $this->phones->normalize($phone);
        $phoneHash = hash('sha256', $phone);

        $latest = SmsOtp::query()
            ->where('phone_hash', $phoneHash)
            ->where('purpose', $purpose)
            ->whereIn('status', ['pending', 'verified'])
            ->latest('id')
            ->first();

        if ($latest?->resend_available_at?->isFuture()) {
            $seconds = max(1, now()->diffInSeconds($latest->resend_available_at, false));
            throw new RuntimeException("Please wait {$seconds} seconds before requesting another code.");
        }

        $this->limits->enforceOtp($phone, $ip);

        $length = max(4, min(8, (int) $this->settings->get('otp_length', 6)));
        $ttl = max(60, min(3600, (int) $this->settings->get('otp_ttl', 300)));
        $maxAttempts = max(1, min(20, (int) $this->settings->get('otp_max_attempts', 5)));
        $cooldown = max(10, min(3600, (int) $this->settings->get('otp_resend_cooldown', 60)));
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        // Requesting a new code invalidates both an older pending code and any
        // previously issued verification token for the same purpose.
        SmsOtp::query()
            ->where('phone_hash', $phoneHash)
            ->where('purpose', $purpose)
            ->whereIn('status', ['pending', 'verified'])
            ->update(['status' => 'replaced']);

        $otp = SmsOtp::query()->create([
            'uuid' => (string) Str::uuid(),
            'phone_hash' => $phoneHash,
            'phone_encrypted' => Crypt::encryptString($phone),
            'purpose' => $purpose,
            'token_hash' => Hash::make($code),
            'max_attempts' => $maxAttempts,
            'status' => 'pending',
            'request_ip_hash' => hash('sha256', $ip),
            'metadata' => $metadata,
            'resend_available_at' => now()->addSeconds($cooldown),
            'expires_at' => now()->addSeconds($ttl),
        ]);

        $templateKey = $purpose . '_otp';
        $templateVariables = [
            'customer_name' => trim((string) ($metadata['customer_name'] ?? 'Customer')) ?: 'Customer',
            'code' => $code,
            'expires_in' => (int) ceil($ttl / 60),
            'phone' => $phone,
        ];
        $fallback = $this->templates->render('otp', $templateVariables);
        $content = $this->templates->render($templateKey, $templateVariables, $fallback);

        $result = $this->sms->send(new SmsMessage(
            $phone,
            $content,
            type: 'otp',
            metadata: ['otp_uuid' => $otp->uuid, 'purpose' => $purpose, 'template_key' => $templateKey, 'template_variables' => $templateVariables],
        ));

        if (! $result->accepted) {
            $otp->update(['status' => 'send_failed']);
            throw new RuntimeException($result->errorMessage ?: 'OTP SMS could not be sent.');
        }

        return $otp;
    }

    public function verifyAndIssueToken(string $phone, string $code, string $purpose = 'otp'): ?string
    {
        $this->database->ensure();
        $phone = $this->phones->normalize($phone);
        $otp = SmsOtp::query()
            ->where('phone_hash', hash('sha256', $phone))
            ->where('purpose', $purpose)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if (! $otp || $otp->expires_at->isPast() || $otp->attempts >= $otp->max_attempts) {
            if ($otp) {
                $otp->update(['status' => $otp->expires_at->isPast() ? 'expired' : 'blocked']);
            }

            return null;
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->token_hash)) {
            if ($otp->fresh()->attempts >= $otp->max_attempts) {
                $otp->update(['status' => 'blocked']);
            }

            return null;
        }

        $token = Str::random(80);
        $tokenHash = hash('sha256', $token);
        $metadata = array_merge((array) $otp->metadata, ['verification_token_hash' => $tokenHash]);
        $updates = [
            'status' => 'verified',
            'verified_at' => now(),
            'metadata' => $metadata,
        ];

        if (Schema::hasColumn('india_sms_otps', 'verification_token_hash')) {
            $updates['verification_token_hash'] = $tokenHash;
        }

        $otp->update($updates);

        return $token;
    }

    public function verify(string $phone, string $code, string $purpose = 'otp'): bool
    {
        return $this->verifyAndIssueToken($phone, $code, $purpose) !== null;
    }

    public function validateToken(string $phone, string $token, string $purpose, bool $consume = false): bool
    {
        $this->database->ensure();
        if ($token === '') {
            return false;
        }

        $phone = $this->phones->normalize($phone);
        $query = SmsOtp::query()
            ->where('phone_hash', hash('sha256', $phone))
            ->where('purpose', $purpose)
            ->where('status', 'verified')
            ->where('expires_at', '>', now());

        if (Schema::hasColumn('india_sms_otps', 'consumed_at')) {
            $query->whereNull('consumed_at');
        }

        $otp = $query->latest('id')->first();
        $storedHash = $otp?->verification_token_hash ?: data_get($otp?->metadata, 'verification_token_hash');

        if (! $otp || ! is_string($storedHash) || $storedHash === '') {
            return false;
        }

        if (! hash_equals($storedHash, hash('sha256', $token))) {
            return false;
        }

        if ($consume) {
            $updates = ['status' => 'consumed'];

            if (Schema::hasColumn('india_sms_otps', 'consumed_at')) {
                $updates['consumed_at'] = now();
            }

            $otp->update($updates);
            $this->rememberVerified($phone, $purpose);
        }

        return true;
    }

    public function rememberVerified(string $phone, string $purpose, ?Model $subject = null): void
    {
        $phone = $this->phones->normalize($phone);
        $minutes = max(1, min(525600, (int) $this->settings->get('verification_remember_minutes', 1440)));

        $phoneHash = hash('sha256', $phone);
        Cache::put('india-sms:verified:' . $phoneHash . ':' . $purpose, true, now()->addMinutes($minutes));
        Cache::put('india-sms:verified:' . $phoneHash . ':any', true, now()->addMinutes($minutes));

        if (Schema::hasTable('india_sms_verified_phones')) {
            VerifiedPhone::query()->updateOrCreate(
                ['phone_hash' => $phoneHash, 'purpose' => $purpose],
                [
                    'phone_encrypted' => Crypt::encryptString($phone),
                    'subject_type' => $subject ? $subject::class : null,
                    'subject_id' => $subject?->getKey(),
                    'verified_at' => now(),
                    'expires_at' => now()->addMinutes($minutes),
                    'last_used_at' => now(),
                ],
            );
        }
    }

    public function recentlyVerified(string $phone, ?string $purpose = null): bool
    {
        $phone = $this->phones->normalize($phone);
        $phoneHash = hash('sha256', $phone);
        $cachePurpose = $purpose ?: 'any';

        if (Cache::get('india-sms:verified:' . $phoneHash . ':' . $cachePurpose) === true) {
            return true;
        }

        if (! Schema::hasTable('india_sms_verified_phones')) {
            return false;
        }

        $query = VerifiedPhone::query()
            ->where('phone_hash', $phoneHash)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        if ($purpose !== null) {
            $query->where('purpose', $purpose);
        }

        $record = $query->latest('verified_at')->first();

        if (! $record) {
            return false;
        }

        $record->update(['last_used_at' => now()]);

        return true;
    }
}
