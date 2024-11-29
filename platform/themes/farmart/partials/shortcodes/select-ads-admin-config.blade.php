<div class="mb-3">
    <label class="form-label">{{ __('Background Image') }}</label>
    {!! Form::mediaImage('background', Arr::get($attributes, 'background')) !!}
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Select Layout') }}</label>
    <select class="form-select" name="selectlayout">
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
