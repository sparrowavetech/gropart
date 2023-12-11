@switch(setting('captcha_type'))
    @case('v2')
        <x-core::form-group>
            {!! Captcha::display() !!}
        </x-core::form-group>
    @break

    @case('v3')
        @if (setting('captcha_show_disclaimer'))
            <div style="background-color: rgb(232 233 235); border-radius: 4px; padding: 16px; margin-bottom: 16px">
                {{ trans('plugins/captcha::captcha.recaptcha_disclaimer_message') }}
            </div>
        @endif

        {!! Captcha::display() !!}
    @break
@endswitch
