@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>{{ trans('packages/theme::theme.theme') }}</x-core::card.title>
        </x-core::card.header>

        <x-core::card.body>
            <div class="row">
                @foreach (ThemeManager::getThemes() as $key => $theme)
                    <div class="col-md-6 col-lg-4">
                        <x-core::card>
                            <div class="thumbnail">
                                <div class="img-thumbnail-wrap w-100 overflow-y-hidden h-auto">
                                    <img
                                        class="w-100"
                                        src="{{ Theme::getThemeScreenshot($key) }}"
                                        alt="screenshot"
                                    >
                                </div>
                                <div class="caption">
                                    <div
                                        class="col-12"
                                        style="padding: 15px;"
                                    >
                                        <div style="word-break: break-all">
                                            <h4>{{ $theme['name'] }}</h4>
                                            <p>{{ trans('packages/theme::theme.author') }}: {{ Arr::get($theme, 'author') }}
                                            </p>
                                            <p>{{ trans('packages/theme::theme.version') }}:
                                                {{ Arr::get($theme, 'version', get_cms_version()) }}</p>
                                            <p>{{ trans('packages/theme::theme.description') }}:
                                                {{ Arr::get($theme, 'description') }}</p>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div>
                                            @if (setting('theme') && Theme::getThemeName() == $key)
                                                <x-core::button
                                                    type="button"
                                                    color="info"
                                                    :disabled="true"
                                                    icon="ti ti-check"
                                                >
                                                    {{ trans('packages/theme::theme.activated') }}
                                                </x-core::button>
                                            @else
                                                @if (Auth::guard()->user()->hasPermission('theme.activate'))
                                                    <x-core::button
                                                        type="button"
                                                        color="primary"
                                                        icon="ti ti-check"
                                                        class="btn-trigger-active-theme"
                                                        data-theme="{{ $key }}"
                                                    >
                                                        {{ trans('packages/theme::theme.active') }}
                                                    </x-core::button>
                                                @endif
                                                @if (Auth::guard()->user()->hasPermission('theme.remove'))
                                                    <x-core::button
                                                        type="button"
                                                        color="danger"
                                                        icon="ti ti-check"
                                                        class="btn-trigger-remove-theme"
                                                        data-theme="{{ $key }}"
                                                    >
                                                        {{ trans('packages/theme::theme.remove') }}
                                                    </x-core::button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-core::card>
                    </div>
                @endforeach
            </div>
        </x-core::card.body>
    </x-core::card>

    <x-core::modal
        id="remove-theme-modal"
        :title="trans('packages/theme::theme.remove_theme')"
        type="danger"
        button-id="confirm-remove-theme-button"
        :button-label="trans('packages/theme::theme.remove_theme_confirm_yes')"
    >
        {!! trans('packages/theme::theme.remove_theme_confirm_message') !!}
    </x-core::modal>
@endsection
