@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        <div {!! $options['wrapperAttrs'] !!}>
    @endif
@endif

@if ($showLabel && $options['label'] !== false && $options['label_show'])
    {!! Form::customLabel($name, $options['label'], $options['label_attr']) !!}
@endif

@if ($showField)
    <div>
        @php
            $choices = Arr::get($options, 'choices', []);
        @endphp

        @if (count($choices) < 20)
            @foreach ($choices as $key => $item)
                <x-core::form.checkbox
                    :id="sprintf('%s-item-%s', $name, $key)"
                    :name="$name"
                    :value="$key"
                    :label="$item"
                    :checked="in_array($key, Arr::get($options, 'value', []) ?: Arr::get($options, 'selected', []))"
                    :inline="Arr::get($options, 'inline', false)"
                />
            @endforeach
        @else
            <div
                class="position-relative"
                data-bb-toggle="dropdown-checkboxes"
                data-selected-text="{{ trans('core/base::forms.selected') }}"
                data-placeholder="{{ $placeholder = Arr::get($options, 'attr.placeholder') ?: trans('core/base::forms.select_placeholder') }}"
            >
                <span class="form-select text-truncate">{{ $placeholder }}</span>

                <input type="text" class="form-select" placeholder="{{ trans('core/table::table.search') }}" style="display: none">

                <div class="dropdown-menu dropdown-menu-end w-100">
                    <div data-bb-toggle="tree-checkboxes">
                        <ul class="list-unstyled p-3 pb-0">
                            @foreach ($choices as $key => $item)
                                <x-core::form.checkbox
                                    :id="sprintf('%s-item-%s', $name, $key)"
                                    :name="$name"
                                    :value="$key"
                                    :label="$item"
                                    :checked="in_array($key, Arr::get($options, 'value', []) ?: Arr::get($options, 'selected', []))"
                                    :inline="Arr::get($options, 'inline', false)"
                                />
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @include('core/base::forms.partials.help-block')
    </div>
@endif

@include('core/base::forms.partials.errors')

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        </div>
    @endif
@endif
