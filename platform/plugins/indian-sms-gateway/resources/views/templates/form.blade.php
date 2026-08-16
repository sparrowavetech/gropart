@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
<form method="POST" action="{{ $template->exists ? route('india-sms.templates.update', $template) : route('india-sms.templates.store') }}">
    @csrf
    @if($template->exists) @method('PUT') @endif

    @if(session('success_msg'))
        <div class="alert alert-success d-flex align-items-center mb-3">
            <i class="ti ti-circle-check me-2"></i>{{ session('success_msg') }}
        </div>
    @endif

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Message content</h3>
                        <div class="text-secondary">Edit the exact SMS text that customers will receive.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Template name <span class="text-danger">*</span></label>
                            <input name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Language</label>
                            <input name="language" class="form-control" value="{{ old('language', $template->language ?: 'en') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="8" required>{{ old('content', $template->content) }}</textarea>
                            <div class="form-hint mt-2">
                                Use variables such as <code>@{{ customer_name }}</code>, <code>@{{ site_name }}</code>, <code>@{{ code }}</code>, <code>@{{ expires_in }}</code>, and <code>@{{ order_id }}</code>.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-check form-switch mb-0">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $template->exists ? $template->is_active : true))>
                                <span class="form-check-label">Enable this template</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Provider template IDs</h3>
                        <div class="text-secondary">Only add IDs for gateways you use. Each provider may approve a different ID for the same message.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="accordion" id="gatewayMappingAccordion">
                        @foreach($gateways as $key => $gateway)
                            @php
                                $templateId = old('gateway_template_ids.' . $key, data_get($template->gateway_template_ids, $key));
                                $senderId = old('gateway_sender_ids.' . $key, data_get($template->gateway_sender_ids, $key));
                                $hasValue = filled($templateId) || filled($senderId);
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-{{ $key }}">
                                    <button class="accordion-button {{ $hasValue ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#mapping-{{ $key }}">
                                        <span class="fw-semibold">{{ $gateway['name'] }}</span>
                                        @if($hasValue)<span class="badge bg-success-lt ms-2">Mapped</span>@endif
                                    </button>
                                </h2>
                                <div id="mapping-{{ $key }}" class="accordion-collapse collapse {{ $hasValue ? 'show' : '' }}" data-bs-parent="#gatewayMappingAccordion">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            <div class="col-md-7">
                                                <label class="form-label">DLT / Message Template ID</label>
                                                <input name="gateway_template_ids[{{ $key }}]" class="form-control" value="{{ $templateId }}" placeholder="Provider-approved ID">
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Sender ID override</label>
                                                <input name="gateway_sender_ids[{{ $key }}]" class="form-control" value="{{ $senderId }}" placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Delivery options</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Preferred gateway</label>
                        <select name="gateway" class="form-select">
                            <option value="">Use default gateway</option>
                            @foreach($gateways as $key => $gateway)
                                <option value="{{ $key }}" @selected(old('gateway', $template->gateway) === $key)>{{ $gateway['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Default Sender ID override</label>
                        <input name="sender_id" class="form-control" value="{{ old('sender_id', $template->sender_id) }}" placeholder="Optional">
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Template information</h3></div>
                <div class="card-body">
                    <label class="form-label">Template key</label>
                    @if($template->exists)
                        <input type="hidden" name="key" value="{{ $template->key }}">
                        <input class="form-control" value="{{ $template->key }}" readonly>
                        <div class="form-hint">The key is locked because system events use it.</div>
                    @else
                        <input name="key" class="form-control" value="{{ old('key') }}" placeholder="registration_otp" required>
                    @endif
                </div>
            </div>

            <div class="d-grid gap-2">
                <button class="btn btn-primary btn-lg"><i class="ti ti-device-floppy me-1"></i>Save template</button>
                <a href="{{ route('india-sms.templates.index') }}" class="btn btn-outline-secondary">Back to templates</a>
            </div>
        </div>
    </div>
</form>
@endsection
