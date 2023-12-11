@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        <div {!! $options['wrapperAttrs'] !!}>
    @endif
@endif

@php
    if ($showLabel && empty($options['label'])) {
        $options['label'] = trans('core/base::forms.image');
    }
@endphp

@if ($showLabel && $options['label'] !== false && $options['label_show'])
    {!! Form::customLabel($name, $options['label'], $options['label_attr']) !!}
@endif

@if ($showField)
    {!! Form::mediaImage($name, $options['value'] ?? null) !!}

    @if (isset($options['help_block']['text']))
        <x-core::form.helper-text class="mt-1">
            {!! $options['help_block']['text'] !!}
        </x-core::form.helper-text>
    @endif
@endif

@include('core/base::forms.partials.errors')

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        </div>
    @endif
@endif
