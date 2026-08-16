@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
@if(($license['activated'] ?? false) && Route::has('india-sms.index'))
    @include('plugins/india-sms-gateway::partials.nav')
@endif

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-0">Ashikul License</h3>
                    <div class="text-secondary mt-1">Activation is verified securely by the Ashikul License Panel.</div>
                </div>
                @if($license['activated'] ?? false)
                    <span class="badge {{ ($license['grace_active'] ?? false) ? 'bg-warning-lt' : 'bg-success-lt' }}">
                        {{ ($license['grace_active'] ?? false) ? 'Offline grace' : 'Active' }}
                    </span>
                @else
                    <span class="badge bg-danger-lt">{{ ucfirst(str_replace('_', ' ', $license['status'] ?? 'inactive')) }}</span>
                @endif
            </div>

            <div class="card-body">
            @if(!($license['activated'] ?? false))
                @php
                    $trialLimit = (int) ($license['trial_limit'] ?? 100);
                    $trialUsed = (int) ($license['trial_used'] ?? 0);
                    $trialRemaining = (int) ($license['trial_remaining'] ?? max(0, $trialLimit - $trialUsed));
                    $trialPercent = $trialLimit > 0 ? min(100, round(($trialUsed / $trialLimit) * 100)) : 100;
                @endphp

                <div class="alert {{ $trialRemaining > 0 ? 'alert-info' : 'alert-danger' }}">
                    @if($trialRemaining > 0)
                        <strong>Free trial is active.</strong>
                        You can send {{ $trialRemaining }} more successfully accepted SMS before license activation is required.
                    @else
                        <strong>Your 100-SMS trial has ended.</strong>
                        Activate a license to continue sending OTP, order notifications and test SMS.
                    @endif
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <div>
                                <strong>Trial usage</strong>
                                <div class="text-secondary">Only successfully accepted SMS sent after this trial started count toward the limit.</div>
                            </div>
                            <div class="text-end">
                                <strong>{{ $trialUsed }} / {{ $trialLimit }}</strong>
                                <div class="text-secondary">{{ $trialRemaining }} remaining</div>
                            </div>
                        </div>
                        <div class="progress">
                            <div
                                class="progress-bar {{ $trialRemaining > 0 ? 'bg-primary' : 'bg-danger' }}"
                                style="width: {{ $trialPercent }}%"
                                role="progressbar"
                                aria-valuenow="{{ $trialPercent }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                    </div>
                </div>
            @endif
                @if($license['activated'] ?? false)
                    <div class="alert {{ ($license['grace_active'] ?? false) ? 'alert-warning' : 'alert-success' }}">
                        <strong>{{ ($license['grace_active'] ?? false) ? 'License server temporarily unavailable.' : 'License verified.' }}</strong>
                        <div class="mt-1">
                            {{ ($license['grace_active'] ?? false) ? 'The plugin is running inside its temporary offline grace period.' : 'All licensed features are available on this domain.' }}
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-vcenter">
                            <tbody>
                                <tr><th style="width: 220px">Product</th><td><code>{{ $license['product_id'] ?? '—' }}</code></td></tr>
                                <tr><th>Licensed domain</th><td><code>{{ $license['domain'] ?? '—' }}</code></td></tr>
                                <tr><th>License key</th><td><code>••••{{ $license['last4'] ?? '—' }}</code></td></tr>
                                <tr><th>License type</th><td>{{ ($license['license_type'] ?? '') ?: 'Lifetime / managed by server' }}</td></tr>
                                <tr><th>Expires</th><td>{{ ($license['expires_at'] ?? '') ?: 'Never' }}</td></tr>
                                <tr><th>Activated on</th><td>{{ ($license['activated_at'] ?? '') ?: '—' }}</td></tr>
                                <tr><th>Last verified</th><td>{{ ($license['last_valid_at'] ?? '') ?: '—' }}</td></tr>
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($license['last_error']))
                        <div class="alert alert-warning"><strong>Last license message:</strong> {{ $license['last_error'] }}</div>
                    @endif

                    <div class="d-flex flex-wrap gap-2">
                        <form method="post" action="{{ route('india-sms.activation.refresh') }}">
                            @csrf
                            <button class="btn btn-outline-primary"><i class="ti ti-refresh me-1"></i>Check license now</button>
                        </form>
                        <form method="post" action="{{ route('india-sms.activation.destroy') }}" onsubmit="return confirm('Deactivate this license and release the current domain?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger"><i class="ti ti-key-off me-1"></i>Deactivate license</button>
                        </form>
                    </div>
                @else
                    @if(!empty($license['last_error']))
                        <div class="alert alert-danger">{{ $license['last_error'] }}</div>
                    @endif

                    <div class="alert alert-info">
                        Activate now or continue using the free trial. This installation will be registered to <code>{{ $license['current_domain'] ?? request()->getHost() }}</code> through the Ashikul License Panel.
                    </div>

                    <form method="post" action="{{ route('india-sms.activation.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label required">License key</label>
                            <input type="text" name="activation_key" class="form-control" value="{{ old('activation_key') }}" placeholder="Enter your license key" autocomplete="off" required>
                        </div>
                        <button class="btn btn-primary"><i class="ti ti-key me-1"></i>Activate license</button>
                    </form>
                @endif
            </div>

            <div class="card-footer d-flex flex-wrap justify-content-between gap-2">
                <span class="text-secondary">License server: <code>{{ parse_url($license['server_url'] ?? '', PHP_URL_HOST) ?: 'Not configured' }}</code></span>
                <div class="d-flex gap-2">
                    <a href="https://ashikul.info" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm">ashikul.info</a>
                    <a href="https://wa.me/8801671410831" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm"><i class="ti ti-brand-whatsapp me-1"></i>WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
