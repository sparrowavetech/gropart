<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\DTOs\SmsResult;
use Ashikul\IndiaSmsGateway\Jobs\SendSmsJob;
use Ashikul\IndiaSmsGateway\Models\SmsLog;
use Ashikul\IndiaSmsGateway\Models\SmsTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SmsManager
{
    public function __construct(
        private GatewayRegistry $gateways,
        private SettingsRepository $settings,
        private PhoneNormalizer $phones,
        private SegmentCounter $segments,
        private RateLimitService $limits,
        private LicenseManager $licenses,
    ) {
    }

    public function send(SmsMessage $message, ?string $gateway = null, bool $allowFallback = true): SmsResult
    {
        if (! $this->licenses->canSendSms()) {
            return SmsResult::failed(
                'The 100-SMS trial has ended. Activate an Indian SMS license to continue.',
                'trial_exhausted'
            );
        }

        if (! $this->settings->bool('enabled')) {
            return SmsResult::failed('Indian SMS is disabled.', 'service_disabled');
        }

        try {
            $message->to = $this->phones->normalize($message->to);
            $this->limits->enforceSms($message->to);
        } catch (Throwable $e) {
            return SmsResult::failed($e->getMessage(), 'validation_error');
        }

        $templateGateway = '';
        $templateKey = trim((string) ($message->metadata['template_key'] ?? ''));
        if ($gateway === null && $templateKey !== '') {
            $templateGateway = trim((string) (SmsTemplate::query()->where('key', $templateKey)->value('gateway') ?? ''));
        }

        $primary = $gateway ?: ($templateGateway ?: (string) $this->settings->get('default_gateway', config('india-sms.default_gateway')));
        $drivers = [$primary];
        if ($allowFallback) {
            foreach ([(string) $this->settings->get('fallback_gateway'), (string) $this->settings->get('second_fallback_gateway')] as $fallback) {
                if ($fallback && ! in_array($fallback, $drivers, true)) { $drivers[] = $fallback; }
            }
        }

        $last = SmsResult::failed('No enabled SMS gateway is configured.', 'gateway_unavailable');
        foreach ($drivers as $driverKey) {
            if (! $driverKey || ! $this->gateways->enabled($driverKey) || ! $this->gateways->configured($driverKey)) { continue; }
            $last = $this->sendThrough($driverKey, $message);
            if ($last->accepted || ! $this->isTemporaryFailure($last)) { return $last; }
        }

        return $last;
    }


    public function dispatch(SmsMessage $message, ?string $gateway = null): SmsResult
    {
        $useQueue = $this->settings->bool('queue_enabled')
            && $this->settings->bool('queue_worker_confirmed')
            && (string) config('queue.default', 'sync') !== 'sync';

        if ($useQueue) {
            $this->queue($message, $gateway);

            return new SmsResult(true, 'queued', null, null, null, [], $gateway);
        }

        return $this->send($message, $gateway);
    }

    public function queue(SmsMessage $message, ?string $gateway = null): void
    {
        SendSmsJob::dispatch($message->to, $message->message, $message->senderId, $message->type, $message->metadata, $gateway)->onQueue((string) $this->settings->get('queue_name', 'sms'));
    }

    private function sendThrough(string $gateway, SmsMessage $message): SmsResult
    {
        $log = SmsLog::query()->create([
            'uuid' => (string) Str::uuid(), 'gateway' => $gateway, 'recipient' => $message->to,
            'sender_id' => $message->senderId, 'message' => $message->message, 'message_type' => $message->type,
            'segments' => $this->segments->count($message->message), 'status' => 'processing',
            'metadata' => $message->metadata, 'request_payload' => $this->redact(['to' => $message->to, 'message' => $message->message, 'sender_id' => $message->senderId]),
        ]);

        try {
            $result = $this->gateways->driver($gateway)->send($message);
            $result->gateway = $gateway;
            $log->fill([
                'status' => $result->accepted ? $result->status : 'failed',
                'provider_message_id' => $result->messageId,
                'error_code' => $result->errorCode,
                'error_message' => $result->errorMessage,
                'response_payload' => $this->redact($result->response),
                'sent_at' => $result->accepted ? now() : null,
                'failed_at' => $result->accepted ? null : now(),
            ])->save();
            return $result;
        } catch (Throwable $e) {
            Log::error('India SMS sending failed', ['gateway' => $gateway, 'exception' => $e]);
            $log->update(['status' => 'failed', 'error_code' => 'internal_error', 'error_message' => $e->getMessage(), 'failed_at' => now()]);
            return SmsResult::failed($e->getMessage(), 'internal_error');
        }
    }

    private function isTemporaryFailure(SmsResult $result): bool
    {
        return in_array($result->errorCode, ['connection_error', 'timeout', '429', '500', '502', '503', '504', 'internal_error'], true)
            || str_contains(strtolower((string) $result->errorMessage), 'timeout');
    }

    private function redact(array $payload): array
    {
        $sensitive = ['api_key', 'apikey', 'token', 'secret', 'authorization', 'password'];
        array_walk_recursive($payload, function (&$value, $key) use ($sensitive): void {
            if (in_array(strtolower((string) $key), $sensitive, true)) { $value = '***'; }
        });
        return $payload;
    }
}
