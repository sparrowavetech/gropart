<?php

namespace Ashikul\IndiaSmsGateway\Services;

class GatewayDiagnosticsService
{
    public function __construct(private SettingsRepository $settings)
    {
    }

    public function inspect(string $gateway): array
    {
        $prefix = 'gateway_' . $gateway . '_';
        $sender = trim((string) $this->settings->get($prefix . 'sender_id'));
        $entity = trim((string) $this->settings->get($prefix . 'entity_id'));
        $route = trim((string) $this->settings->get($prefix . 'route'));
        $endpoint = trim((string) $this->settings->get($prefix . 'endpoint'));
        $apiKey = trim((string) ($this->settings->secret($prefix . 'api_key') ?? ''));

        $checks = [
            'enabled' => [
                'label' => 'Gateway enabled',
                'ok' => $this->settings->bool($prefix . 'enabled'),
                'hint' => 'Enable the gateway before using it.',
            ],
            'api_key' => [
                'label' => 'API key saved',
                'ok' => $apiKey !== '' || in_array($gateway, ['generic', 'firebasesms'], true),
                'hint' => 'Enter the provider API key or credentials.',
            ],
            'sender_id' => [
                'label' => 'Sender ID configured',
                'ok' => $sender !== '',
                'hint' => 'Use the exact DLT-approved sender/header.',
            ],
            'entity_id' => [
                'label' => 'PE / Entity ID configured',
                'ok' => $entity !== '' || in_array($gateway, ['twofactor'], true),
                'hint' => 'Required by DLT-enabled transactional routes.',
            ],
            'route' => [
                'label' => 'Route configured',
                'ok' => $route !== '' || in_array($gateway, ['msg91', 'kaleyra', 'twofactor'], true),
                'hint' => 'Use the route supplied by your provider.',
            ],
            'endpoint' => [
                'label' => 'API endpoint available',
                'ok' => $endpoint !== '' || ! in_array($gateway, ['generic', 'firebasesms'], true),
                'hint' => 'Local gateways require a valid API endpoint.',
            ],
        ];


        $passed = collect($checks)->where('ok', true)->count();
        $total = count($checks);

        return [
            'checks' => $checks,
            'score' => $total > 0 ? (int) round(($passed / $total) * 100) : 0,
            'sender' => $sender,
            'entity' => $entity,
            'route' => $route,
            'endpoint' => $endpoint,
        ];
    }
}
