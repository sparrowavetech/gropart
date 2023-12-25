@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        <div {!! $options['wrapperAttrs'] !!}>
    @endif
@endif

@php
    $options['attr']['label'] = $options['label'];
    $options['attr']['label_attr'] = Arr::get($options, 'label_attr');
    $isShowLabel = $showLabel && $options['label'] && $options['label_show'];

    if (! $isShowLabel) {
        unset($options['attr']['label']);
    }
@endphp

@if ($showField)
    @php
        Arr::set($options['attr'], 'class', str_replace('form-control', '', $options['attr']['class']));
        Arr::set($options['attr'], 'helperText', $options['help_block']['text']);
    @endphp
    {!! Form::onOffCheckbox($name, $options['value'], $options['attr']) !!}
@endif

@include('core/base::forms.partials.errors')

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        </div>
    @endif
@endif
