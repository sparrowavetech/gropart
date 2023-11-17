<div class="form-group">
    <label class="control-label">{{ __('Select category') }}</label>

    <div class="ui-select-wrapper form-group">
        <select name="category_id" class="ui-select">
            {!! ProductCategoryHelper::renderProductCategoriesSelect(Arr::get($attributes, 'category_id')) !!}
        </select>
        <svg class="svg-next-icon svg-next-icon-size-16">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
            >
                <path d="M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z"></path>
            </svg>
        </svg>
    </div>
</div>

<div class="form-group">
    <label class="control-label">{{ __('Limit number of categories') }}</label>
    <input
        class="form-control"
        name="number_of_categories"
        type="number"
        value="{{ Arr::get($attributes, 'number_of_categories', 3) }}"
        placeholder="{{ __('Default: 3') }}"
    >
</div>

<div class="form-group">
    <label class="control-label">{{ __('Limit number of products') }}</label>
    <input
        class="form-control"
        name="limit"
        type="number"
        value="{{ Arr::get($attributes, 'limit') }}"
        placeholder="{{ __('Unlimited by default') }}"
    >
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
