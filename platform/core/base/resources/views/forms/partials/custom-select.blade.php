<div class="mb-3 {{ Arr::get($selectAttributes, 'wrapper_class') ?: '' }}">
    @php
        Arr::set($selectAttributes, 'class', Arr::get($selectAttributes, 'class') . ' form-select');
        $choices = $list ?? $choices;
        $selectAttributes = [...$selectAttributes, 'data-placeholder' => trans('core/base::forms.select_placeholder')];
    @endphp
    {!! Form::select(
        $name,
        $choices,
        $selected,
        $selectAttributes,
        $optionsAttributes,
        $optgroupsAttributes,
    ) !!}
</div>
