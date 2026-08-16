@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    {!! Form::open(['url' => route('sms.settings'), 'class' => 'main-setting-form']) !!}
        <x-core-setting::section
            :title="trans('plugins/sms::sms.settings.title')"
            :description="trans('plugins/sms::sms.settings.description')"
        >
            <x-core-setting::form-group>
                <label class="text-title-field" for="sms_url">{{ trans('plugins/sms::sms.settings.sms_url') }}</label>
                <input data-counter="500" type="text" class="next-input" name="sms_url" id="sms_url" value="{{ setting('sms_url') }}" placeholder="{{ trans('plugins/sms::sms.settings.sms_url') }}">
                <div class="mt-2 text-muted" style="font-size: 0.85rem; line-height: 1.5;">
                    <p class="mb-1"><strong>{{ __('Example API URL:') }}</strong></p>
                    <pre class="bg-light p-2 border rounded" style="word-break: break-all; white-space: pre-wrap; font-size: 0.8rem; color: #555;">{{ trans('plugins/sms::sms.settings.sms_url_helper') }}</pre>
                    <p class="mb-0 mt-2"><strong>{{ __('Supported Placeholders:') }}</strong></p>
                    <ul class="ps-3 mb-0" style="list-style-type: disc;">
                        <li><code>@{{mobile}}</code> - {{ __('Recipient mobile number') }}</li>
                        <li><code>@{{message}}</code> - {{ __('SMS content message text') }}</li>
                        <li><code>@{{template_id}}</code> - {{ __('DLT Template ID (if applicable)') }}</li>
                    </ul>
                </div>
            </x-core-setting::form-group>

            <input type="hidden" name="sms_otp_enabled" value="0">
            <x-core-setting::on-off
                name="sms_otp_enabled"
                :label="trans('plugins/sms::sms.sms_otp_enabled')"
                :value="setting('sms_otp_enabled')"
            />
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