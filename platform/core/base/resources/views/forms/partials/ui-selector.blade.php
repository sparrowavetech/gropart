@if (count($choices) > 0)
    @php
        $attributes['name'] = Arr::get($attributes, 'name', $name);
        $attributes['class'] = Arr::get($attributes, 'class', '') . ' form-imagecheck-input';
        $attributes = Arr::except($attributes, ['id', 'type', 'value']);
        $htmlAttributes = null;

        foreach ($attributes as $key => $attribute) {
            $htmlAttributes .= ($htmlAttributes ? ' '  : '') . $key . '=' . sprintf('"%s"', $attribute);
        }
    @endphp

    <div class="row">
        @foreach($choices as $key => $option)
            <div class="col-xl-4 col-sm-6 ui-selector">
                <label for="input-image-check-{{ $key }}" class="form-imagecheck mb-3 form-imagecheck-tick">
                    <input type="radio" id="input-image-check-{{ $key }}" {!! $htmlAttributes !!} value="{{ $key }}" @checked($key == $value)>
                    @if ($image = Arr::get($option, 'image', asset('vendor/core/core/base/images/ui-selector-placeholder.jpg')))
                        <span class="form-imagecheck-figure mb-1">
                        <img src="{{ $image }}" class="form-imagecheck-image" alt="{{ Arr::get($option, 'label') }}">
                    </span>
                    @endif
                    @if ($label = Arr::get($option, 'label'))
                        <label for="input-image-check-{{ $key }}" class="cursor-pointer title text-center form-check-label">
                            {!! BaseHelper::clean($label) !!}
                        </label>
                    @endif
                </label>
            </div>
        @endforeach
    </div>
@endif
