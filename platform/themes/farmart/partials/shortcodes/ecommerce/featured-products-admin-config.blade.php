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
    <label class="form-label">{{ __('View All Product Name') }}</label>
    <input
        class="form-control"
        name="limit"
        type="number"
        value="{{ Arr::get($attributes, 'limit') }}"
        placeholder="{{ __('View All Product Name') }}"
    >
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
