@php
    $values = Arr::wrap($values ?? []);

    $attributes = (array) $attributes;

    $multiple = count($values) > 1;
@endphp

<div class="position-relative form-check-group">
    @foreach ($values as $key => $option)
        @php
            $optionAttributes = $attributes;

            // Suffix a copy, not $attributes itself - mutating it would compound the id across
            // iterations (field_a, then field_a_b, then field_a_b_c).
            if ($multiple && isset($optionAttributes['id'])) {
                $optionAttributes['id'] = $optionAttributes['id'] . '_' . $key;
            }
        @endphp

        <x-core::form.radio
            :name="$name"
            :value="$key"
            {{-- Compare as strings: a loose == would check the option whose value is '' or 0
                 whenever nothing is selected, because PHP treats those as equal to null. --}}
            :checked="filled($selected) && (string) $key === (string) $selected"
            :attributes="new Illuminate\View\ComponentAttributeBag($optionAttributes)"
        >
            {{ $option }}
        </x-core::form.radio>
    @endforeach
</div>
