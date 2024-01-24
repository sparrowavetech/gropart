@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        <div {!! $options['wrapperAttrs'] !!}>
    @endif
@endif


@if ($showLabel && $options['label'] !== false && $options['label_show'])
    <x-core::form.label
        :for="$name"
        :label="$options['label']"
        :attributes="new Illuminate\View\ComponentAttributeBag($options['label_attr'])"
    />
@endif

@if ($showField)
    @if ($prepend = Arr::get($options, 'prepend'))
        {!! $prepend !!}
    @endif

    <input type="password" name="{{ $name }}" value="{{ $options['value'] }}" {!! Html::attributes($options['attr']) !!}>

    @if ($append = Arr::get($options, 'append'))
        {!! $append !!}
    @endif

    @include('core/base::forms.partials.help-block')
@endif

@include('core/base::forms.partials.errors')

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        </div>
    @endif
@endif
