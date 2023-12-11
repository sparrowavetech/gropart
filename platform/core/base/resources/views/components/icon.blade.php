@props(['name', 'size' => null])

@php
    $class = Arr::toCssClasses(['icon', $name, "icon-$size" => $size]);
@endphp

@if(str_contains($name, 'ti ti-'))
    <span {{ $attributes->class(['icon-tabler-wrapper', "icon-$size" => $size]) }}>
        @include('core/base::components.icons.' . str_replace('ti ti-', '', $name))
    </span>
@else
    <i {{ $attributes->class($class) }}></i>
@endif
