<?php

namespace Ashikul\IndiaSmsGateway\Drivers;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\DTOs\SmsResult;
use Throwable;

class Fast2SmsDriver extends AbstractDriver
{
    public function key(): string { return 'fast2sms'; }

    public function send(SmsMessage $message): SmsResult
    {
        try {
            $endpoint = (string) ($this->setting('endpoint') ?: 'https://www.fast2sms.com/dev/bulkV2');
            $route = trim((string) $this->setting('route', 'dlt')) ?: 'dlt';
            $templateId = $this->templateId($message);

            $variables = $this->templateVariableValues($message);
            // Preserve the provider-approved Sender ID exactly as configured.
            // Different providers/accounts may use different lengths and casing.
            $senderId = trim($this->senderId($message));

            $isDltRoute = strtolower($route) === 'dlt';
            if ($isDltRoute && $templateId === '') {
                return SmsResult::failed(
                    'Fast2SMS DLT route requires a mapped provider Message ID.',
                    'template_id_required'
                );
            }

            $payload = [
                'route' => $route,
                'sender_id' => $senderId,
                'message' => $templateId !== '' ? $templateId : $message->message,
                'numbers' => preg_replace('/^91/', '', $message->to),
            ];

            if ($templateId !== '') {
                $payload['variables_values'] = implode('|', $variables);
            }

            // Fast2SMS Dev API documents the bulkV2 request as GET query
            // parameters with the API key in the authorization header.
            $response = $this->http()
                ->withHeaders(['authorization' => (string) $this->secret('api_key')])
                ->get($endpoint, $payload);
            $data = $response->json();
            $data = is_array($data) ? $data : ['raw' => mb_substr($response->body(), 0, 10000)];
            $accepted = $response->successful() && (bool) data_get($data, 'return', false);
            return new SmsResult($accepted, $accepted ? 'accepted' : 'failed',
                (string) (data_get($data, 'request_id') ?: data_get($data, 'requestId') ?: '') ?: null,
                null, $accepted ? null : (string) (data_get($data, 'message.0') ?: data_get($data, 'message') ?: 'Fast2SMS rejected the request.'),
                $data, $this->key());
        } catch (Throwable $e) { return SmsResult::failed($e->getMessage(), 'connection_error'); }
    }
}
