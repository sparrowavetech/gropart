<?php

namespace Ashikul\IndiaSmsGateway\Drivers;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\DTOs\SmsResult;
use Ashikul\IndiaSmsGateway\Services\UrlGuard;
use Illuminate\Support\Arr;
use Throwable;

class GenericHttpDriver extends AbstractDriver
{
    public function key(): string
    {
        return 'generic';
    }

    public function send(SmsMessage $message): SmsResult
    {
        try {
            $endpoint = (string) $this->setting('endpoint');

            app(UrlGuard::class)->assertSafeEndpoint(
                $endpoint,
                filter_var(
                    $this->setting('allow_insecure_http', false),
                    FILTER_VALIDATE_BOOLEAN
                )
            );

            $payload = $this->staticParameters();
            $this->put($payload, 'phone_field', 'number', substr($message->to, 2));
            $this->put($payload, 'message_field', 'text', $message->message);
            $this->put($payload, 'username_field', 'user', $this->setting('username'));
            $this->put($payload, 'password_field', 'password', $this->secret('password'));
            $this->put($payload, 'api_key_field', 'api_key', $this->secret('api_key'));
            $this->put(
                $payload,
                'sender_field',
                'senderid',
                $this->senderId($message)
            );
            $this->put($payload, 'channel_field', 'channel', $this->setting('channel'));
            $this->put($payload, 'dcs_field', 'DCS', $this->setting('dcs', '0'));
            $this->put($payload, 'flashsms_field', 'flashsms', $this->setting('flashsms', '0'));
            $this->put(
                $payload,
                'template_id_field',
                'DLTTemplateId',
                $this->templateId($message)
            );
            $this->put($payload, 'entity_id_field', 'PEID', $this->setting('entity_id'));
            $this->put($payload, 'route_field', 'route', $this->setting('route'));

            $request = $this->http();
            $headerName = trim((string) $this->setting('auth_header'));

            if ($headerName !== '') {
                $request = $request->withHeaders([
                    $headerName => (string) $this->secret('auth_header_value'),
                ]);
            }

            $method = strtoupper((string) $this->setting('method', 'GET'));
            $payloadType = (string) $this->setting('payload_type', 'query');

            if ($method === 'GET' || $payloadType === 'query') {
                $response = $request->get($endpoint, $payload);
            } elseif ($payloadType === 'json') {
                $response = $request->post($endpoint, $payload);
            } else {
                $response = $request->asForm()->post($endpoint, $payload);
            }

            $data = $response->json();
            $data = is_array($data)
                ? $data
                : ['raw' => mb_substr($response->body(), 0, 10000)];

            $successPath = trim((string) $this->setting('success_path'));
            $successValue = trim((string) $this->setting('success_value'));

            if ($successPath !== '') {
                $actual = Arr::get($data, $successPath);
                $accepted = $response->successful()
                    && strtolower((string) $actual) === strtolower($successValue);
            } else {
                $raw = strtolower((string) ($data['raw'] ?? $response->body()));
                $accepted = $response->successful()
                    && ! preg_match('/error|invalid|failed|unauthori[sz]ed/', $raw);
            }

            $messageId = $this->path(
                $data,
                (string) $this->setting('message_id_path')
            );
            $error = $this->path(
                $data,
                (string) $this->setting('error_path')
            );

            return new SmsResult(
                $accepted,
                $accepted ? 'accepted' : 'failed',
                $messageId !== null ? (string) $messageId : null,
                $accepted ? null : (string) $response->status(),
                $accepted
                    ? null
                    : (string) ($error ?: 'The local Indian gateway rejected the request.'),
                $data,
                $this->key()
            );
        } catch (Throwable $exception) {
            return SmsResult::failed(
                $exception->getMessage(),
                'connection_error'
            );
        }
    }

    private function put(
        array &$payload,
        string $settingKey,
        string $defaultField,
        mixed $value
    ): void {
        $field = trim((string) $this->setting($settingKey, $defaultField));

        if ($field !== '' && $value !== null && $value !== '') {
            $payload[$field] = $value;
        }
    }

    private function path(array $data, string $path): mixed
    {
        return trim($path) !== '' ? Arr::get($data, $path) : null;
    }

    private function staticParameters(): array
    {
        $raw = trim((string) $this->setting('static_parameters'));

        if ($raw === '') {
            return [];
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }
}
