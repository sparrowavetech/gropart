<?php

namespace Botble\Sms\Supports;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class SmsGatewayLog
{
    private const ENCRYPTED_MESSAGE_PREFIX = 'encrypted:v1:';

    public static function storeOtpMessage(string $message): string
    {
        return self::ENCRYPTED_MESSAGE_PREFIX . Crypt::encryptString($message);
    }

    public static function displayMessage(string $stored): ?string
    {
        if (! str_starts_with($stored, self::ENCRYPTED_MESSAGE_PREFIX)) {
            return $stored;
        }

        try {
            return Crypt::decryptString(substr($stored, strlen(self::ENCRYPTED_MESSAGE_PREFIX)));
        } catch (\Throwable) {
            return null;
        }
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    public static function responseSummary(mixed $payload): array
    {
        if (! is_array($payload)) {
            return ['format' => get_debug_type($payload)];
        }

        $summary = [];
        foreach (['ErrorCode' => 'code', 'JobId' => 'job_id', 'MessageData.0.MessageId' => 'message_id'] as $path => $key) {
            $value = Arr::get($payload, $path);
            if (is_scalar($value) && preg_match('/\A[A-Za-z0-9_.-]{1,64}\z/', (string) $value) === 1) {
                $summary[$key] = (string) $value;
            }
        }

        return $summary;
    }

    private static function write(string $level, string $message, array $context): void
    {
        try {
            Log::channel('sms')->log($level, $message, $context);
        } catch (\Throwable) {
            // Logging must never turn an accepted gateway response into a failed send.
        }
    }
}
