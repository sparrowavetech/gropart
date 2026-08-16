<?php

namespace Ashikul\IndiaSmsGateway\Drivers;

use Ashikul\IndiaSmsGateway\Contracts\SmsDriverInterface;
use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\Models\SmsTemplate;
use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class AbstractDriver implements SmsDriverInterface
{
    public function __construct(protected SettingsRepository $settings)
    {
    }

    protected function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings->get('gateway_' . $this->key() . '_' . $key, $default);
    }

    protected function secret(string $key): ?string
    {
        return $this->settings->secret('gateway_' . $this->key() . '_' . $key);
    }


    protected function templateId(SmsMessage $message): string
    {
        $template = $this->messageTemplate($message);
        $ids = is_array($template?->gateway_template_ids) ? $template->gateway_template_ids : [];
        $value = trim((string) ($ids[$this->key()] ?? ''));

        if ($value !== '') {
            return $value;
        }

        // Backward compatibility for installations that previously stored one
        // gateway-wide DLT template ID. New configuration is template-specific.
        return trim((string) $this->setting('template_id'));
    }

    protected function messageTemplate(SmsMessage $message): ?SmsTemplate
    {
        $templateKey = trim((string) ($message->metadata['template_key'] ?? ''));

        return $templateKey === ''
            ? null
            : SmsTemplate::query()->where('key', $templateKey)->first();
    }

    /**
     * Return provider variable values in the same order as the placeholders in
     * the approved DLT template. Fast2SMS expects only the variable values,
     * separated by pipes, rather than the fully rendered SMS body.
     */
    protected function templateVariableValues(SmsMessage $message): array
    {
        $template = $this->messageTemplate($message);
        if (! $template) {
            return [];
        }

        $provided = $message->metadata['template_variables'] ?? null;
        if (is_array($provided) && $provided !== []) {
            $values = [];
            preg_match_all('/\{#VAR#\}|\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', (string) $template->content, $matches, PREG_SET_ORDER);
            $sequential = array_values($provided);
            $position = 0;

            foreach ($matches as $match) {
                $name = trim((string) ($match[1] ?? ''));
                if ($name !== '' && array_key_exists($name, $provided)) {
                    $values[] = (string) $provided[$name];
                } elseif (array_key_exists($position, $sequential)) {
                    $values[] = (string) $sequential[$position];
                }
                $position++;
            }

            if ($values !== []) {
                return $values;
            }
        }

        // Fallback for older senders: recover variable values by matching the
        // rendered message against the stored template text.
        $parts = preg_split('/(\{#VAR#\}|\{\{\s*[a-zA-Z0-9_]+\s*\}\})/', (string) $template->content, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (! is_array($parts)) {
            return [];
        }

        $pattern = '';
        $placeholderCount = 0;
        foreach ($parts as $part) {
            if (preg_match('/^(\{#VAR#\}|\{\{\s*[a-zA-Z0-9_]+\s*\}\})$/', $part)) {
                $pattern .= '(.*?)';
                $placeholderCount++;
            } else {
                $pattern .= preg_quote($part, '/');
            }
        }

        if ($placeholderCount === 0 || ! preg_match('/^' . $pattern . '$/us', $message->message, $captures)) {
            return [];
        }

        array_shift($captures);

        return array_map(static fn ($value): string => trim((string) $value), $captures);
    }

    protected function senderId(SmsMessage $message): string
    {
        if (trim((string) $message->senderId) !== '') {
            return trim((string) $message->senderId);
        }

        $templateKey = trim((string) ($message->metadata['template_key'] ?? ''));
        if ($templateKey !== '') {
            $template = SmsTemplate::query()->where('key', $templateKey)->first();
            $senders = is_array($template?->gateway_sender_ids) ? $template->gateway_sender_ids : [];
            $value = trim((string) ($senders[$this->key()] ?? $template?->sender_id ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return trim((string) $this->setting('sender_id'));
    }

    protected function http(): PendingRequest
    {
        return Http::connectTimeout((int) $this->settings->get('connect_timeout', 5))
            ->timeout((int) $this->settings->get('request_timeout', 20))
            ->retry((int) $this->settings->get('retry_attempts', 1), 300, throw: false)
            ->acceptJson();
    }

    public function balance(): ?string
    {
        return null;
    }
}
