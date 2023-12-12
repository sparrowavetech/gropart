@php
    $values = Arr::wrap($values ?? []);
    Arr::set($attributes, 'class', str_replace('form-control', '', $attributes['class']));
@endphp

<x-core::form.radio-list
    :name="$name"
    :value="$selected"
    :options="$values"
    :attributes="new Illuminate\View\ComponentAttributeBag((array) $attributes)"
/>
