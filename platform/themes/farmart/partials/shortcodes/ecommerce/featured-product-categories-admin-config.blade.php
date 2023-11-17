<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input
        class="form-control"
        name="title"
        type="text"
        value="{{ Arr::get($attributes, 'title') }}"
        placeholder="{{ __('Title') }}"
    >
</div>

<div class="form-group">
    <label class="control-label">{{ __('Limit') }}</label>
    <input
        class="form-control"
        name="limit"
        type="number"
        value="{{ Arr::get($attributes, 'limit') }}"
        placeholder="{{ __('Limit') }}"
    >
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
