<div class="form-group">
    <label for="widget-name">{{ trans('core/base::forms.name') }}</label>
    <input
        class="form-control"
        name="name"
        type="text"
        value="{{ $config['name'] }}"
    >
</div>

<div class="form-group">
    <label for="number_display">{{ __('Number categories to display') }}</label>
    <input
        class="form-control"
        name="number_display"
        type="number"
        value="{{ $config['number_display'] }}"
    >
</div>
