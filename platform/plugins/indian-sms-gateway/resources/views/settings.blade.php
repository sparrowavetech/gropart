@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
@include('plugins/india-sms-gateway::partials.alerts')
@include('plugins/india-sms-gateway::partials.nav')

<form method="post" action="{{ route('india-sms.settings.update') }}">
    @csrf
    @method('PUT')

    <div class="row row-cards">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">General sending</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="enabled" value="1" @checked($settings->bool('enabled'))>
                                <span class="form-check-label">Enable India SMS service</span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Default gateway</label>
                            <select name="default_gateway" class="form-select">
                                @foreach($gateways as $key => $gateway)
                                    <option value="{{ $key }}" @selected($settings->get('default_gateway', 'msg91') === $key)>{{ $gateway['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fallback gateway</label>
                            <select name="fallback_gateway" class="form-select">
                                <option value="">None</option>
                                @foreach($gateways as $key => $gateway)
                                    <option value="{{ $key }}" @selected($settings->get('fallback_gateway') === $key)>{{ $gateway['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Second fallback</label>
                            <select name="second_fallback_gateway" class="form-select">
                                <option value="">None</option>
                                @foreach($gateways as $key => $gateway)
                                    <option value="{{ $key }}" @selected($settings->get('second_fallback_gateway') === $key)>{{ $gateway['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Request timeout (seconds)</label><input type="number" name="request_timeout" class="form-control" value="{{ $settings->get('request_timeout', 20) }}"></div>
                        <div class="col-md-4"><label class="form-label">Connect timeout</label><input type="number" name="connect_timeout" class="form-control" value="{{ $settings->get('connect_timeout', 5) }}"></div>
                        <div class="col-md-4"><label class="form-label">Retry attempts</label><input type="number" name="retry_attempts" class="form-control" value="{{ $settings->get('retry_attempts', 1) }}"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 border-primary" id="admin-sms-recipient">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title mb-1">Admin SMS recipient</h3>
                        <small class="text-secondary">Choose where new-order alerts will be delivered. A dedicated page is also available from <strong>Indian SMS → Admin SMS</strong>.</small>
                    </div>
                    <a class="btn btn-primary btn-sm" href="{{ route('india-sms.admin-notifications') }}"><i class="ti ti-bell-ringing me-1"></i>Open Admin SMS page</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="admin_new_order_sms" value="1" @checked($settings->bool('admin_new_order_sms', true))>
                                <span class="form-check-label"><strong>Send SMS to admin when a new order is placed</strong></span>
                            </label>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><strong>Admin mobile number(s)</strong> <span class="text-danger">*</span></label>
                            <textarea name="admin_phone_numbers" class="form-control @error('admin_phone_numbers') is-invalid @enderror" rows="2" placeholder="9876543210, 018XXXXXXXX">{{ old('admin_phone_numbers', $settings->get('admin_phone_numbers')) }}</textarea>
                            @error('admin_phone_numbers')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-hint">Enter India mobile numbers. Separate multiple recipients with commas, spaces, semicolons or new lines.</small>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <i class="ti ti-bell-ringing me-1"></i> When enabled, the <strong>Admin: New Order Alert</strong> template is sent to every number entered above.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Queue & rate limits</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="queue_enabled" value="1" @checked($settings->bool('queue_enabled'))>
                                <span class="form-check-label">Use Laravel queue for automatic SMS</span>
                            </label>
                        </div>
                        <div class="col-md-6"><label class="form-label">Queue name</label><input name="queue_name" class="form-control" value="{{ $settings->get('queue_name', 'sms') }}"></div>
                        <div class="col-12">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="queue_worker_confirmed" value="1" @checked($settings->bool('queue_worker_confirmed'))>
                                <span class="form-check-label">A queue worker is continuously running on this server</span>
                            </label>
                            <small class="form-hint">When this is not checked, OTP and order notifications are sent synchronously so messages do not remain stuck in the queue.</small>
                        </div>
                        <div class="col-md-6"><label class="form-label">Max SMS per recipient/hour</label><input type="number" name="max_per_recipient_hour" class="form-control" value="{{ $settings->get('max_per_recipient_hour', 20) }}"><small class="form-hint">0 disables this limit.</small></div>
                        <div class="col-md-6"><label class="form-label">Global SMS per minute</label><input type="number" name="global_per_minute" class="form-control" value="{{ $settings->get('global_per_minute', 100) }}"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">OTP & frontend verification</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="otp_enabled" value="1" @checked($settings->bool('otp_enabled', true))>
                                <span class="form-check-label">Enable secure OTP service</span>
                            </label>
                        </div>
                        @foreach([
                            'otp_length' => ['OTP code length', 6],
                            'otp_ttl' => ['OTP validity (seconds)', 300],
                            'otp_max_attempts' => ['Maximum verification attempts', 5],
                            'otp_resend_cooldown' => ['Resend cooldown (seconds)', 60],
                            'otp_requests_phone_hour' => ['Requests per phone/hour', 5],
                            'otp_requests_ip_hour' => ['Requests per IP/hour', 20],
                        ] as $key => [$label, $default])
                            <div class="col-md-4"><label class="form-label">{{ $label }}</label><input type="number" name="{{ $key }}" class="form-control" value="{{ $settings->get($key, $default) }}"></div>
                        @endforeach
                        <div class="col-md-4"><label class="form-label">Remember verified phone (minutes)</label><input type="number" name="verification_remember_minutes" class="form-control" value="{{ $settings->get('verification_remember_minutes', 1440) }}"><small class="form-hint">Used to skip repeated checkout verification.</small></div>
                        <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="registration_otp" value="1" @checked($settings->bool('registration_otp'))><span class="form-check-label">Require OTP before customer registration</span></label></div>
                        <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="login_otp" value="1" @checked($settings->bool('login_otp'))><span class="form-check-label">Enable OTP-only login option on customer login</span></label></div>
                        <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="password_reset_otp" value="1" @checked($settings->bool('password_reset_otp'))><span class="form-check-label">Allow password reset with mobile OTP</span></label></div>
                        <div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="checkout_otp" value="1" @checked($settings->bool('checkout_otp'))><span class="form-check-label">Require OTP before checkout submission</span></label></div>
                        <div class="col-md-6"><label class="form-label">Minimum order total for checkout OTP</label><input type="number" step="0.01" name="checkout_min_total" class="form-control" value="{{ $settings->get('checkout_min_total', 0) }}"></div>
                        <div class="col-md-6 d-flex align-items-end"><label class="form-check"><input class="form-check-input" type="checkbox" name="skip_verified_checkout" value="1" @checked($settings->bool('skip_verified_checkout', true))><span class="form-check-label">Skip checkout OTP for recently verified phones</span></label></div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 border-primary" id="new-order-notifications">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title mb-1">New order SMS notifications</h3>
                        <small class="text-secondary">Control who receives an SMS immediately after a new order is placed.</small>
                    </div>
                    <span class="badge bg-primary-lt">Ecommerce</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex">
                            <div><i class="ti ti-info-circle me-2"></i></div>
                            <div>These switches use the editable <strong>Customer: New Order Confirmation</strong> and <strong>Admin: New Order Alert</strong> templates from the SMS Templates page.</div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="ecommerce_notifications_enabled" value="1" @checked($settings->bool('ecommerce_notifications_enabled', true))>
                                <span class="form-check-label"><strong>Enable ecommerce SMS notifications</strong></span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-sm h-100">
                                <div class="card-body">
                                    <label class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="customer_new_order_sms" value="1" @checked($settings->bool('customer_new_order_sms', true))>
                                        <span class="form-check-label"><strong>New order SMS to customer</strong></span>
                                    </label>
                                    <p class="text-secondary mb-0">Sends the order number, total and current status to the customer phone number saved with the order.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <strong>Admin new-order alert</strong>
                                        <span class="badge {{ $settings->bool('admin_new_order_sms', true) && trim((string) $settings->get('admin_phone_numbers')) !== '' ? 'bg-success-lt' : 'bg-warning-lt' }}">
                                            {{ $settings->bool('admin_new_order_sms', true) && trim((string) $settings->get('admin_phone_numbers')) !== '' ? 'Configured' : 'Needs setup' }}
                                        </span>
                                    </div>
                                    <p class="text-secondary mb-2">Admin recipient numbers are configured in the dedicated Admin SMS recipient card above.</p>
                                    <a href="#admin-sms-recipient" class="btn btn-outline-primary btn-sm"><i class="ti ti-user-cog me-1"></i>Configure admin recipient</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="customer_status_sms" value="1" @checked($settings->bool('customer_status_sms', true))>
                                <span class="form-check-label">Order status change SMS</span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="customer_payment_sms" value="1" @checked($settings->bool('customer_payment_sms', true))>
                                <span class="form-check-label">Payment confirmation SMS</span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="customer_shipping_sms" value="1" @checked($settings->bool('customer_shipping_sms', true))>
                                <span class="form-check-label">Shipping status SMS</span>
                            </label>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('india-sms.templates.index') }}">
                                <i class="ti ti-message-cog me-1"></i>Edit SMS templates
                            </a>
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('india-sms.logs.index') }}">
                                <i class="ti ti-list-details me-1"></i>View delivery logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card position-sticky" style="top:1rem">
                <div class="card-header"><h3 class="card-title">India routing</h3></div>
                <div class="card-body">
                    <p class="text-secondary">Accepted formats:</p>
                    <ul><li>01712345678</li><li>91712345678</li><li>+91712345678</li></ul>
                    <p>All numbers are normalized to <code>91XXXXXXXXX</code>. Other countries are rejected.</p>
                    <hr>
                    <p class="mb-2"><strong>Active frontend features</strong></p>
                    <ul class="mb-0">
                        <li>Registration OTP gate</li>
                        <li>OTP-only customer login</li>
                        <li>Password reset by OTP</li>
                        <li>Checkout OTP gate</li>
                        <li>New-order admin/customer SMS</li>
                        <li>Order-status customer SMS</li>
                    </ul>
                </div>
                <div class="card-footer"><button class="btn btn-primary w-100"><i class="ti ti-device-floppy me-1"></i>Save all settings</button></div>
            </div>
        </div>
    </div>
</form>
@endsection
