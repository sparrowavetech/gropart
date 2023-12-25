@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        <div {!! $options['wrapperAttrs'] !!}>
    @endif
@endif

@if ($showLabel && $options['label'] !== false && $options['label_show'])
    {!! Form::customLabel($name, $options['label'], $options['label_attr']) !!}
@endif

@if ($showField)
    {!! Form::multiChecklist(
        $name,
        $options['value'] ?: Arr::get($options, 'selected', []),
        $options['choices'],
        $options['attr'],
        Arr::get($options, 'empty_value'),
        Arr::get($options, 'inline', false),
        Arr::get($options, 'as_dropdown', false),
        Arr::get($options, 'attr.data-url'),
    ) !!}

    @include('core/base::forms.partials.help-block')
@endif

@include('core/base::forms.partials.errors')

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        </div>
    @endif
@endif
