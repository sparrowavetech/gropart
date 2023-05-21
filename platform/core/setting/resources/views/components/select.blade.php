@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
])

<x-core-setting::form-group>
    @if($label)
        <label for="{{ $name }}" class="text-title-field">{{ $label }}</label>
    @endif
    {!! Form::customSelect($name, $options, $value, $attributes->getAttributes()) !!}
</x-core-setting::form-group>
