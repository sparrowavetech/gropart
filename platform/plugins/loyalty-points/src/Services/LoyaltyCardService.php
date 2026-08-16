<?php

namespace Botble\LoyaltyPoints\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Support\Facades\Cache;

class LoyaltyCardService
{
    // v2: bumped when the member token became permanent, to drop QR codes
    // cached with the old month-rotating token.
    protected const QR_CACHE_PREFIX = 'loyalty_qr_v2_';

    protected const CACHE_TTL_DAYS = 7;

    public function getQrCodeSvg(Customer $customer): string
    {
        $cacheKey = self::QR_CACHE_PREFIX . $customer->id;

        return Cache::remember(
            $cacheKey,
            now()->addDays(self::CACHE_TTL_DAYS),
            fn () => $this->generateQrCode($customer)
        );
    }

    public function generateQrCode(Customer $customer): string
    {
        $content = $this->buildQrContent($customer);

        $renderer = new ImageRenderer(
            new RendererStyle(200, 2),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($content);
    }

    public function buildQrContent(Customer $customer): string
    {
        $token = $this->generateMemberToken($customer->id);

        return route('public.loyalty-points.member', ['token' => $token]);
    }

    /**
     * The token is signed with the customer ID only, never with a timestamp.
     * Loyalty cards are printed (PVC membership cards) and must keep scanning
     * for years, so the signature must never rotate.
     */
    public function generateMemberToken(int|string $customerId): string
    {
        return base64_encode($customerId . ':' . $this->signCustomerId($customerId));
    }

    public function validateMemberToken(string $token): int|string|null
    {
        $decoded = base64_decode($token, true);

        if (! $decoded || ! str_contains($decoded, ':')) {
            return null;
        }

        [$customerId, $hash] = explode(':', $decoded, 2);

        if (
            ! hash_equals($this->signCustomerId($customerId), $hash)
            && ! $this->isLegacyRotatingHash($customerId, $hash)
        ) {
            return null;
        }

        return is_numeric($customerId) ? (int) $customerId : $customerId;
    }

    protected function signCustomerId(int|string $customerId): string
    {
        return hash_hmac('sha256', (string) $customerId, config('app.key'));
    }

    /**
     * Cards issued before the permanent token existed were signed with a
     * month-rotating hash valid for the current and previous month only.
     * Keep accepting those so already-downloaded cards do not break.
     */
    protected function isLegacyRotatingHash(int|string $customerId, string $hash): bool
    {
        $secret = config('app.key');

        foreach ([now()->format('Y-m'), now()->subMonth()->format('Y-m')] as $period) {
            if (hash_equals(hash_hmac('sha256', $customerId . '|' . $period, $secret), $hash)) {
                return true;
            }
        }

        return false;
    }

    public function generateSecurityHash(int|string $customerId): string
    {
        $secret = config('app.key');
        $hash = hash_hmac('sha256', (string) $customerId, $secret);

        return substr($hash, 0, 8);
    }

    public function validateQrContent(string $content): int|string|null
    {
        // Support both integer IDs and UUIDs
        if (! preg_match('/^LOYALTY:([\w-]+):([a-f0-9]{8})$/', $content, $matches)) {
            return null;
        }

        $customerId = $matches[1];
        $hash = $matches[2];

        if ($hash !== $this->generateSecurityHash($customerId)) {
            return null;
        }

        // Return as integer if numeric, otherwise as string (UUID)
        return is_numeric($customerId) ? (int) $customerId : $customerId;
    }

    public function clearCache(Customer $customer): void
    {
        Cache::forget(self::QR_CACHE_PREFIX . $customer->id);
    }
}
