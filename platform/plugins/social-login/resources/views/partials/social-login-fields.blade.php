<x-core::form.on-off.checkbox
    name="social_login_enable"
    :label="trans('plugins/social-login::social-login.settings.enable')"
    :checked="old('social_login_enable', setting('social_login_enable'))"
    data-bb-toggle="collapse"
    data-bb-target=".social-login-settings"
    :wrapper="false"
/>

<x-core::form.fieldset
    class="social-login-settings mt-3"
    @style(['display: none;' => !old('social_login_enable', setting('social_login_enable'))])
>
    @foreach (SocialService::getProviders() as $provider => $item)
        <x-core::form.on-off.checkbox
            name="social_login_{{ $provider }}_enable"
            :label="trans(sprintf('plugins/social-login::social-login.settings.%s.enable', $provider))"
            :checked="old($provider, SocialService::getProviderEnabled($provider))"
            data-bb-toggle="collapse"
            data-bb-target=".social-login-{{ $provider }}-settings"
        />

        <x-core::form.fieldset
            class="social-login-{{ $provider }}-settings"
            @style(['display: none;' => ! old(sprintf('social_login_%s_enable', $provider), SocialService::getProviderEnabled($provider))])
        >
            @foreach ($item['data'] as $input)
                @php($isDisabled = in_array(app()->environment(), SocialService::getEnvDisableData()) && in_array($input, Arr::get($item, 'disable', [])))

                <x-core::form.text-input
                    :name="'social_login_' . $provider . '_' . $input"
                    :label="trans(
                        'plugins/social-login::social-login.settings.' . $provider . '.' . $input,
                    )"
                    :value="$isDisabled
                        ? SocialService::getDataDisable($provider . '_' . $input)
                        : setting('social_login_' . $provider . '_' . $input)"
                    :disabled="$isDisabled"
                    :readonly="$isDisabled"
                />
            @endforeach

            <x-core::alert>
                {!! BaseHelper::clean(
                    trans('plugins/social-login::social-login.settings.' . $provider . '.helper', [
                        'callback' => '<code class=\'text-danger\'>' . route('auth.social.callback', $provider) . '</code>',
                    ]),
                ) !!}
            </x-core::alert>
        </x-core::form.fieldset>
    @endforeach
</x-core::form.fieldset>
