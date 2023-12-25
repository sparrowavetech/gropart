@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        {!! Form::open(['route' => ['ip-blocker.settings.update']]) !!}
        <x-core-setting::section
                :title="trans('plugins/ip-blocker::ip-blocker.ip_blocker_title')"
                :description="trans('plugins/ip-blocker::ip-blocker.ip_blocker_description')"
        >

            <label>{{ __('plugins/ip-blocker::ip-blocker.please_enter_ip_address') }}</label>

            {!! Form::text('ip_addresses', $ips, ['class' => 'tags mb-3', 'placeholder' => trans('plugins/ip-blocker::ip-blocker.please_enter_ip_address')]) !!}

            <label>{{ __('plugins/ip-blocker::ip-blocker.please_enter_wildcard_ip_address') }}</label>

            {!! Form::text('wildcard_ip_address', $wildcardIPAddress, ['class' => 'tags mb-3', 'placeholder' => trans('plugins/ip-blocker::ip-blocker.please_enter_wildcard_ip_address')]) !!}

            <button class="btn btn-info" type="submit">
                {{ trans('plugins/ip-blocker::ip-blocker.save_settings') }}
            </button>
        </x-core-setting::section>
        {!! Form::close() !!}

        <x-core-setting::section>
            {!! Form::open(['route' => ['ip-blocker.settings.check-secret-key']]) !!}
            <x-core-setting::text-input
                    name="secret_key"
                    :label="trans('plugins/ip-blocker::ip-blocker.api_secret_key_label')"
                    type="text"
                    :value="$secretKey"
                    :placeholder="trans('plugins/ip-blocker::ip-blocker.api_secret_key_placeholder')"
                    :helperText="trans('plugins/ip-blocker::ip-blocker.api_secret_key_helper', ['link' => Html::link('https://ipinfo.io/pricing', attributes: ['target' => '_blank'])])"
            />

            <button class="btn btn-info" type="submit">
                {{ trans('plugins/ip-blocker::ip-blocker.activate') }}
            </button>
            {!! Form::close() !!}

            @if($secretKey)
                {!! Form::open(['route' => ['ip-blocker.settings.available-countries']]) !!}
                <x-core-setting::form-group class="mt-3">
                    <label class="text-title-field" for="available_countries">{{ trans('plugins/ip-blocker::ip-blocker.available_countries') }}</label>
                    <label>
                        <input type="checkbox" class="check-all" data-set=".available-countries" @if(empty($countriesCode) || empty(array_diff(array_keys(\Botble\Base\Supports\Helper::countries()), $countriesCode))) checked @endif>
                        {{ trans('plugins/ip-blocker::ip-blocker.all_countries') }}
                    </label>
                    <div class="form-group form-group-no-margin">
                        <div class="multi-choices-widget list-item-checkbox">
                            <ul>
                                @foreach (\Botble\Base\Supports\Helper::countries() as $key => $item)
                                    <li>
                                        <input
                                                type="checkbox"
                                                class="styled available-countries"
                                                name="available_countries[]"
                                                value="{{ $key }}"
                                                id="available-countries-item-{{ $key }}"
                                                @checked(empty($countriesCode) || in_array($key, $countriesCode))
                                        >
                                        <label for="available-countries-item-{{ $key }}">{{ $item }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </x-core-setting::form-group>

                <button class="btn btn-info" type="submit">
                    {{ trans('plugins/ip-blocker::ip-blocker.save_settings') }}
                </button>
                {!! Form::close() !!}
            @endif
        </x-core-setting::section>

        <x-core-setting::section
                :title="trans('plugins/ip-blocker::ip-blocker.history')"
                :description="trans('plugins/ip-blocker::ip-blocker.history_description')"
        >
            <div class="table-wrapper" style="padding-top: 25px">
                {!! $historyTable->renderTable() !!}
            </div>
        </x-core-setting::section>
    </div>
@endsection
