@for ($i = 1; $i < 5; $i++)
    <div class="mb-3">
        <label class="form-label">{{ __('Ad :number', ['number' => $i]) }}</label>
        <select
            class="form-control"
            name="key_{{ $i }}"
        >
            <option value="">{{ __('-- select --') }}</option>
            @foreach ($ads as $ad)
                <option
                    value="{{ $ad->key }}"
                    @if ($ad->key == Arr::get($attributes, 'key_' . $i)) selected @endif
                >{{ $ad->name }}</option>
            @endforeach
        </select>
    </div>
@endfor
