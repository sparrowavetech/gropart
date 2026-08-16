<?php

namespace Ashikul\IndiaSmsGateway\Drivers;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\DTOs\SmsResult;
use Throwable;

class Msg91Driver extends AbstractDriver
{
    public function key(): string { return 'msg91'; }

    public function send(SmsMessage $message): SmsResult
    {
        try {
            $endpoint = (string) ($this->setting('endpoint') ?: 'https://control.msg91.com/api/v5/flow/');
            $payload = [
                'template_id' => $this->templateId($message),
                'short_url' => (string) $this->setting('short_url', '0'),
                'recipients' => [[
                    'mobiles' => $message->to,
                    'message' => $message->message,
                ]],
            ];
            $response = $this->http()
                ->withHeaders(['authkey' => (string) $this->secret('api_key'), 'Content-Type' => 'application/json'])
                ->post($endpoint, $payload);
            $data = $response->json();
            $data = is_array($data) ? $data : ['raw' => mb_substr($response->body(), 0, 10000)];
            $accepted = $response->successful() && strtolower((string) data_get($data, 'type')) !== 'error';
            return new SmsResult($accepted, $accepted ? 'accepted' : 'failed',
                (string) (data_get($data, 'request_id') ?: data_get($data, 'requestId') ?: '') ?: null,
                null, $accepted ? null : (string) (data_get($data, 'message') ?: 'MSG91 rejected the request.'),
                $data, $this->key());
        } catch (Throwable $e) { return SmsResult::failed($e->getMessage(), 'connection_error'); }
    }
}
