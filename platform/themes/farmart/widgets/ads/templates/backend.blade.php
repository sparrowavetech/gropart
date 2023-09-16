@if (is_plugin_active('ads'))
    <div class="form-group">
        <label for="widget-name">{{ __('Name') }}</label>
        <input
            class="form-control"
            id="widget-name"
            name="name"
            type="text"
            value="{{ $config['name'] }}"
        >
    </div>

    <div class="form-group">
        <label for="widget_ads">{{ __('Select Ads') }}</label>
        {!! Form::customSelect(
            'ads_key',
            ['' => __('-- Select --')] +
                AdsManager::getData(true)->pluck('name', 'key')->toArray(),
            $config['ads_key'],
            ['class' => 'form-control select-full'],
        ) !!}
    </div>

    <div class="form-group">
        <label>{{ __('Background') }}</label>
        {!! Form::mediaImage('background', $config['background']) !!}
    </div>

    <div class="form-group">
        <label for="widget_ads">{{ __('Size') }}</label>
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
