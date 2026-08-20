<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control" placeholder="{{ __('Title') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Subtitle') }}</label>
    <input type="text" name="subtitle" value="{{ Arr::get($attributes, 'subtitle') }}" class="form-control" placeholder="{{ __('Subtitle') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Highlight') }}</label>
    <input type="text" name="highlight" value="{{ Arr::get($attributes, 'highlight') }}" class="form-control" placeholder="{{ __('Highlight') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Number of displays') }}</label>
    <input type="text" name="number_of_displays" value="{{ Arr::get($attributes, 'number_of_displays') }}" class="form-control" placeholder="{{ __('Number of displays') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Link') }}</label>
    <input type="text" name="link" value="{{ Arr::get($attributes, 'link') }}" class="form-control" placeholder="{{ __('Link') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Text link') }}</label>
    <input type="text" name="text_link" value="{{ Arr::get($attributes, 'text_link') }}" class="form-control" placeholder="{{ __('Text link') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('Background color') }}</label>
    {!! Form::customColor('bg_color', Arr::get($attributes, 'bg_color')) !!}
</div>

<div class="form-group">
    <label class="control-label">{{ __('Style') }}</label>
    {!! Form::customSelect('style', [
            'style-1' => __('Style 1'),
            'style-2' => __('Style 2'),
            'style-3' => __('Style 3'),
            'style-4' => __('Style 4'),
        ], Arr::get($attributes, 'style')) !!}
</div>
