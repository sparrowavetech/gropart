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
    <label class="form-label">{{ __('Select a product collection') }}</label>
    <select class="form-select" name="collection_id">
        <option value="">{{ __('All') }}</option>
        @foreach ($productCollectionsFull as $collection)
            <option value="{{ $collection->id }}"
                @if ($collection->id == Arr::get($attributes, 'collection_id')) selected @endif
            >{!! BaseHelper::clean($collection->indent_text) !!} {{ $collection->name }}</option>
        @endforeach
    </select>
</div>
