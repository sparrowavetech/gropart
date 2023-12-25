<input
    id="{{ $uniqueId }}"
    name="{{ $name }}"
    type="hidden"
>

@if (setting('captcha_show_disclaimer'))
    <div style="display: block; background-color: rgb(232 233 235); border-radius: 4px; padding: 16px; margin-bottom: 16px; ">
        {{ trans('plugins/captcha::captcha.recaptcha_disclaimer_message') }}
    </div>
@endif
