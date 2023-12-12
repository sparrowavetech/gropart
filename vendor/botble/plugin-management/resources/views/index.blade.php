@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header-action')
    @if (config('packages.plugin-management.general.enable_marketplace_feature') &&
            auth()->user()->hasPermission('plugins.marketplace'))
        <x-core::button
            tag="a"
            :href="route('plugins.new')"
            color="primary"
            icon="ti ti-plus"
            class="ms-auto"
        >
            {{ trans('packages/plugin-management::plugin.plugins_add_new') }}
        </x-core::button>
    @endif
@endpush

@section('content')
    <div id="plugin-list">
        <div class="app-grid--blank-slate row row-cards">
            @foreach ($list as $plugin)
                <div class="app-card-item col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card app-item app-{{ $plugin->path }}">
                        <div
                            class="app-icon"
                            @if ($plugin->image) style="background-image: url('{{ $plugin->image }}');" @endif
                        >
                            @if (!$plugin->image)
                                <x-core::icon
                                    name="ti ti-puzzle-filled"
                                    class="text-white"
                                    size="lg"
                                />
                            @endif
                        </div>

                        <div class="app-details">
                            <h4 class="app-name">{{ $plugin->name }}</h4>
                        </div>
                        <div class="app-footer border-top">
                            <div
                                class="app-description text-muted text-truncate py-2"
                                title="{{ $plugin->description }}"
                            >
                                {{ $plugin->description }}
                            </div>
                            @if (!config('packages.plugin-management.general.hide_plugin_author', false))
                                <div class="app-author">
                                    {{ trans('packages/plugin-management::plugin.author') }}:
                                    <a
                                        href="{{ $plugin->url }}"
                                        target="_blank"
                                    >{{ $plugin->author }}</a>
                                </div>
                            @endif
                            <div class="app-version mb-3">
                                {{ trans('packages/plugin-management::plugin.version') }}: {{ $plugin->version }}
                            </div>
                            <div class="app-actions btn-list">
                                @if (auth()->user()->hasPermission('plugins.edit'))
                                    <x-core::button
                                        type="button"
                                        :color="$plugin->status ? 'warning' : 'primary'"
                                        class="btn-trigger-change-status"
                                        data-plugin="{{ $plugin->path }}"
                                        data-status="{{ $plugin->status }}"
                                    >
                                        @if ($plugin->status)
                                            {{ trans('packages/plugin-management::plugin.deactivate') }}
                                        @else
                                            {{ trans('packages/plugin-management::plugin.activate') }}
                                        @endif
                                    </x-core::button>
                                @endif

                                <button
                                    class="btn btn-success btn-trigger-update-plugin"
                                    style="display: none;"
                                    data-name="{{ $plugin->path }}"
                                    data-check-update="{{ $plugin->id ?? 'plugin-' . $plugin->path }}"
                                    data-version="{{ $plugin->version }}"
                                >{{ trans('packages/plugin-management::plugin.update') }}</button>

                                @if (auth()->user()->hasPermission('plugins.remove'))
                                    <x-core::button
                                        type="button"
                                        class="btn-trigger-remove-plugin"
                                        data-plugin="{{ $plugin->path }}"
                                    >
                                        {{ trans('packages/plugin-management::plugin.remove') }}
                                    </x-core::button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <x-core::modal.action
        id="remove-plugin-modal"
        type="danger"
        :title="trans('packages/plugin-management::plugin.remove_plugin')"
        :description="trans('packages/plugin-management::plugin.remove_plugin_confirm_message')"
        :submit-button-attrs="['id' => 'confirm-remove-plugin-button']"
        :submit-button-label="trans('packages/plugin-management::plugin.remove_plugin_confirm_yes')"
    />

    <x-core::modal
        id="confirm-install-plugin-modal"
        :title="trans('packages/plugin-management::plugin.install_plugin')"
        button-id="confirm-install-plugin-button"
        :button-label="trans('packages/plugin-management::plugin.install')"
    >
        <input
            type="hidden"
            name="plugin_name"
            value=""
        >
        <input
            type="hidden"
            name="ids"
            value=""
        >
        <p id="requirement-message"></p>
    </x-core::modal>
@endsection
