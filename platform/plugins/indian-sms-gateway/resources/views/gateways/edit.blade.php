@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
@include('plugins/india-sms-gateway::partials.alerts')
@include('plugins/india-sms-gateway::partials.nav')

@php $isLocalGateway = in_array($gateway, ['generic', 'firebasesms'], true); @endphp

<style>
    .indian-sms-advanced summary { cursor: pointer; list-style: none; }
    .indian-sms-advanced summary::-webkit-details-marker { display: none; }
    .indian-sms-advanced summary:after { content: '+'; float: right; font-size: 20px; line-height: 1; }
    .indian-sms-advanced[open] summary:after { content: '−'; }
</style>


<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Configuration health</h3></div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div><div class="fw-semibold">Gateway readiness</div><div class="text-secondary">Fix highlighted items before sending production SMS.</div></div>
                    <span class="badge {{ $diagnostics['score'] >= 80 ? 'bg-success-lt' : ($diagnostics['score'] >= 50 ? 'bg-warning-lt' : 'bg-danger-lt') }}">{{ $diagnostics['score'] }}%</span>
                </div>
                <div class="row g-2">
                    @foreach($diagnostics['checks'] as $check)
                        <div class="col-md-6">
                            <div class="border rounded p-2 d-flex gap-2 align-items-start">
                                <span class="badge {{ $check['ok'] ? 'bg-success-lt' : 'bg-warning-lt' }}">{{ $check['ok'] ? '✓' : '!' }}</span>
                                <div><div class="fw-semibold">{{ $check['label'] }}</div><small class="text-secondary">{{ $check['hint'] }}</small></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Request preview</h3></div>
            <div class="card-body">
                <div class="small text-secondary mb-2">Sensitive credentials are hidden.</div>
                <pre class="bg-light border rounded p-3 mb-0" style="white-space:pre-wrap">{
  "gateway": "{{ $gateway }}",
  "sender_id": "{{ $diagnostics['sender'] }}",
  "entity_id": "{{ $diagnostics['entity'] }}",
  "route": "{{ $diagnostics['route'] }}",
  "template_id": "per message template",
  "phone": "91XXXXXXXXXX"
}</pre>
            </div>
        </div>
    </div>
</div>

