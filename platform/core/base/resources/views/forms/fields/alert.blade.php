@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        <div {!! $options['wrapperAttrs'] !!}>
   @endif
@endif

@if ($showField)
    <x-core::alert
        :attributes="new Illuminate\View\ComponentAttributeBag((array) $options['attr'])"
        :type="$options['type']">
        {!! $options['html'] !!}
    </x-core::alert>
@endif

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        </div>
    @endif
@endif
