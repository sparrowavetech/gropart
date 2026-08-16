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
            <div class="text-secondary">Choose a gateway. The template list will show only mappings available for that provider.</div>
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
                        <option value="">No template / direct message</option>
                        @foreach($templates as $template)
                            @php $ids = is_array($template->gateway_template_ids) ? $template->gateway_template_ids : []; @endphp
                            <option value="{{ $template->key }}"
                                data-name="{{ e($template->name) }}"
                                data-content="{{ e($template->content) }}"
                                data-mapped-gateways='@json(array_keys(array_filter($ids, fn($id) => trim((string) $id) !== "")))'
                                @selected(old('template_key') === $template->key)>
                                {{ $template->name }} ({{ $template->key }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint" id="indiaSmsTemplateHint">Optional. Required only when the selected provider uses an approved DLT/Template ID.</div>
                    <div class="indian-sms-test-status mt-2" id="indiaSmsTemplateStatus"></div>
                </div>
                <div class="col-12">
                    <label class="form-label">Rendered test message</label>
                    <textarea class="form-control" name="message" id="indiaSmsTestMessage" rows="3" required>{{ old('message', 'Test SMS from Indian SMS.') }}</textarea>
                    <div class="form-hint">The final message must exactly match the provider-approved template after variable replacement.</div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Live preview</label>
                        <a href="{{ route('india-sms.templates.index') }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-template me-1"></i>Manage templates</a>
                    </div>
                    <div class="indian-sms-preview" id="indiaSmsMessagePreview">Test SMS from Indian SMS.</div>
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
    if (!gateway || !template || !message) return;

    const providerHints = {
        msg91: 'Select an MSG91-mapped template when your account requires DLT approval.',
        fast2sms: 'Fast2SMS DLT route requires a template mapped with its Fast2SMS Template ID.',
        firebasesms: 'Template mapping is optional unless your FirebaseSMS/local provider requires it.',
        twofactor: 'Select a provider-approved template when your 2Factor route requires template mapping.',
        smscountry: 'Select an SMSCountry-mapped template when DLT approval is enabled on your account.',
        kaleyra: 'Select a Kaleyra-mapped template when the configured API requires an approved template ID.',
        generic: 'Select a mapped template only when your local provider requires a Template ID or PEID.'
    };
    const sample = { customer_name: 'Customer', site_name: 'Your Store', code: '654321', expires_in: '5', order_id: '1001', order_amount: '499.00', order_status: 'Processing' };
    function renderVariables(text) {
        return (text || '').replace(/{{\s*([a-zA-Z0-9_]+)\s*}}/g, (_, key) => sample[key] || ('{' + key + '}'));
    }
    function refreshOptions() {
        const selectedGateway = gateway.value;
        let mappedCount = 0;
        Array.from(template.options).forEach((option, index) => {
            if (index === 0) { option.hidden = false; option.disabled = false; return; }
            let mapped = [];
            try { mapped = JSON.parse(option.dataset.mappedGateways || '[]'); } catch (e) {}
            const visible = mapped.includes(selectedGateway);
            option.hidden = !visible;
            option.disabled = !visible;
            if (visible) mappedCount++;
        });
        if (template.selectedOptions[0] && template.selectedOptions[0].disabled) template.value = '';
        hint.textContent = providerHints[selectedGateway] || 'Optional. Select an approved provider template when required.';
        status.innerHTML = mappedCount > 0
            ? '<span class="badge bg-success-lt">' + mappedCount + ' mapped template' + (mappedCount === 1 ? '' : 's') + ' available</span>'
            : '<span class="badge bg-warning-lt">No mapped template for this gateway</span>';
        refreshMessage(false);
    }
    function refreshMessage(overwrite) {
        const option = template.selectedOptions[0];
        if (option && option.value && option.dataset.content) {
            const rendered = renderVariables(option.dataset.content);
            if (overwrite) message.value = rendered;
            preview.textContent = overwrite ? rendered : message.value;
        } else {
            preview.textContent = message.value || 'Your message preview will appear here.';
        }
    }
    gateway.addEventListener('change', refreshOptions);
    template.addEventListener('change', () => refreshMessage(true));
    message.addEventListener('input', () => preview.textContent = message.value || 'Your message preview will appear here.');
    refreshOptions();
});
</script>
@endsection
