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
    <label class="form-label">{{ __('View All Brands Label Name') }}</label>
    <input
        class="form-control"
        name="labeltitle"
        type="text"
        value="{{ Arr::get($attributes, 'labeltitle') }}"
        placeholder="{{ __('View All Brands') }}"
    >
</div>

<div class="mb-3">
    <label class="form-label">{{ __('View All Brands URL') }}</label>
    <input
        class="form-control"
        name="labelurl"
        type="text"
        value="{{ Arr::get($attributes, 'labelurl') }}"
        placeholder="{{ __('View All Brands URL') }}"
    >
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
