@extends('core/acl::layouts.guest')

@section('content')
    <h2 class="h3 text-center mb-3">
        {{ trans('core/acl::auth.sign_in_below') }}
    </h2>

    <x-core::form
        :url="route('access.login')"
        method="post"
    >
        <x-core::form.text-input
            :label="trans('core/acl::auth.login.username')"
            name="username"
            :value="old(
                'email',
                BaseHelper::hasDemoModeEnabled() ? config('core.base.general.demo.account.username') : null,
            )"
            :placeholder="trans('core/acl::auth.login.placeholder.username')"
            :required="true"
            error-key="email"
            tabindex="1"
        />

        <x-core::form.text-input
            :label="trans('core/acl::auth.login.password')"
            type="password"
            name="password"
            :value="BaseHelper::hasDemoModeEnabled()
                ? config('core.base.general.demo.account.password')
                : null"
            :placeholder="trans('core/acl::auth.login.placeholder.password')"
            :required="true"
            tabindex="2"
        >
            <x-slot:label-description>
                <a
                    href="{{ route('access.password.request') }}"
                    {{ trans('core/acl::auth.forgot_password.title') }}
                    tabindex="5"
                >{{ trans('core/acl::auth.lost_your_password') }}</a>
            </x-slot:label-description>
        </x-core::form.text-input>

        <x-core::form-group>
            <x-core::form.checkbox
                :label="trans('core/acl::auth.login.remember')"
                name="remember"
                :checked="true"
                tabindex="3"
            />
        </x-core::form-group>

        <div class="form-footer">
            <x-core::button
                type="submit"
                color="primary"
                class="w-100"
                icon="ti ti-login-2"
                tabindex="4"
            >
                {{ trans('core/acl::auth.login.login') }}
            </x-core::button>
        </div>

        {!! apply_filters(BASE_FILTER_AFTER_LOGIN_OR_REGISTER_FORM, null, $model) !!}
    </x-core::form>
@endsection

@push('footer')
    {!! $jsValidator !!}
@endpush
