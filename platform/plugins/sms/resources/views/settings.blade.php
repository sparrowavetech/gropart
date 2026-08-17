@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    $fieldClass = 'form-control';
    $apiExample = static fn (string $value) => '<pre class="bg-light p-2 border rounded mb-0 text-muted" style="word-break: break-all; white-space: pre-wrap; font-size: 0.82rem;">' . e($value) . '</pre>';
@endphp

@section('content')
    {!! Form::open(['url' => route('sms.settings'), 'class' => 'main-setting-form']) !!}
        <x-core-setting::section
            :title="trans('plugins/sms::sms.settings.title')"
            :description="trans('plugins/sms::sms.settings.description')"
        >
            <div class="border rounded p-3 mb-3">
                <h3 class="mb-3">{{ trans('plugins/sms::sms.settings.api_configuration') }}</h3>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="text-title-field" for="sms_base_api_url">{{ trans('plugins/sms::sms.settings.base_api_url') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_base_api_url" id="sms_base_api_url" value="{{ $smsConfig['sms_base_api_url'] }}">
                    </div>
                    <div class="col-md-4">
                        <label class="text-title-field" for="sms_user">{{ trans('plugins/sms::sms.settings.user') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_user" id="sms_user" value="{{ $smsConfig['sms_user'] }}">
                    </div>
                    <div class="col-md-4">
                        <label class="text-title-field" for="sms_password">{{ trans('plugins/sms::sms.settings.password') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_password" id="sms_password" value="{{ $smsConfig['sms_password'] }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="text-title-field" for="sms_send_api_call">{{ trans('plugins/sms::sms.settings.send_api_call') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_send_api_call" id="sms_send_api_call" value="{{ $smsConfig['sms_send_api_call'] }}" placeholder="SendSMS">
                    </div>
                    <div class="col-md-3">
                        <label class="text-title-field" for="sms_sender_id">{{ trans('plugins/sms::sms.settings.sender_id') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_sender_id" id="sms_sender_id" value="{{ $smsConfig['sms_sender_id'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="text-title-field" for="sms_channel">{{ trans('plugins/sms::sms.settings.channel') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_channel" id="sms_channel" value="{{ $smsConfig['sms_channel'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="text-title-field" for="sms_dcs">{{ trans('plugins/sms::sms.settings.dcs') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_dcs" id="sms_dcs" value="{{ $smsConfig['sms_dcs'] }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="text-title-field" for="sms_flashsms">{{ trans('plugins/sms::sms.settings.flashsms') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_flashsms" id="sms_flashsms" value="{{ $smsConfig['sms_flashsms'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="text-title-field" for="sms_route">{{ trans('plugins/sms::sms.settings.route') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_route" id="sms_route" value="{{ $smsConfig['sms_route'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="text-title-field" for="sms_dlt_template_id">{{ trans('plugins/sms::sms.settings.dlt_template_id') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_dlt_template_id" id="sms_dlt_template_id" value="{{ $smsConfig['sms_dlt_template_id'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="text-title-field" for="sms_peid">{{ trans('plugins/sms::sms.settings.peid') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_peid" id="sms_peid" value="{{ $smsConfig['sms_peid'] }}">
                    </div>
                </div>

                <div class="mb-3">
                    <strong>{{ trans('plugins/sms::sms.settings.example_api_url') }}</strong>
                    {!! $apiExample(trans('plugins/sms::sms.settings.sms_url_helper')) !!}
                </div>

                <div class="text-muted">
                    <strong>{{ trans('plugins/sms::sms.settings.supported_placeholders') }}</strong>
                    <ul class="ps-3 mb-0">
                        <li><code>@{{mobile}}</code> - {{ __('Recipient mobile number') }}</li>
                        <li><code>@{{message}}</code> - {{ __('SMS content message text') }}</li>
                        <li><code>@{{template_id}}</code> - {{ __('DLT Template ID') }}</li>
                    </ul>
                </div>
            </div>

            <div class="border rounded p-3 mb-3">
                <h3 class="mb-3">{{ trans('plugins/sms::sms.settings.delivery_configuration') }}</h3>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="text-title-field" for="sms_delivery_api_call">{{ trans('plugins/sms::sms.settings.delivery_api_call') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_delivery_api_call" id="sms_delivery_api_call" value="{{ $smsConfig['sms_delivery_api_call'] }}" placeholder="GetDelivery">
                    </div>
                </div>

                <div class="mb-3">
                    <strong>{{ trans('plugins/sms::sms.settings.example_api_url') }}</strong>
                    {!! $apiExample(trans('plugins/sms::sms.settings.sms_delivery_report_url_helper')) !!}
                </div>

                <div class="text-muted">
                    <strong>{{ trans('plugins/sms::sms.settings.supported_placeholders') }}</strong>
                    <ul class="ps-3 mb-0">
                        <li><code>Jobid=@{{job_id}}</code> - {{ __('Last sent SMS Job ID') }}</li>
                        <li><code>User=@{{user}}</code> - {{ __('From base configuration') }}</li>
                        <li><code>Password=@{{password}}</code> - {{ __('From base configuration') }}</li>
                    </ul>
                </div>
            </div>

            <div class="border rounded p-3 mb-3">
                <h3 class="mb-3">{{ trans('plugins/sms::sms.settings.balance_configuration') }}</h3>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="text-title-field" for="sms_balance_api_call">{{ trans('plugins/sms::sms.settings.balance_api_call') }}</label>
                        <input type="text" class="{{ $fieldClass }}" name="sms_balance_api_call" id="sms_balance_api_call" value="{{ $smsConfig['sms_balance_api_call'] }}" placeholder="GetBalance">
                    </div>
                </div>

                <div class="mb-3">
                    <strong>{{ trans('plugins/sms::sms.settings.example_api_url') }}</strong>
                    {!! $apiExample(trans('plugins/sms::sms.settings.sms_balance_url_helper')) !!}
                </div>

                <div class="text-muted">
                    <strong>{{ trans('plugins/sms::sms.settings.supported_placeholders') }}</strong>
                    <ul class="ps-3 mb-0">
                        <li><code>User=@{{user}}</code> - {{ __('From base configuration') }}</li>
                        <li><code>Password=@{{password}}</code> - {{ __('From base configuration') }}</li>
                    </ul>
                </div>
            </div>

            <input type="hidden" name="sms_login_otp_enabled" value="0">
            <x-core-setting::on-off
                name="sms_login_otp_enabled"
                :label="trans('plugins/sms::sms.settings.enable_login_otp')"
                :value="setting('sms_login_otp_enabled')"
            />

            <input type="hidden" name="sms_registration_otp_enabled" value="0">
            <x-core-setting::on-off
                name="sms_registration_otp_enabled"
                :label="trans('plugins/sms::sms.settings.enable_registration_otp')"
                :value="setting('sms_registration_otp_enabled', setting('sms_otp_enabled'))"
            />
        </x-core-setting::section>

        <x-core-setting::section
            :title="trans('plugins/sms::sms.settings.balance_title')"
            :description="trans('plugins/sms::sms.settings.balance_description')"
        >
            @php
                $balance = $smsBalance['balance'] ?? [];
            @endphp

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="text-muted small text-uppercase">{{ trans('plugins/sms::sms.settings.promo_balance') }}</div>
                        <div class="fs-1 fw-bold text-success mt-2">{{ $balance['Promo'] ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="text-muted small text-uppercase">{{ trans('plugins/sms::sms.settings.trans_balance') }}</div>
                        <div class="fs-1 fw-bold text-primary mt-2">{{ $balance['Trans'] ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div @class(['alert mt-3 mb-0', 'alert-success' => $smsBalance['success'] ?? false, 'alert-warning' => ! ($smsBalance['success'] ?? false)])>
                {{ ($smsBalance['success'] ?? false) ? trans('plugins/sms::sms.settings.balance_updated') : ($smsBalance['message'] ?? trans('plugins/sms::sms.settings.balance_unavailable')) }}
            </div>
        </x-core-setting::section>

        <div class="flexbox-annotated-section" style="border: none">
            <div class="flexbox-annotated-section-annotation">
                &nbsp;
            </div>
            <div class="flexbox-annotated-section-content">
                <button class="btn btn-success" type="submit">{{ trans('plugins/sms::sms.save_settings') }}</button>
            </div>
        </div>
    {!! Form::close() !!}
@stop
