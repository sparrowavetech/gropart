<div class="form-group">
    <label for="widget-title">{{ __('Title') }}</label>
    <input
        class="form-control"
        id="widget-title"
        name="title"
        type="text"
        value="{{ $config['title'] }}"
    >
</div>

<div class="form-group">
    <label for="widget-subtitle">{{ __('Subtitle') }}</label>
    <textarea
        class="form-control"
        id="widget-subtitle"
        name="subtitle"
        rows="4"
    >{{ $config['subtitle'] }}</textarea>
</div>