<form method="post" action="{{ route('india-sms.gateways.update', $gateway) }}">
    @csrf @method('PUT')
    <div class="card">
        <div class="card-header"><div><h3 class="card-title">{{ $meta['name'] }}</h3><div class="text-secondary">{{ $meta['description'] }}</div></div></div>
        <div class="card-body"><div class="row g-3">
            <div class="col-12"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="enabled" value="1" @checked($settings->bool('gateway_' . $gateway . '_enabled'))><span class="form-check-label">Enable this gateway</span></label></div>

            @if($isLocalGateway)
                <div class="col-12"><div class="alert alert-info mb-0"><strong>Quick setup:</strong> enter the values supplied by your SMS provider. Default field mapping already matches common Indian local APIs.</div></div>
                <div class="col-12"><div class="alert alert-info mb-0"><strong>DLT Template IDs are message-specific.</strong> Configure them from <a href="{{ route('india-sms.templates.index') }}">Templates</a>. Each message can have a different ID for every gateway.</div></div>
                <div class="col-12"><label class="form-label">Base API URL <span class="text-danger">*</span></label><input name="endpoint" type="url" class="form-control" value="{{ old('endpoint', $settings->get('gateway_' . $gateway . '_endpoint')) }}" placeholder="http://api.example.in/api/mt/SendSMS" required></div>
                <div class="col-md-6"><label class="form-label">Username <span class="text-danger">*</span></label><input name="username" class="form-control" value="{{ old('username', $settings->get('gateway_' . $gateway . '_username')) }}" required></div>
                <div class="col-md-6"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep saved password"><small class="form-hint">Saved securely. Existing password is never displayed.</small></div>
                <div class="col-md-6"><label class="form-label">DLT Sender ID <span class="text-danger">*</span></label><input name="sender_id" class="form-control" value="{{ old('sender_id', $settings->get('gateway_' . $gateway . '_sender_id')) }}" placeholder="Example: GROPRT" required></div>
                <div class="col-md-3"><label class="form-label">Channel</label><input name="channel" class="form-control" value="{{ old('channel', $settings->get('gateway_' . $gateway . '_channel', 'Transactional')) }}" placeholder="Transactional"></div>
                <div class="col-md-3"><label class="form-label">Route</label><input name="route" class="form-control" value="{{ old('route', $settings->get('gateway_' . $gateway . '_route')) }}" placeholder="Provider route"></div>
                <div class="col-md-6"><label class="form-label">PEID / Entity ID</label><input name="entity_id" class="form-control" value="{{ old('entity_id', $settings->get('gateway_' . $gateway . '_entity_id')) }}"></div>
                <div class="col-md-6"><label class="form-label">DCS</label><select name="dcs" class="form-select"><option value="0" @selected($settings->get('gateway_' . $gateway . '_dcs', '0') === '0')>0 — Standard text</option><option value="8" @selected($settings->get('gateway_' . $gateway . '_dcs') === '8')>8 — Unicode</option></select></div>
                <div class="col-md-6"><label class="form-label">Flash SMS</label><select name="flashsms" class="form-select"><option value="0" @selected($settings->get('gateway_' . $gateway . '_flashsms', '0') === '0')>Off</option><option value="1" @selected($settings->get('gateway_' . $gateway . '_flashsms') === '1')>On</option></select></div>

                <div class="col-12"><div class="border rounded p-3 bg-light"><div class="fw-semibold mb-2">Request format</div><code class="d-block text-wrap">?user={{ '{username}' }}&password=********&senderid={{ '{sender_id}' }}&channel={{ '{channel}' }}&DCS={{ '{dcs}' }}&flashsms={{ '{flashsms}' }}&number={{ '{mobile}' }}&text={{ '{message}' }}&route={{ '{route}' }}&DLTTemplateId={{ '{template_id}' }}&PEID={{ '{entity_id}' }}</code></div></div>

                <div class="col-12"><details class="indian-sms-advanced border rounded p-3"><summary class="fw-semibold">Advanced settings — only change when your provider uses different field names</summary><div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label">Method</label><select name="method" class="form-select"><option value="GET" @selected($settings->get('gateway_' . $gateway . '_method', 'GET') === 'GET')>GET</option><option value="POST" @selected($settings->get('gateway_' . $gateway . '_method') === 'POST')>POST</option></select></div>
                    <div class="col-md-4"><label class="form-label">Payload</label><select name="payload_type" class="form-select"><option value="query" @selected($settings->get('gateway_' . $gateway . '_payload_type', 'query') === 'query')>Query string</option><option value="form" @selected($settings->get('gateway_' . $gateway . '_payload_type') === 'form')>Form data</option><option value="json" @selected($settings->get('gateway_' . $gateway . '_payload_type') === 'json')>JSON</option></select></div>
                    @foreach(['username_field'=>['Username field','user'],'password_field'=>['Password field','password'],'phone_field'=>['Phone field','number'],'message_field'=>['Message field','text'],'sender_field'=>['Sender field','senderid'],'channel_field'=>['Channel field','channel'],'dcs_field'=>['DCS field','DCS'],'flashsms_field'=>['Flash SMS field','flashsms'],'route_field'=>['Route field','route'],'template_id_field'=>['DLT Template field','DLTTemplateId'],'entity_id_field'=>['PEID field','PEID'],'success_path'=>['Success JSON path',''],'success_value'=>['Success value',''],'message_id_path'=>['Message ID path',''],'error_path'=>['Error JSON path','']] as $field => [$label,$default])
                        <div class="col-md-6"><label class="form-label">{{ $label }}</label><input name="{{ $field }}" class="form-control" value="{{ old($field, $settings->get('gateway_' . $gateway . '_' . $field, $default)) }}"></div>
                    @endforeach
                    <div class="col-md-6"><label class="form-label">Authorization header</label><input name="auth_header" class="form-control" value="{{ old('auth_header', $settings->get('gateway_' . $gateway . '_auth_header')) }}"></div>
                    <div class="col-md-6"><label class="form-label">Header secret</label><input type="password" name="auth_header_value" class="form-control" placeholder="Leave blank to keep saved value"></div>
                    <div class="col-12"><label class="form-label">Static parameters (JSON)</label><textarea name="static_parameters" class="form-control" rows="3" placeholder='{"custom_field":"value"}'>{{ old('static_parameters', $settings->get('gateway_' . $gateway . '_static_parameters')) }}</textarea></div>
                    <div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="allow_insecure_http" value="1" @checked($settings->bool('gateway_' . $gateway . '_allow_insecure_http'))><span class="form-check-label">Allow legacy HTTP endpoint</span></label><small class="form-hint text-danger">Use only when your provider has no HTTPS endpoint.</small></div>
                </div></details></div>
            @else
                <div class="col-md-6"><label class="form-label">API key</label><input type="password" name="api_key" class="form-control" placeholder="Leave blank to keep the saved key"><small class="form-hint">Stored encrypted.</small></div>
                @if($gateway === 'smscountry')<div class="col-md-6"><label class="form-label">Username</label><input name="username" class="form-control" value="{{ old('username', $settings->get('gateway_smscountry_username')) }}"></div>@endif
                @if($gateway === 'kaleyra')<div class="col-md-6"><label class="form-label">Account SID</label><input name="account_id" class="form-control" value="{{ old('account_id', $settings->get('gateway_kaleyra_account_id')) }}"></div>@endif
                <div class="col-12"><div class="alert alert-info mb-0"><strong>DLT Template IDs are message-specific.</strong> Configure them from <a href="{{ route('india-sms.templates.index') }}">Templates</a>. Each template can use a different ID for every gateway.</div></div><div class="col-md-6"><label class="form-label">Sender ID</label><input name="sender_id" class="form-control" value="{{ old('sender_id', $settings->get('gateway_' . $gateway . '_sender_id')) }}" placeholder="Example: GROPRT"><small class="form-hint">Use the exact sender/header approved by your provider. Length and letter case depend on the provider account.</small></div>
                <div class="col-md-6"><label class="form-label">PE / Entity ID</label><input name="entity_id" class="form-control" value="{{ old('entity_id', $settings->get('gateway_' . $gateway . '_entity_id')) }}"></div>
                <div class="col-md-6"><label class="form-label">Route</label><input name="route" class="form-control" value="{{ old('route', $settings->get('gateway_' . $gateway . '_route')) }}" placeholder="Example: dlt / transactional / 15"><small class="form-hint">Use the exact route supplied by the provider.</small></div>
                <div class="col-12"><label class="form-label">API endpoint</label><input name="endpoint" type="url" class="form-control" value="{{ old('endpoint', $settings->get('gateway_' . $gateway . '_endpoint')) }}" placeholder="Use provider default when blank"></div>
            @endif
        </div></div>
        <div class="card-footer d-flex justify-content-between"><a href="{{ route('india-sms.gateways.index') }}" class="btn btn-outline-secondary">Back</a><button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save gateway</button></div>
    </div>
</form>
@endsection
