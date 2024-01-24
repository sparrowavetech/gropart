@extends('packages/installer::layouts.master')

@section('pageTitle')
    {{ trans('packages/installer::installer.welcome.pageTitle') }}
@endsection

@section('header')
    <x-core::card.title>
        {{ trans('packages/installer::installer.welcome.title') }}
    </x-core::card.title>
@endsection

@section('content')
    <p class="text-secondary">
        {{ trans('packages/installer::installer.welcome.message') }}
    </p>

    <form method="POST" action="{{ route('installers.welcome.next') }}" id="welcome-form">
        @csrf

        <x-core::form.select
            :label="trans('packages/installer::installer.welcome.language')"
            name="language"
            :options="$languages"
            :value="old('language', app()->getLocale())"
        />
    </form>
@endsection

@section('footer')
    <x-core::button
        type="submit"
        color="primary"
        form="welcome-form"
    >
        {{ trans('packages/installer::installer.welcome.next') }}
    </x-core::button>
@endsection
