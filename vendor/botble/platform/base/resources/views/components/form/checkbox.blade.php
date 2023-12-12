@props([
    'id' => null,
    'label' => null,
    'name' => null,
    'value' => null,
    'checked' => false,
    'helperText' => null,
    'inline' => false,
    'single' => false,
])

@php
    $labelClasses = Arr::toCssClasses([
        'form-check',
        'form-check-inline' => $inline,
        'form-check-single' => $single,
    ]);
@endphp

<label class="{{ $labelClasses }}">
    <input
        {{ $attributes->merge(['type' => 'checkbox', 'id' => $id, 'name' => $name, 'class' => 'form-check-input', 'value' => $value]) }}
        @checked(old($name, $checked))
    >

    @if($label || $slot->isNotEmpty())
        <span class="form-check-label">
            {{ $label ?: $slot }}
        </span>
    @endif

    @if ($helperText)
        <span class="form-check-description">{!! BaseHelper::clean($helperText) !!}</span>
    @endif
</label>
