@if (is_plugin_active('ads'))
    <div class="mb-3">
        <label class="form-label" for="widget-name">{{ __('Name') }}</label>
        <input
            class="form-control"
            id="widget-name"
            name="name"
            type="text"
            value="{{ $config['name'] }}"
        >
    </div>

    <div class="mb-3">
        <label class="form-label" for="widget_ads">{{ __('Select Ads') }}</label>
        {!! Form::customSelect(
            'ads_key[]',
            AdsManager::getData(true)->pluck('name', 'key')->toArray(),
            Arr::wrap($config['ads_key'] ?? null),
            ['class' => 'form-control select-full', 'multiple' => true],
        ) !!}
    </div>

    <div class="mb-3">
        <label>{{ __('Background') }}</label>
        {!! Form::mediaImage('background', $config['background']) !!}
    </div>

    <div class="mb-3">
        <label class="form-label" for="widget_ads">{{ __('Size') }}</label>
        {!! Form::customSelect(
            'size',
            [
                'full-with' => __('Full width'),
                'large' => __('Large'),
                'medium' => __('Medium'),
            ],
            $config['size'],
            ['class' => 'form-control select-full'],
        ) !!}
    </div>
@endif
