<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Models\SmsLog;
use Ashikul\IndiaSmsGateway\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WebhookController
{
    public function handle(Request $request, string $gateway): JsonResponse
    {
        $payload = $request->all();
        $messageId = (string) (Arr::get($payload, 'message_id') ?? Arr::get($payload, 'request_id') ?? Arr::get($payload, 'trackingId') ?? '');
        $status = strtolower((string) (Arr::get($payload, 'status') ?? Arr::get($payload, 'delivery_status') ?? 'unknown'));
        $eventId = (string) (Arr::get($payload, 'event_id') ?? $request->header('X-Event-ID') ?? hash('sha256', json_encode($payload)));
        $event = WebhookEvent::query()->firstOrCreate(['gateway' => $gateway, 'event_id' => $eventId], ['provider_message_id' => $messageId ?: null, 'status' => $status, 'payload' => $payload]);

        if (! $event->processed_at && $messageId) {
            $mapped = match (true) {
                str_contains($status, 'deliver') => 'delivered', str_contains($status, 'fail'), str_contains($status, 'reject') => 'failed', str_contains($status, 'sent') => 'sent', default => 'accepted',
            };
            SmsLog::query()->where('provider_message_id', $messageId)->update(['status' => $mapped, 'delivered_at' => $mapped === 'delivered' ? now() : null, 'failed_at' => $mapped === 'failed' ? now() : null]);
            $event->update(['processed_at' => now()]);
        }
        return response()->json(['success' => true]);
    }
}
