@extends('core/acl::layouts.guest')

@section('content')
    <h2 class="h3 text-center mb-3">
        {{ trans('core/acl::auth.reset_password') }}
    </h2>

    <x-core::form
        :url="route('access.password.reset.post')"
        method="post"
    >
        <input
            type="hidden"
            name="token"
            value="{{ $token }}"
        />

        <x-core::form.text-input
            :label="trans('core/acl::auth.reset.email')"
            type="email"
            name="email"
            :value="old('email', $email)"
            :placeholder="trans('core/acl::auth.login.placeholder.email')"
        />

        <x-core::form.text-input
            :label="trans('core/acl::auth.reset.new_password')"
            type="password"
            name="password"
            :placeholder="trans('core/acl::auth.reset.placeholder.new_password')"
        />

        <x-core::form.text-input
            :label="trans('core/acl::auth.reset.password_confirmation')"
            type="password"
            name="password_confirmation"
            :placeholder="trans('core/acl::auth.reset.placeholder.new_password_confirmation')"
        />

        <div class="form-footer">
            <x-core::button
                type="submit"
                color="primary"
                class="w-100"
            >
                {{ trans('core/acl::auth.reset.update') }}
            </x-core::button>
        </div>
    </x-core::form>
@endsection

@push('footer')
    {!! $jsValidator !!}
@endpush
