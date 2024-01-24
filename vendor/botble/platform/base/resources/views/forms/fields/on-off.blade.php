@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        <div {!! $options['wrapperAttrs'] !!}>
    @endif
@endif

@php
    $options['attr']['label'] = $options['label'];
    $isShowLabel = $showLabel && $options['label'] && $options['label_show'];

    if (!$isShowLabel) {
        unset($options['attr']['label']);
    }
@endphp

@if ($showField)
    @php
        Arr::set($options['attr'], 'class', str_replace('form-control', '', $options['attr']['class']));
    @endphp
    {!! Form::onOff($name, $options['value'], $options['attr']) !!}

    @include('core/base::forms.partials.help-block')
@endif

@include('core/base::forms.partials.errors')

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        </div>
    @endif
@endif
