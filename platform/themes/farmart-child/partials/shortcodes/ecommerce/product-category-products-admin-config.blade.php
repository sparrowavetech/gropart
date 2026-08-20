
<div class="mb-3">
    <label class="form-label">{{ __('View All Product Category Label Name') }}</label>
    <input
        class="form-control"
        name="labeltitle"
        type="text"
        value="{{ Arr::get($attributes, 'labeltitle') }}"
        placeholder="{{ __('View Product Category Label Name') }}"
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
    <label class="form-label">{{ __('Select category') }}</label>
    <select name="category_id" class="form-select">
        {!! ProductCategoryHelper::renderProductCategoriesSelect(Arr::get($attributes, 'category_id')) !!}
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Limit number of categories') }}</label>
    <input
        class="form-control"
        name="number_of_categories"
        type="number"
        value="{{ Arr::get($attributes, 'number_of_categories', 3) }}"
        placeholder="{{ __('Default: 3') }}"
    >
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Limit number of products') }}</label>
    <input
        class="form-control"
        name="limit"
        type="number"
        value="{{ Arr::get($attributes, 'limit') }}"
        placeholder="{{ __('Unlimited by default') }}"
    >
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
