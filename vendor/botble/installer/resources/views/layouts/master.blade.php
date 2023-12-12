<!DOCTYPE html>
<html lang="{{ Str::replace('_', '-', App::getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <title>@yield('pageTitle', trans('packages/installer::installer.title'))</title>

    <link
        href="{{ asset('vendor/core/core/base/images/favicon.png') }}"
        rel="icon"
    >

    @include('core/base::components.layouts.header')

    <link
        href="{{ asset('vendor/core/packages/installer/css/style.css') }}?v={{ get_cms_version() }}"
        rel="stylesheet"
    />

    <style>
        [v-cloak],
        [x-cloak] {
            display: none;
        }
    </style>

    @php
        Assets::getFacadeRoot()
            ->removeStyles([
                'fontawesome',
                'select2',
                'custom-scrollbar',
                'datepicker',
                'spectrum',
                'fancybox',
            ])
            ->removeScripts([
                'excanvas',
                'ie8-fix',
                'modernizr',
                'select2',
                'datepicker',
                'cookie',
                'toastr',
                'custom-scrollbar',
                'stickytableheaders',
                'jquery-waypoints',
                'spectrum',
                'fancybox',
                'fslightbox',
            ]);
    @endphp
    {!!  Assets::renderHeader(['core']) !!}

    <link
        href="{{ BaseHelper::getGoogleFontsURL() }}"
        rel="preconnect"
    >
    <link
        href="{{ BaseHelper::getGoogleFontsURL('css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap') }}"
        rel="stylesheet"
    >

    @yield('styles')
</head>

<body>
    @php
        $currentStep = match (true) {
            Route::is('installers.welcome') => 1,
            Route::is('installers.requirements.index') => 2,
            Route::is('installers.environments.index') => 3,
            Route::is('installers.accounts.index') => 4,
            Route::is('installers.licenses.index') => 5,
            Route::is('installers.final') => 6,
            default => 1,
        };
    @endphp

    <div class="page-wrapper justify-content-center min-h-full">
        <div class="page-body">
            <div class="container-xl installer-container">
                <div class="row mb-6">
                    <div class="col">
                        <h2 class="h1 page-title justify-content-center text-white">
                            {{ trans('packages/installer::installer.title') }}
                        </h2>
                    </div>
                </div>

                <div class="row installer-wrapper">
                    <div class="col-md-3 p-4">
                        <div class="steps-backdrop"></div>
                        <x-core::step :counter="true" :vertical="true">
                            <x-core::step.item :is-active="$currentStep === 1">
                                @if ($currentStep > 1)
                                    <a href="{{ route('installers.welcome') }}">{{ trans('packages/installer::installer.welcome.title') }}</a>
                                @else
                                    {{ trans('packages/installer::installer.welcome.title') }}
                                @endif
                            </x-core::step.item>
                            <x-core::step.item :is-active="$currentStep === 2">
                                @if ($currentStep > 2)
                                    <a href="{{ route('installers.requirements.index') }}">{{ trans('packages/installer::installer.requirements.title') }}</a>
                                @else
                                    {{ trans('packages/installer::installer.requirements.title') }}
                                @endif
                            </x-core::step.item>
                            <x-core::step.item :is-active="$currentStep === 3">
                                @if ($currentStep > 3)
                                    <a href="{{ route('installers.environments.index') }}">{{ trans('packages/installer::installer.environment.wizard.title') }}</a>
                                @else
                                    {{ trans('packages/installer::installer.environment.wizard.title') }}
                                @endif
                            </x-core::step.item>
                            <x-core::step.item :is-active="$currentStep === 4">
                                @if ($currentStep > 4)
                                    <a href="{{ route('installers.accounts.index') }}">{{ trans('packages/installer::installer.createAccount.title') }}</a>
                                @else
                                    {{ trans('packages/installer::installer.createAccount.title') }}
                                @endif
                            </x-core::step.item>
                            <x-core::step.item :is-active="$currentStep === 5">
                                @if ($currentStep > 5)
                                    <a href="{{ route('installers.licenses.index') }}">{{ trans('packages/installer::installer.license.title') }}</a>
                                @else
                                    {{ trans('packages/installer::installer.license.title') }}
                                @endif
                            </x-core::step.item>

                            <x-core::step.item :is-active="$currentStep === 6">
                                {{ trans('packages/installer::installer.final.title') }}
                            </x-core::step.item>
                        </x-core::step>
                    </div>
                    <div class="col-md-9 p-0">
                        <x-core::card class="h-100">
                            @hasSection('header')
                                <x-core::card.header>
                                    @yield('header')
                                </x-core::card.header>
                            @endif

                            <x-core::card.body>
                                @include('packages/installer::partials.alert')

                                @yield('content')
                            </x-core::card.body>

                            @hasSection('footer')
                                <x-core::card.footer class="d-flex justify-content-end">
                                    @yield('footer')
                                </x-core::card.footer>
                            @endif
                        </x-core::card>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {!!  Assets::renderFooter() !!}

    <script type="text/javascript">
        var BotbleVariables = BotbleVariables || {
            languages: {
                notices_msg: {{ Js::from(trans('core/base::notices')) }},
            },
        };
    </script>

    @push('footer')
        @if (Session::has('success_msg') || Session::has('error_msg') || (isset($errors) && $errors->any()) || isset($error_msg))
            <script type="text/javascript">
                $(function() {
                    @if (Session::has('success_msg'))
                    Botble.showSuccess('{{ session('success_msg') }}');
                    @endif
                    @if (Session::has('error_msg'))
                    Botble.showError('{{ session('error_msg') }}');
                    @endif
                    @if (isset($error_msg))
                    Botble.showError('{{ $error_msg }}');
                    @endif
                    @if (isset($errors))
                    @foreach ($errors->all() as $error)
                    Botble.showError('{{ $error }}');
                    @endforeach
                    @endif
                })
            </script>
        @endif
    @endpush

    @yield('scripts')
</body>
</html>
