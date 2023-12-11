@extends('core/acl::layouts.guest')

@section('content')
    <h2 class="h3 text-center mb-3">
        {{ trans('core/acl::auth.forgot_password.title') }}
    </h2>

    <x-core::form
        :url="route('access.password.email')"
        method="post"
    >

        <p class="text-muted mb-4">{!! BaseHelper::clean(trans('core/acl::auth.forgot_password.message')) !!}</p>
        <x-core::form.text-input
            :label="trans('core/acl::auth.login.email')"
            type="email"
            name="email"
            :placeholder="trans('core/acl::auth.login.placeholder.email')"
            :required="true"
        />

        <div class="form-footer">
            <x-core::button
                type="submit"
                color="primary"
                icon="ti ti-mail"
                class="w-100"
            >
                {{ trans('core/acl::auth.forgot_password.submit') }}
            </x-core::button>
        </div>
    </x-core::form>

    <div class="text-center text-muted mt-3">
        <a href="{{ route('access.login') }}">{{ trans('core/acl::auth.back_to_login') }}</a>
    </div>
@endsection

@push('footer')
    {!! $jsValidator !!}
@endpush
