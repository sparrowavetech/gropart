@props(['name', 'value', 'key', 'checked' => false, 'single' => false])

@php
    $labelClasses = Arr::toCssClasses([
        'form-check form-check-inline mb-3',
        'form-check-single' => $single,
    ]);
@endphp

<label class="{{ $labelClasses }}">
    <input
        {{ $attributes->merge(['class' => 'form-check-input']) }}
        type="radio"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked(old($name) === $value || $checked)
    >

    <span class="form-check-label">{{ $slot }}</span>
</label>
