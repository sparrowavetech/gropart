<?php

namespace Ashikul\IndiaSmsGateway\Drivers;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\DTOs\SmsResult;
use Throwable;

class TwoFactorDriver extends AbstractDriver
{
    public function key(): string { return 'twofactor'; }

    public function send(SmsMessage $message): SmsResult
    {
        try {
            $apiKey = (string) $this->secret('api_key');
            $endpoint = (string) ($this->setting('endpoint') ?: "https://2factor.in/API/V1/{$apiKey}/ADDON_SERVICES/SEND/TSMS");
            $payload = [
                'From' => $this->senderId($message),
                'To' => substr($message->to, 2),
                'TemplateName' => $this->templateId($message),
                'VAR1' => $message->message,
            ];
            $response = $this->http()->asForm()->post($endpoint, $payload);
            $data = $response->json();
            $data = is_array($data) ? $data : ['raw' => mb_substr($response->body(), 0, 10000)];
            $accepted = $response->successful() && strtoupper((string) data_get($data, 'Status')) === 'SUCCESS';
            return new SmsResult($accepted, $accepted ? 'accepted' : 'failed',
                (string) (data_get($data, 'Details') ?: '') ?: null, null,
                $accepted ? null : (string) (data_get($data, 'Details') ?: '2Factor rejected the request.'),
                $data, $this->key());
        } catch (Throwable $e) { return SmsResult::failed($e->getMessage(), 'connection_error'); }
    }
}
