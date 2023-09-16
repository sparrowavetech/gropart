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
        value="{{ Arr::get($attributes, 'limit', 8) }}"
        placeholder="{{ __('Limit') }}"
    >
</div>

<div class="form-group">
    <label class="control-label">{{ __('Select a product collection') }}</label>
    <div class="ui-select-wrapper form-group">
        <select
            class="ui-select"
            name="collection_id"
        >
            <option value="">{{ __('All') }}</option>
            @foreach ($productCollections as $collection)
                <option
                    value="{{ $collection->id }}"
                    @if ($collection->id == Arr::get($attributes, 'collection_id')) selected @endif
                >{!! BaseHelper::clean($collection->indent_text) !!} {{ $collection->name }}</option>
            @endforeach
        </select>
        <svg class="svg-next-icon svg-next-icon-size-16">
            <use
                xmlns:xlink="http://www.w3.org/1999/xlink"
                xlink:href="#select-chevron"
            ></use>
        </svg>
    </div>
</div>

{!! Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes')) !!}
