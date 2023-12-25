@php
    Assets::addScriptsDirectly('vendor/core/core/base/js/repeater-field.js');

    $values = array_values(is_array($value) ? $value : (array) json_decode($value ?: '[]', true));

    $added = [];

    if (! empty($values)) {
        for ($i = 0; $i < count($values); $i++) {
            $group = '';
            foreach ($fields as $key => $field) {
                $item = Form::hidden($name . '[' . $i . '][' . $key . '][key]', $field['attributes']['name']);
                $field['attributes']['name'] = $name . '[' . $i . '][' . $key . '][value]';
                $field['attributes']['value'] = Arr::get($values, $i . '.' . $key . '.value');
                $field['attributes']['options']['id'] = $id = 'repeater_field_' . md5($field['attributes']['name']);
                Arr::set($field, 'attributes.id', $id);
                Arr::set($field, 'label_attr.for', $id);
                $item .= Blade::render(sprintf('<x-core::form.label %s>%s</x-core::form.label>', Html::attributes(Arr::get($field, 'label_attr', [])), $field['label']));
                $item .= call_user_func_array([Form::class, $field['type']], array_values($field['attributes']));

                $group .= Blade::render(sprintf("<x-core::form-group>%s</x-core::form-group>", $item));
            }

            $added[] = Blade::render(sprintf('<div class="repeater-item-group">%s</div>', $group));
        }
    }

    $group = '';

    foreach ($fields as $key => $field) {
        $item = Form::hidden($name . '[__key__][' . $key . '][key]', $field['attributes']['name']);
        $field['attributes']['name'] = $name . '[__key__][' . $key . '][value]';
        $field['attributes']['options']['id'] = 'repeater_field_' . md5($field['attributes']['name']) . '__key__';
        Arr::set($field, 'label_attr.for', $field['attributes']['options']['id']);
        $item .= Blade::render(sprintf('<x-core::form.label %s>%s</x-core::form.label>', Html::attributes(Arr::get($field, 'label_attr', [])), $field['label']));
        $item .= call_user_func_array([Form::class, $field['type']], array_values($field['attributes']));

        $group .= Blade::render(sprintf('<x-core::form-group>%s</x-core::form-group>', $item));
    }

    $defaultFields = [Blade::render(sprintf('<div class="repeater-item-group">%s</div>', $group))];

    $repeaterId = 'repeater_field_' . md5($name) . '_' . uniqid();
@endphp

<input
    name="{{ $name }}"
    type="hidden"
    value="[]"
>

<div
    class="repeater-group"
    id="{{ $repeaterId }}_group"
    data-next-index="{{ count($added) }}"
>
    @foreach ($added as $field)
        <fieldset
            class="form-fieldset position-relative"
            data-id="{{ $repeaterId }}_{{ $loop->index }}"
            data-index="{{ $loop->index }}"
        >
            <legend class="d-flex justify-content-end align-items-center">
                <x-core::button
                    class="position-absolute remove-item-button"
                    data-target="repeater-remove"
                    data-id="{{ $repeaterId }}_{{ $loop->index }}"
                    icon="ti ti-x"
                    :icon-only="true"
                    size="sm"
                />
            </legend>

            <div>{!! $field !!}</div>
        </fieldset>
    @endforeach
</div>

<div class="mb-3">
    <x-core::button
        data-target="repeater-add"
        data-id="{{ $repeaterId }}"
        type="button"
    >
        {{ __('Add new') }}
    </x-core::button>
</div>

<x-core::custom-template id="{{ $repeaterId }}_template">
    @foreach($defaultFields as $defaultFieldIndex => $defaultField)
        <fieldset data-id="{{ $repeaterId }}___key__" data-index="__key__" class="form-fieldset position-relative">
            <div data-target="fields">__fields__</div>

            <x-core::button
                class="position-absolute remove-item-button"
                data-target="repeater-remove"
                data-id="{{ $repeaterId }}___key__"
                icon="ti ti-x" :icon-only="true"
                size="sm"
            />
        </fieldset>
    @endforeach
</x-core::custom-template>

<x-core::custom-template id="{{ $repeaterId }}_fields">
    {{ $defaultField }}
</x-core::custom-template>
