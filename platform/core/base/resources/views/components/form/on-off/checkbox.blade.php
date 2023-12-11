@php
    $value = 1;
@endphp

<x-core::form-group class="{{ $wrapperClass ?? null }}">
    <input type="hidden" name="{{ $name }}" value="0">

    @include('core/base::components.form.checkbox')
</x-core::form-group>
