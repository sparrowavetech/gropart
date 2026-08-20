<div class="mb-3">
    <label class="form-label">{{ __('Title') }}</label>
    <input
        class="form-control"
        name="title"
        type="text"
        value="{{ Arr::get($attributes, 'title') }}"
        placeholder="{{ __('Title') }}"
    >
</div>

<div class="mb-3">
    <label class="form-label">{{ __('View All Product Category Label Name') }}</label>
    <input
        class="form-control"
        name="labeltitle"
        type="text"
        value="{{ Arr::get($attributes, 'labeltitle') }}"
        placeholder="{{ __('All Product Category Label Name') }}"
    >
</div>

<div class="mb-3">
    <label class="form-label">{{ __('View All Product Category URL') }}</label>
    <input
        class="form-control"
        name="labelurl"
        type="text"
        value="{{ Arr::get($attributes, 'labelurl') }}"
        placeholder="{{ __('View All Product Category URL') }}"
    >
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Limit') }}</label>
    <input
        class="form-control"
        name="limit"
        type="number"
        value="{{ Arr::get($attributes, 'limit') }}"
        placeholder="{{ __('Limit') }}"
    >
</div>


{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
