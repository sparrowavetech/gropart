@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
@include('plugins/india-sms-gateway::partials.alerts')
@include('plugins/india-sms-gateway::partials.nav')

<style>
    .indian-sms-gateway-grid .card { border: 1px solid var(--bb-border-color, #e2e8f0); border-radius: 12px; transition: box-shadow .18s ease, transform .18s ease; }
    .indian-sms-gateway-grid .card:hover { box-shadow: 0 10px 28px rgba(15, 23, 42, .09); transform: translateY(-2px); }
    .indian-sms-provider-number { width: 44px; height: 44px; min-width: 44px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(145deg, #206bc4, #174b8a); color: #fff; font-size: 13px; font-weight: 700; letter-spacing: .04em; box-shadow: 0 5px 12px rgba(32, 107, 196, .2); }
    .indian-sms-provider-body { min-height: 184px; display: flex; flex-direction: column; }
    .indian-sms-provider-description { min-height: 48px; margin-bottom: 12px; }
    .indian-sms-provider-actions { margin-top: auto; }
    .indian-sms-test-card { border-radius: 14px; overflow: hidden; }
    .indian-sms-test-status { min-height: 24px; }
    .indian-sms-preview { background: var(--bb-bg-surface-secondary, #f8fafc); border: 1px dashed var(--bb-border-color, #dbe3ee); border-radius: 10px; padding: 14px; min-height: 76px; white-space: pre-wrap; }
    @media (max-width: 767.98px) { .indian-sms-provider-body, .indian-sms-provider-description { min-height: auto; } }
</style>

<div class="row row-cards indian-sms-gateway-grid">
    @foreach ($gateways as $key => $gateway)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body indian-sms-provider-body">
                    <div class="d-flex align-items-start gap-3">
                        <span class="indian-sms-provider-number">{{ $gateway['icon'] }}</span>
                        <div class="flex-fill">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <h3 class="card-title mb-1">{{ $gateway['name'] }}</h3>
                                @if ($defaultGateway === $key)<span class="badge bg-blue-lt">Default</span>@endif
                            </div>
                            <p class="text-secondary indian-sms-provider-description">{{ $gateway['description'] }}</p>
                            <div class="mb-3">
                                <span class="badge {{ $gateway['enabled'] ? 'bg-success-lt' : 'bg-secondary-lt' }}">{{ $gateway['enabled'] ? 'Enabled' : 'Disabled' }}</span>
                                <span class="badge {{ $gateway['configured'] ? 'bg-azure-lt' : 'bg-warning-lt' }}">{{ $gateway['configured'] ? 'Configured' : 'Not configured' }}</span>
                                <span class="badge {{ ($gateway['mapped_templates'] ?? 0) > 0 ? 'bg-purple-lt' : 'bg-secondary-lt' }}">{{ $gateway['mapped_templates'] ?? 0 }} mapped</span>
                            </div>
                        </div>
                    </div>
                    <div class="indian-sms-provider-actions ps-md-5">
                        <a href="{{ route('india-sms.gateways.edit', $key) }}" class="btn btn-primary"><i class="ti ti-adjustments me-1"></i>Configure</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card mt-3 indian-sms-test-card">
    <div class="card-header">
        <div>
            <h3 class="card-title mb-1">Send a test SMS</h3>
            <div class="text-secondary">Choose a gateway and test either an approved DLT template or a direct-message route. The form adapts to the selected provider route.</div>
        </div>
    </div>
    <form method="post" action="{{ route('india-sms.gateways.test') }}" id="indiaSmsTestForm">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Phone number</label>
                    <input class="form-control" name="phone" value="{{ old('phone') }}" placeholder="9876543210" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gateway</label>
                    <select class="form-select" name="gateway" id="indiaSmsTestGateway">
                        @foreach($gateways as $key => $gateway)
                            <option value="{{ $key }}" @selected(old('gateway', $defaultGateway) === $key)>{{ $gateway['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">DLT / Approved Template</label>
                    <select class="form-select" name="template_key" id="indiaSmsTestTemplate">
                        <option value="">No template — direct message only</option>
                        @foreach($templates as $template)
                                                        <option value="{{ $template->key }}"
                                data-name="{{ e($template->name) }}"
                                data-content="{{ e($template->content) }}"
                                data-mapped-gateways="{{ e($template->mapped_gateway_keys_json ?: '[]') }}" data-template-ids="{{ e($template->gateway_template_ids_json ?: '{}') }}"
                                @selected(old('template_key') === $template->key)>
                                {{ $template->name }} ({{ $template->key }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint" id="indiaSmsTemplateHint">Select a template when your route uses a provider-approved Message/Template ID.</div>
                    <div class="indian-sms-test-status mt-2" id="indiaSmsTemplateStatus"></div>
                </div>
                <div class="col-12" id="indiaSmsVariableSection" style="display:none">
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div><div class="fw-semibold">Template variables</div><div class="text-secondary small">Enter values in the same order as the approved provider template.</div></div>
                            <span class="badge bg-purple-lt" id="indiaSmsVariableCount">0 variables</span>
                        </div>
                        <div class="row g-2" id="indiaSmsVariableFields"></div>
                    </div>
                </div>
                <div class="col-12" id="indiaSmsDirectMessageSection">
                    <label class="form-label">Test message</label>
                    <textarea class="form-control" name="message" id="indiaSmsTestMessage" rows="3">{{ old('message', 'Test SMS from Indian SMS.') }}</textarea>
                    <div class="form-hint" id="indiaSmsMessageHint">Used only for direct-message routes. DLT routes use the selected provider Message ID and variable values.</div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Live preview</label>
                        <a href="{{ route('india-sms.templates.index') }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-template me-1"></i>Manage templates</a>
                    </div>
                    <div class="indian-sms-preview" id="indiaSmsMessagePreview">Select a template or enter a direct test message.</div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary"><i class="ti ti-send me-1"></i>Send test SMS</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const gateway = document.getElementById('indiaSmsTestGateway');
    const template = document.getElementById('indiaSmsTestTemplate');
    const message = document.getElementById('indiaSmsTestMessage');
    const preview = document.getElementById('indiaSmsMessagePreview');
    const hint = document.getElementById('indiaSmsTemplateHint');
    const status = document.getElementById('indiaSmsTemplateStatus');
    const variableSection = document.getElementById('indiaSmsVariableSection');
    const variableFields = document.getElementById('indiaSmsVariableFields');
    const variableCount = document.getElementById('indiaSmsVariableCount');
    const directSection = document.getElementById('indiaSmsDirectMessageSection');
    if (!gateway || !template || !message) return;

    const routes = @json($gatewayRoutes);
    const providerHints = {
        msg91: 'Select an MSG91-approved template when your configured route requires DLT mapping.',
        fast2sms: 'Fast2SMS DLT mode shows only templates that contain a Fast2SMS Message ID.',
        firebasesms: 'A mapped template is optional unless the connected provider requires one.',
        twofactor: 'Select a provider template only when required by the configured route.',
        smscountry: 'Use an SMSCountry-approved template when DLT mapping is enabled.',
        kaleyra: 'Use a Kaleyra-approved template when the configured API requires one.',
        generic: 'Use a mapped Template ID or PEID only when required by the local provider.'
    };
    const samples = ['Customer', '654321', '5', '9876543210', 'Demo Store', '10001', '999.00', 'Processing'];

    function selectedOption() { return template.selectedOptions[0] || null; }
    function mappedForGateway(option) {
        if (!option || !option.value) return false;
        try { return JSON.parse(option.dataset.mappedGateways || '[]').includes(gateway.value); } catch (e) { return false; }
    }
    function mappedId(option) {
        if (!option || !option.value) return '';
        try { return String((JSON.parse(option.dataset.templateIds || '{}')[gateway.value]) || '').trim(); } catch (e) { return ''; }
    }
    function filterTemplateOptions() {
        const route = String(routes[gateway.value] || '').toLowerCase();
        const requiresMappedTemplate = gateway.value === 'fast2sms' && route === 'dlt';
        let firstAvailable = null;
        Array.from(template.options).forEach(function (option, index) {
            if (index === 0) {
                option.hidden = requiresMappedTemplate;
                option.disabled = requiresMappedTemplate;
                return;
            }
            const available = !requiresMappedTemplate || mappedForGateway(option);
            option.hidden = !available;
            option.disabled = !available;
            if (available && !firstAvailable) firstAvailable = option;
        });
        const current = selectedOption();
        if (requiresMappedTemplate && (!current || !current.value || current.disabled)) {
            template.value = firstAvailable ? firstAvailable.value : '';
        }
        return { requiresMappedTemplate: requiresMappedTemplate, hasMappedTemplate: !!firstAvailable };
    }
    function placeholders(content) {
        const found = [];
        const regex = /\{#VAR#\}|\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g;
        let match;
        while ((match = regex.exec(String(content || ''))) !== null) {
            found.push(match[1] ? match[1] : 'Variable ' + (found.length + 1));
        }
        return found;
    }
    function rebuildVariables() {
        const option = selectedOption();
        const content = option && option.value ? (option.dataset.content || '') : '';
        const names = placeholders(content);
        variableFields.innerHTML = '';
        names.forEach(function (name, index) {
            const column = document.createElement('div');
            column.className = 'col-md-6';
            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = name.replaceAll('_', ' ');
            const input = document.createElement('input');
            input.className = 'form-control india-sms-variable-input';
            input.name = 'template_variables[]';
            input.value = samples[index] || ('Value ' + (index + 1));
            input.dataset.index = String(index);
            input.addEventListener('input', updatePreview);
            column.appendChild(label); column.appendChild(input); variableFields.appendChild(column);
        });
        variableCount.textContent = names.length + (names.length === 1 ? ' variable' : ' variables');
        variableSection.style.display = option && option.value ? '' : 'none';
    }
    function renderTemplate(content) {
        const values = Array.from(document.querySelectorAll('.india-sms-variable-input')).map(function (input) { return input.value; });
        let index = 0;
        return String(content || '').replace(/\{#VAR#\}|\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, function () {
            const value = values[index] !== undefined ? values[index] : '';
            index += 1;
            return value;
        });
    }
    function updatePreview() {
        const option = selectedOption();
        if (option && option.value) preview.textContent = renderTemplate(option.dataset.content || '');
        else preview.textContent = message.value || 'Your direct-message preview will appear here.';
    }
    function refresh() {
        const route = String(routes[gateway.value] || '').toLowerCase();
        const filterState = filterTemplateOptions();
        const option = selectedOption();
        const isFast2SmsDlt = filterState.requiresMappedTemplate;
        hint.textContent = providerHints[gateway.value] || 'Select an approved template only when required by the provider route.';

        if (isFast2SmsDlt && !filterState.hasMappedTemplate) {
            status.innerHTML = '<span class="badge bg-danger-lt">No Fast2SMS-mapped template found</span> <a class="ms-2" href="{{ route('india-sms.templates.index') }}">Add Message ID in Templates</a>';
            directSection.style.display = 'none';
            message.required = false;
        } else if (isFast2SmsDlt && (!option || !option.value)) {
            status.innerHTML = '<span class="badge bg-danger-lt">Select a mapped Fast2SMS template</span>';
            directSection.style.display = 'none';
            message.required = false;
        } else if (option && option.value && mappedForGateway(option)) {
            status.innerHTML = '<span class="badge bg-success-lt">Mapped ID: ' + mappedId(option) + '</span>';
            directSection.style.display = 'none';
            message.required = false;
        } else if (option && option.value) {
            status.innerHTML = '<span class="badge bg-warning-lt">No mapping for this gateway</span>';
            directSection.style.display = route === 'dlt' ? 'none' : '';
            message.required = route !== 'dlt';
        } else {
            status.innerHTML = '<span class="badge bg-azure-lt">Direct message mode</span>';
            directSection.style.display = '';
            message.required = true;
        }
        rebuildVariables();
        updatePreview();
    }

    const form = gateway.closest('form');
    if (form) {
        form.addEventListener('submit', function (event) {
            const route = String(routes[gateway.value] || '').toLowerCase();
            if (gateway.value === 'fast2sms' && route === 'dlt' && (!template.value || !mappedForGateway(selectedOption()))) {
                event.preventDefault();
                status.innerHTML = '<span class="badge bg-danger-lt">Choose a Fast2SMS-mapped template before sending</span>';
                template.focus();
            }
        });
    }

    gateway.addEventListener('change', refresh);
    template.addEventListener('change', refresh);
    message.addEventListener('input', updatePreview);
    refresh();
});
</script>
@endsection
