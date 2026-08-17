@php
    /** @var Botble\Table\Abstracts\TableAbstract $table */
@endphp

<div class="wrapper-filter sms-delivery-report-filter">
    <p>{{ trans('core/table::table.filters') }}</p>

    <input
        type="hidden"
        class="filter-data-url"
        value="{{ isset($table) ? $table->getFilterInputUrl() : route('table.filter.input') }}"
    />

    <div class="sample-filter-item-wrap hidden">
        <div class="row filter-item form-filter g-3 align-items-start">
            <div class="col-12 col-lg-4">
                <x-core::form.select
                    name="filter_columns[]"
                    :options="array_combine(array_keys($columns), array_column($columns, 'title'))"
                    class="filter-column-key"
                />
            </div>

            <div class="col-12 col-lg-4">
                <x-core::form.select
                    name="filter_operators[]"
                    :options="[
                        'like' => trans('core/table::table.contains'),
                        '=' => trans('core/table::table.is_equal_to'),
                        '>' => trans('core/table::table.greater_than'),
                        '<' => trans('core/table::table.less_than'),
                    ]"
                    class="filter-operator filter-column-operator"
                />
            </div>

            <div class="col-12 col-lg-4 d-flex gap-2">
                <span class="filter-column-value-wrap flex-grow-1">
                    <input
                        class="form-control filter-column-value"
                        type="text"
                        placeholder="{{ trans('core/table::table.value') }}"
                        name="filter_values[]"
                    >
                </span>

                <x-core::button
                    type="button"
                    class="btn-remove-filter-item text-danger"
                    :tooltip="trans('core/table::table.delete')"
                    icon="ti ti-trash"
                    :icon-only="true"
                />
            </div>
        </div>
    </div>

    <x-core::form
        class="filter-form"
        method="get"
    >
        <input
            type="hidden"
            name="filter_table_id"
            class="filter-data-table-id"
            value="{{ $tableId }}"
        >
        <input
            type="hidden"
            name="class"
            class="filter-data-class"
            value="{{ $class }}"
        >

        <div class="filter_list filter-items-wrap">
            @foreach ($requestFilters as $filterItem)
                <div @class([
                    'row filter-item form-filter g-3 align-items-start',
                    'filter-item-default' => $loop->first,
                ])>
                    <div class="col-12 col-lg-4">
                        <x-core::form.select
                            name="filter_columns[]"
                            :options="['' => trans('core/table::table.select_field')] +
                                array_combine(array_keys($columns), array_column($columns, 'title'))"
                            :value="$filterItem['column']"
                            class="filter-column-key"
                        />
                    </div>

                    <div class="col-12 col-lg-4">
                        <x-core::form.select
                            name="filter_operators[]"
                            :options="[
                                'like' => trans('core/table::table.contains'),
                                '=' => trans('core/table::table.is_equal_to'),
                                '>' => trans('core/table::table.greater_than'),
                                '<' => trans('core/table::table.less_than'),
                            ]"
                            :value="$filterItem['operator']"
                            class="filter-operator filter-column-operator"
                        />
                    </div>

                    <div class="col-12 col-lg-4 d-flex gap-2">
                        <div class="filter-column-value-wrap flex-grow-1 mb-3">
                            <input
                                class="form-control filter-column-value"
                                type="text"
                                placeholder="{{ trans('core/table::table.value') }}"
                                name="filter_values[]"
                                value="{{ $filterItem['value'] }}"
                            >
                        </div>

                        @if (! $loop->first)
                            <x-core::button
                                type="button"
                                class="btn-remove-filter-item text-danger"
                                :tooltip="trans('core/table::table.delete')"
                                icon="ti ti-trash"
                                :icon-only="true"
                            />
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="btn-list">
            <x-core::button
                type="button"
                class="add-more-filter"
            >
                {{ trans('core/table::table.add_additional_filter') }}
            </x-core::button>
            <x-core::button
                type="submit"
                color="primary"
                class="btn-apply"
            >
                {{ trans('core/table::table.apply') }}
            </x-core::button>
            <x-core::button
                tag="a"
                href="{{ URL::current() }}"
                data-bb-toggle="datatable-reset-filter"
                @style(['display: none' => ! request()->has('filter_table_id')])
                icon="ti ti-refresh"
                class="w-6"
                :icon-only="true"
            />
        </div>
    </x-core::form>
</div>
