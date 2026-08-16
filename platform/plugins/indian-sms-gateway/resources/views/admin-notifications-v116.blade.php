@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
@include('plugins/india-sms-gateway::partials.alerts')
@include('plugins/india-sms-gateway::partials.nav')

<form method="post" action="{{ route('india-sms.admin-notifications.update') }}">
    @csrf
    @method('PUT')

    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card border-primary">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Admin new-order SMS</h3>
                        <p class="card-subtitle">Add the mobile number that will receive an SMS whenever a customer places a new order.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="admin_new_order_sms" value="1" @checked($settings->bool('admin_new_order_sms', true))>
                            <span class="form-check-label"><strong>Send new-order SMS to admin</strong></span>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required" for="admin_phone_numbers">Admin mobile number(s)</label>
                        <textarea
                            id="admin_phone_numbers"
                            name="admin_phone_numbers"
                            class="form-control form-control-lg @error('admin_phone_numbers') is-invalid @enderror"
                            rows="4"
                            placeholder="9876543210&#10;018XXXXXXXX"
                            autofocus
                        >{{ old('admin_phone_numbers', $settings->get('admin_phone_numbers', '')) }}</textarea>
                        @error('admin_phone_numbers')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint mt-2">
                            Use India mobile numbers. Add multiple numbers using commas, spaces, semicolons, or separate lines.
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-1"></i>
                        The <strong>Admin: New Order Alert</strong> SMS template will be sent to every number saved here.
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a class="btn btn-outline-secondary" href="{{ route('india-sms.templates.index') }}">
                        <i class="ti ti-template me-1"></i>Edit message template
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>Save admin number
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Current status</h3></div>
                <div class="card-body">
                    @php($hasAdminNumber = trim((string) $settings->get('admin_phone_numbers', '')) !== '')
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span>Admin notification</span>
                        <span class="badge {{ $settings->bool('admin_new_order_sms', true) ? 'bg-success-lt' : 'bg-secondary-lt' }}">
                            {{ $settings->bool('admin_new_order_sms', true) ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span>Recipient number</span>
                        <span class="badge {{ $hasAdminNumber ? 'bg-success-lt' : 'bg-danger-lt' }}">
                            {{ $hasAdminNumber ? 'Saved' : 'Missing' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
