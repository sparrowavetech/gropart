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
@endsection

@section('footer')
    <x-core::button
        tag="a"
        color="primary"
        :href="URL::signedRoute('installers.requirements.index', [], \Carbon\Carbon::now()->addMinutes(30))"
    >
        {{ trans('packages/installer::installer.welcome.next') }}
    </x-core::button>
@endsection
