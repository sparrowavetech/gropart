
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
    <label class="form-label">{{ __('Select category') }}</label>
    <select name="category_id" class="form-select">
        {!! ProductCategoryHelper::renderProductCategoriesSelect(Arr::get($attributes, 'category_id')) !!}
    </select>
</div>
