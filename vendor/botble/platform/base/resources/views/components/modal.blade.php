@props([
    'id' => null,
    'title' => null,
    'blur' => true,
    'closeButton' => true,
    'size' => null,
    'bodyClass' => null,
    'bodyAttrs' => [],
    'formAction' => null,
    'formMethod' => 'POST',
    'formAttrs' => [],
    'hasForm' => false,
    'type' => null,
    'buttonId' => null,
    'buttonClass' => null,
    'buttonLabel' => null,
    'centered' => true,
    'scrollable' => true,
])

@php
    $classes = Arr::toCssClasses(['modal', 'fade', 'modal-blur' => $blur]);

    $hasForm = $hasForm || $formAction;

    $modalContentAttributes = ['class' => rtrim('modal-content ' . Arr::get($formAttrs, 'class'))];
@endphp

<div
    {{ $attributes->merge(['id' => $id, 'class' => $classes])->class($classes) }}
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    data-select2-dropdown-parent="true"
>
    <div
        @class([
            'modal-dialog',
            'modal-dialog-centered' => $centered,
            'modal-dialog-scrollable' => $scrollable,
            $size ? "modal-$size" : null,
        ])
        role="document"
    >
        <{{ $hasForm ? 'form' : 'div' }}
            {!! Html::attributes($modalContentAttributes) !!}
        @if ($hasForm)
            action="{{ $formAction }}"
            method="{{ $formMethod }}"
            {!! Html::attributes($formAttrs) !!}
        @endif
        >
        @if ($hasForm)
            @csrf
        @endif

        @if ($title || $closeButton)
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                @if ($closeButton)
                    <x-core::modal.close-button />
                @endif
            </div>
        @endif

        @if($type)
            <div class="modal-status bg-{{ $type }}"></div>
        @endif

        @if ($slot->isNotEmpty())
            <div {!! Html::attributes(array_merge($bodyAttrs, ['class' => rtrim('modal-body ' . Arr::get($bodyAttrs, 'class'))])) !!}>
                {{ $slot }}
            </div>
        @else
            {{ $slot }}
        @endif

        @if (!empty($footer) && $footer->isNotEmpty())
            <div class="modal-footer">
                {{ $footer }}
            </div>
        @endif

        @if($buttonId && $buttonLabel)
            <div class="modal-footer">
                <x-core::button type="button" data-bs-dismiss="modal">
                    {{ trans('core/base::tables.cancel') }}
                </x-core::button>
                <x-core::button
                    :id="$buttonId"
                    @class(['ms-auto', $buttonClass => $buttonClass])
                    :color="$type ?? 'primary'"
                >
                    {{ $buttonLabel }}
                </x-core::button>
            </div>
        @endif
    </{{ $hasForm ? 'form' : 'div' }}>
</div>
</div>
