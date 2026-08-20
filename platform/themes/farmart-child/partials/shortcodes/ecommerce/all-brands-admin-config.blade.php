<div class="form-group">
    <label class="control-label">{{ __('Title') }}</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control"
           placeholder="{{ __('Title') }}">
</div>
<div class="mb-3">
    <label class="form-label">{{ __('View All Brand Name') }}</label>
    <input
        class="form-control"
        name="labeltitle"
        type="text"
        value="{{ Arr::get($attributes, 'labeltitle') }}"
        placeholder="{{ __('View All Brand Name') }}"
    >
</div>
