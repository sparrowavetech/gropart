<div class="mb-3">
    <label class="form-label">{{ __('Background Image') }}</label>
    {!! Form::mediaImage('background', Arr::get($attributes, 'background')) !!}
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Select Layout') }}</label>
    <select required class="form-select" name="selectlayout">
        <option value="">{{ __('-- select --') }}</option>
        <option value="full-width"
            @if (Arr::get($attributes, 'selectlayout') == 'full-width') selected @endif
        >{{ __('Full Width') }}</option>
        <option value="boxed-width"
            @if (Arr::get($attributes, 'selectlayout') == 'boxed-width') selected @endif
        >{{ __('Boxed Layout') }}</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Slider Desktop Height') }}</label>
    <input required type="text" class="form-control" name="sliderdheight" value="{{ Arr::get($attributes, 'sliderdheight') }}" placeholder="{{ __('Enter Slider Desktop View Height') }}" />
    <input type="hidden" name="sliderid" value="{{ Arr::get($attributes, 'sliderid', Str::random(10)) }}" placeholder="{{ __('Enter Slider Desktop View Height') }}" />
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Slider Tablet Height') }}</label>
    <input required type="text" class="form-control" name="slidertheight" value="{{ Arr::get($attributes, 'slidertheight') }}" placeholder="{{ __('Enter Slider Tablet View Height') }}" />
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Slider Mobile Height') }}</label>
    <input required type="text" class="form-control" name="slidermheight" value="{{ Arr::get($attributes, 'slidermheight') }}" placeholder="{{ __('Enter Slider Mobile View Height') }}" />
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Ads') }}</label>
    <select
        class="form-select"
        name="ads"
    >
        <option value="">{{ __('-- select --') }}</option>
        @foreach ($ads as $ad)
            <option
                value="{{ $ad->key }}"
                @if ($ad->key == Arr::get($attributes, 'ads')) selected @endif
            >{{ $ad->name }}</option>
        @endforeach
    </select>
</div>
