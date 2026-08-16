<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Models\SmsTemplate;
use Ashikul\IndiaSmsGateway\Services\DatabaseInstaller;
use Ashikul\IndiaSmsGateway\Services\GatewayRegistry;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TemplatesController extends Controller
{
    public function index(Request $request, DatabaseInstaller $database, GatewayRegistry $gateways)
    {
        page_title()->setTitle('SMS Templates');
        $database->ensure();

        if (! Schema::hasTable('india_sms_templates')) {
            return view('plugins/india-sms-gateway::templates.index', [
                'templates' => $this->emptyPaginator($request),
                'databaseError' => true,
                'gateways' => $gateways->all(),
            ]);
        }

        return view('plugins/india-sms-gateway::templates.index', [
            'templates' => SmsTemplate::query()->latest()->paginate(25),
            'databaseError' => false,
            'gateways' => $gateways->all(),
        ]);
    }

    public function create(GatewayRegistry $gateways, DatabaseInstaller $database)
    {
        abort_unless($database->ensure(), 503, 'Indian SMS database tables are not ready.');
        page_title()->setTitle('Create SMS Template');

        return view('plugins/india-sms-gateway::templates.form', [
            'template' => new SmsTemplate(),
            'gateways' => $gateways->all(),
        ]);
    }

    public function store(Request $request, DatabaseInstaller $database)
    {
        abort_unless($database->ensure(), 503, 'Indian SMS database tables are not ready.');
        SmsTemplate::query()->create($this->validateData($request));

        return redirect()->route('india-sms.templates.index')->with('success_msg', 'SMS template created.');
    }

    public function edit(SmsTemplate $template, GatewayRegistry $gateways, DatabaseInstaller $database)
    {
        abort_unless($database->ensure(), 503, 'Indian SMS database tables are not ready.');
        $template->refresh();
        page_title()->setTitle('Edit SMS Template');

        return view('plugins/india-sms-gateway::templates.form', [
            'template' => $template,
            'gateways' => $gateways->all(),
        ]);
    }

    public function update(Request $request, SmsTemplate $template, DatabaseInstaller $database)
    {
        abort_unless($database->ensure(), 503, 'Indian SMS database tables are not ready.');

        $data = $this->validateData($request, $template);
        // Template keys are integration identifiers. Keep the existing key stable while editing.
        $data['key'] = $template->key;

        DB::transaction(function () use ($template, $data): void {
            $template->forceFill($data)->saveOrFail();
        });

        return redirect()
            ->route('india-sms.templates.edit', $template)
            ->with('success_msg', 'Template content and gateway mapping saved successfully.');
    }

    public function destroy(SmsTemplate $template)
    {
        $template->delete();
        return redirect()->route('india-sms.templates.index')->with('success_msg', 'SMS template deleted.');
    }

    private function validateData(Request $request, ?SmsTemplate $template = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'key' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z0-9_\-]+$/',
                Rule::unique('india_sms_templates', 'key')->ignore($template?->id),
            ],
            'language' => ['required', 'string', 'max:10'],
            'content' => ['required', 'string', 'max:5000'],
            'gateway' => ['nullable', 'string', 'max:60'],
            'sender_id' => ['nullable', 'string', 'max:100'],
            'gateway_template_ids' => ['nullable', 'array'],
            'gateway_template_ids.*' => ['nullable', 'string', 'max:191'],
            'gateway_sender_ids' => ['nullable', 'array'],
            'gateway_sender_ids.*' => ['nullable', 'string', 'max:100'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['gateway'] = $data['gateway'] ?? null;
        $data['sender_id'] = $data['sender_id'] ?? null;
        $data['gateway_template_ids'] = array_filter(
            $data['gateway_template_ids'] ?? [],
            static fn ($value): bool => trim((string) $value) !== ''
        );
        $data['gateway_sender_ids'] = array_filter(
            $data['gateway_sender_ids'] ?? [],
            static fn ($value): bool => trim((string) $value) !== ''
        );
        preg_match_all('/{{\s*([a-zA-Z0-9_]+)\s*}}/', $data['content'], $matches);
        $data['variables'] = array_values(array_unique($matches[1] ?? []));

        return $data;
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, 25, max(1, (int) $request->input('page', 1)), [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }
}
