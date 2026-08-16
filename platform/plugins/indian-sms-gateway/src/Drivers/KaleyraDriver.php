<?php

namespace Ashikul\IndiaSmsGateway\Drivers;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\DTOs\SmsResult;
use Throwable;

class KaleyraDriver extends AbstractDriver
{
    public function key(): string { return 'kaleyra'; }

    public function send(SmsMessage $message): SmsResult
    {
        try {
            $sid = trim((string) $this->setting('account_id'));
            $endpoint = (string) ($this->setting('endpoint') ?: "https://api.kaleyra.io/v1/{$sid}/messages");
            $payload = [
                'to' => '+' . $message->to,
                'type' => 'TXN',
                'sender' => $this->senderId($message),
                'body' => $message->message,
                'template_id' => $this->templateId($message),
                'entity_id' => (string) $this->setting('entity_id'),
            ];
            $response = $this->http()
                ->withHeaders(['api-key' => (string) $this->secret('api_key')])
                ->asForm()->post($endpoint, array_filter($payload, fn ($v) => $v !== ''));
            $data = $response->json();
            $data = is_array($data) ? $data : ['raw' => mb_substr($response->body(), 0, 10000)];
            $accepted = $response->successful() && ! data_get($data, 'error');
            return new SmsResult($accepted, $accepted ? 'accepted' : 'failed',
                (string) (data_get($data, 'id') ?: data_get($data, 'message_id') ?: '') ?: null,
                null, $accepted ? null : (string) (data_get($data, 'error.message') ?: data_get($data, 'message') ?: 'Kaleyra rejected the request.'),
                $data, $this->key());
        } catch (Throwable $e) { return SmsResult::failed($e->getMessage(), 'connection_error'); }
    }
}
