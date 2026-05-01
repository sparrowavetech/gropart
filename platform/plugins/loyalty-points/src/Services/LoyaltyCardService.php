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
    protected const QR_CACHE_PREFIX = 'loyalty_qr_';

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

    public function generateMemberToken(int|string $customerId): string
    {
        $secret = config('app.key');
        $data = $customerId . '|' . now()->format('Y-m');

        return base64_encode($customerId . ':' . hash_hmac('sha256', $data, $secret));
    }

    public function validateMemberToken(string $token): int|string|null
    {
        try {
            $decoded = base64_decode($token);
            if (! $decoded || ! str_contains($decoded, ':')) {
                return null;
            }

            [$customerId, $hash] = explode(':', $decoded, 2);

            $secret = config('app.key');
            $data = $customerId . '|' . now()->format('Y-m');
            $expectedHash = hash_hmac('sha256', $data, $secret);

            if (! hash_equals($expectedHash, $hash)) {
                $data = $customerId . '|' . now()->subMonth()->format('Y-m');
                $expectedHash = hash_hmac('sha256', $data, $secret);

                if (! hash_equals($expectedHash, $hash)) {
                    return null;
                }
            }

            return is_numeric($customerId) ? (int) $customerId : $customerId;
        } catch (\Exception) {
            return null;
        }
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
