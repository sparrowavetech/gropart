@php
    Assets::addScripts('coloris')->addStyles('coloris');
@endphp

<x-core::form.color-picker
    :name="$name"
    :value="$value ?? '#000'"
    :attributes="new Illuminate\View\ComponentAttributeBag((array) $attributes)"
/>
