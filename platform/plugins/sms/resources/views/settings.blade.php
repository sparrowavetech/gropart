@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
{!! Form::open(['url' => route('sms.settings'), 'class' => 'main-setting-form']) !!}
<div class="flexbox-annotated-section">
    <div class="flexbox-annotated-section-annotation">
        <div class="annotated-section-title pd-all-20">
            <h2>{{ trans('plugins/sms::sms.settings.title') }}</h2>
        </div>
        <div class="annotated-section-description pd-all-20 p-none-t">
            <p class="color-note">{{ trans('plugins/sms::sms.settings.description') }}</p>
        </div>
    </div>

    <div class="flexbox-annotated-section-content">
        <div class="wrapper-content pd-all-20">
            <div class="form-group mb-3">
                <label class="text-title-field" for="sms_url">{{ trans('plugins/sms::sms.settings.sms_url') }}</label>
                <input data-counter="500" type="text" class="next-input" name="sms_url" id="sms_url" value="{{ setting('sms_url') }}" placeholder="{{ trans('plugins/sms::sms.settings.sms_url') }}">
                {{ Form::helper(trans('plugins/sms::sms.settings.sms_url_helper')) }}
            </div>
        </div>
    </div>

    <div class="flexbox-annotated-section-content">
        <div class="wrapper-content pd-all-20">
            <div class="form-group mb-3">
                <label class="text-title-field" for="sms_otp_enabled">{{ trans('plugins/sms::sms.sms_otp_enabled') }}
                </label>
                <label class="me-2">
                    <input type="radio" name="sms_otp_enabled" value="1" @if (setting('sms_otp_enabled')) checked @endif>{{ trans('core/setting::setting.general.yes') }}
                </label>
                <label>
                    <input type="radio" name="sms_otp_enabled" value="0" @if (!setting('sms_otp_enabled')) checked @endif>{{ trans('core/setting::setting.general.no') }}
                </label>
            </div>
        </div>
    </div>
</div>
<div class="flexbox-annotated-section" style="border: none">
    <div class="flexbox-annotated-section-annotation">
        &nbsp;
    </div>
    <div class="flexbox-annotated-section-content">
        <button class="btn btn-info" type="submit">{{ trans('plugins/sms::sms.save_settings') }}</button>
    </div>
</div>
{!! Form::close() !!}
@endsection