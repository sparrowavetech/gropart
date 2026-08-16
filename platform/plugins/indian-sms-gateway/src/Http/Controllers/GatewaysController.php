<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Ashikul\IndiaSmsGateway\Models\SmsTemplate;
use Ashikul\IndiaSmsGateway\Services\GatewayRegistry;
use Ashikul\IndiaSmsGateway\Services\GatewayDiagnosticsService;
use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Ashikul\IndiaSmsGateway\Services\SmsManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class GatewaysController extends Controller
{
    public function index(GatewayRegistry $registry, SettingsRepository $settings)
    {
        page_title()->setTitle('Indian SMS Gateways');

        $items = [];
        foreach ($registry->all() as $key => $item) {
            $items[$key] = array_merge($item, [
                'enabled' => $registry->enabled($key),
                'configured' => $registry->configured($key),
                'route' => strtolower(trim((string) $settings->get('gateway_' . $key . '_route', ''))),
            ]);
        }

        $templates = SmsTemplate::query()->where('is_active', true)->orderBy('name')->get();
        foreach ($templates as $template) {
            $ids = is_array($template->gateway_template_ids) ? $template->gateway_template_ids : [];
            $mappedKeys = [];
            foreach ($ids as $gatewayKey => $id) {
                if (trim((string) $id) !== '') {
                    $mappedKeys[] = (string) $gatewayKey;
                }
            }
            $template->setAttribute('mapped_gateway_keys_json', json_encode($mappedKeys, JSON_UNESCAPED_SLASHES));
            $template->setAttribute('gateway_template_ids_json', json_encode($ids, JSON_UNESCAPED_SLASHES));
        }

        foreach ($items as $key => &$item) {
            $item['mapped_templates'] = $templates->filter(function (SmsTemplate $template) use ($key): bool {
                $ids = is_array($template->gateway_template_ids) ? $template->gateway_template_ids : [];
                return trim((string) ($ids[$key] ?? '')) !== '';
            })->count();
        }
        unset($item);

        return view('plugins/india-sms-gateway::gateways.index-v143', [
            'gateways' => $items,
            'defaultGateway' => $settings->get('default_gateway', 'msg91'),
            'templates' => $templates,
            'gatewayRoutes' => collect($items)->mapWithKeys(static fn (array $item, string $key): array => [$key => (string) ($item['route'] ?? '')])->all(),
        ]);
    }

    public function edit(string $gateway, GatewayRegistry $registry, SettingsRepository $settings, GatewayDiagnosticsService $diagnostics)
    {
        abort_unless(isset($registry->all()[$gateway]), 404);

        page_title()->setTitle('Configure ' . $registry->all()[$gateway]['name']);

        return view('plugins/india-sms-gateway::gateways.edit', [
            'gateway' => $gateway,
            'meta' => $registry->all()[$gateway],
            'settings' => $settings,
            'diagnostics' => $diagnostics->inspect($gateway),
        ]);
    }

    public function update(
        Request $request,
        string $gateway,
        GatewayRegistry $registry,
        SettingsRepository $settings
    ) {
        abort_unless(isset($registry->all()[$gateway]), 404);

        $data = $request->validate($this->rules($gateway));

        if (array_key_exists('sender_id', $data)) {
            $data['sender_id'] = trim((string) $data['sender_id']);
        }
        $values = ['gateway_' . $gateway . '_enabled' => $request->boolean('enabled')];

        foreach ($data as $key => $value) {
            if (in_array($key, ['api_key', 'token', 'password', 'auth_header_value'], true)) {
                continue;
            }

            $values['gateway_' . $gateway . '_' . $key] = $value;
        }

        $values['gateway_' . $gateway . '_allow_insecure_http'] = $request->boolean('allow_insecure_http');
        $settings->set($values);

        foreach (['api_key', 'token', 'password', 'auth_header_value'] as $secret) {
            if ($request->filled($secret)) {
                $settings->setSecret(
                    'gateway_' . $gateway . '_' . $secret,
                    (string) $request->input($secret)
                );
            }
        }

        return redirect()
            ->route('india-sms.gateways.edit', $gateway)
            ->with('success_msg', 'Gateway settings saved.');
    }

    public function test(Request $request, SmsManager $manager, GatewayRegistry $registry, SettingsRepository $settings, \Ashikul\IndiaSmsGateway\Services\TemplateRenderer $renderer)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:1000'],
            'gateway' => ['required', Rule::in(array_keys($registry->all()))],
            'template_key' => ['nullable', 'string', Rule::exists('india_sms_templates', 'key')->where('is_active', true)],
            'template_variables' => ['nullable', 'array'],
            'template_variables.*' => ['nullable', 'string', 'max:500'],
        ]);

        $gateway = (string) $data['gateway'];
        $templateKey = trim((string) ($data['template_key'] ?? ''));
        $template = $templateKey !== ''
            ? SmsTemplate::query()->where('key', $templateKey)->where('is_active', true)->first()
            : null;

        $route = strtolower(trim((string) $settings->get('gateway_' . $gateway . '_route', '')));
        $isFast2SmsDlt = $gateway === 'fast2sms' && $route === 'dlt';

        if ($isFast2SmsDlt && ! $template) {
            return back()->withInput()->with('error_msg', 'Fast2SMS DLT mode requires a template that has a Fast2SMS Message ID. Configure the mapping in Templates, then select that mapped template.');
        }

        $variables = array_values(array_map(
            static fn ($value): string => trim((string) $value),
            $data['template_variables'] ?? []
        ));

        if ($template) {
            $ids = is_array($template->gateway_template_ids) ? $template->gateway_template_ids : [];
            $mappedId = trim((string) ($ids[$gateway] ?? ''));

            if ($isFast2SmsDlt && $mappedId === '') {
                return back()->withInput()->with('error_msg', 'This template is not mapped to Fast2SMS. Open Templates, add its Fast2SMS Message ID, and then select it again.');
            }

            $messageText = $this->renderTemplateForTest((string) $template->content, $variables, $renderer, $template->key);
        } else {
            $messageText = trim((string) ($data['message'] ?? ''));
            if ($messageText === '') {
                return back()->withInput()->with('error_msg', 'Enter a test message or select an approved template.');
            }
        }

        $result = $manager->send(
            new SmsMessage(
                $data['phone'],
                $messageText,
                type: 'test',
                metadata: [
                    'template_key' => $templateKey,
                    'template_variables' => $variables,
                ],
            ),
            $gateway,
            false
        );

        $providerMessage = trim((string) $result->errorMessage);
        $message = $result->accepted
            ? 'Test SMS accepted. Message ID: ' . ($result->messageId ?: 'N/A')
            : 'Test failed: ' . ($providerMessage !== '' ? $providerMessage : 'The provider rejected the request.');

        return back()->withInput()->with($result->accepted ? 'success_msg' : 'error_msg', $message);
    }

    private function renderTemplateForTest(string $content, array $variables, \Ashikul\IndiaSmsGateway\Services\TemplateRenderer $renderer, string $templateKey): string
    {
        if ($variables === []) {
            $variables = ['Customer', '654321', '5', '9876543210', 'Demo Store', '10001', '999.00', 'Processing'];
        }

        $named = [];
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        foreach (array_values(array_unique($matches[1] ?? [])) as $index => $name) {
            $named[$name] = $variables[$index] ?? ('Value ' . ($index + 1));
        }

        $rendered = $renderer->render($templateKey, $named, $content);
        foreach ($variables as $value) {
            if (! str_contains($rendered, '{#VAR#}')) {
                break;
            }
            $rendered = preg_replace('/\{#VAR#\}/', (string) $value, $rendered, 1);
        }

        return trim($rendered);
    }

    private function rules(string $gateway): array
    {
        $common = [
            'endpoint' => ['nullable', 'url', 'max:1000'],
            'sender_id' => ['nullable', 'string', 'max:100'],
            'template_id' => ['nullable', 'string', 'max:191'],
            'entity_id' => ['nullable', 'string', 'max:191'],
            'route' => ['nullable', 'string', 'max:100'],
            'account_id' => ['nullable', 'string', 'max:191'],
            'username' => ['nullable', 'string', 'max:191'],
            'channel' => ['nullable', 'string', 'max:100'],
            'dcs' => ['nullable', 'string', 'max:20'],
            'flashsms' => ['nullable', 'string', 'max:20'],
        ];

        if (in_array($gateway, ['generic', 'firebasesms'], true)) {
            return array_merge($common, [
                'endpoint' => ['required', 'url', 'max:1000'],
                'api_key' => ['nullable', 'string', 'max:1000'],
                'password' => ['nullable', 'string', 'max:1000'],
                'method' => ['required', Rule::in(['GET', 'POST'])],
                'payload_type' => ['required', Rule::in(['query', 'form', 'json'])],
                'phone_field' => ['required', 'string', 'max:100'],
                'message_field' => ['required', 'string', 'max:100'],
                'username_field' => ['nullable', 'string', 'max:100'],
                'password_field' => ['nullable', 'string', 'max:100'],
                'api_key_field' => ['nullable', 'string', 'max:100'],
                'sender_field' => ['nullable', 'string', 'max:100'],
                'channel_field' => ['nullable', 'string', 'max:100'],
                'dcs_field' => ['nullable', 'string', 'max:100'],
                'flashsms_field' => ['nullable', 'string', 'max:100'],
                'template_id_field' => ['nullable', 'string', 'max:100'],
                'entity_id_field' => ['nullable', 'string', 'max:100'],
                'route_field' => ['nullable', 'string', 'max:100'],
                'success_path' => ['nullable', 'string', 'max:191'],
                'success_value' => ['nullable', 'string', 'max:191'],
                'message_id_path' => ['nullable', 'string', 'max:191'],
                'error_path' => ['nullable', 'string', 'max:191'],
                'static_parameters' => ['nullable', 'json'],
                'auth_header' => ['nullable', 'string', 'max:100'],
                'auth_header_value' => ['nullable', 'string', 'max:1000'],
            ]);
        }

        $providerRules = [
            'api_key' => ['nullable', 'string', 'max:1000'],
        ];


        return array_merge($common, $providerRules);
    }
}
