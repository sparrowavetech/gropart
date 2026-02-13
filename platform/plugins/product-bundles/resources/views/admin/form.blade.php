@extends('core/base::layouts.master')

@section('content')
@php
    $isEdit = (bool) ($bundle->id ?? null);

    $attachedProducts = $isEdit ? ($bundle->products ?? collect()) : collect();
    $fixedItems = $isEdit ? ($bundle->items ?? collect()) : collect();
    $mixGroups = $isEdit ? ($bundle->groups ?? collect()) : collect();

    // Selected attached products (supports both array and comma-separated string from old inputs).
    $attachedSelected = old('attached_product_ids');
    if (is_string($attachedSelected)) {
        $attachedSelected = collect(explode(',', $attachedSelected))
            ->map(fn($v) => (int) trim($v))
            ->filter(fn($v) => $v > 0)
            ->values()
            ->all();
    }
    if (! is_array($attachedSelected)) {
        $attachedSelected = $attachedProducts->pluck('id')->map(fn($v) => (int) $v)->values()->all();
    }

    // Build a map of product_id => "#id - name" for preselected options.
    $productTextMap = [];
    foreach ($attachedProducts as $p) {
        $productTextMap[(int) $p->id] = '#' . $p->id . ' - ' . $p->name;
    }
    foreach ($fixedItems as $it) {
        if ($it->product) {
            $productTextMap[(int) $it->product_id] = '#' . $it->product_id . ' - ' . $it->product->name;
        }
    }
    foreach ($mixGroups as $g) {
        foreach ($g->items ?? [] as $gi) {
            if ($gi->product) {
                $productTextMap[(int) $gi->product_id] = '#' . $gi->product_id . ' - ' . $gi->product->name;
            }
        }
    }
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-9">
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        {{ $isEdit ? trans('plugins/product-bundles::bundles.form.edit_title') : trans('plugins/product-bundles::bundles.form.create_title') }}
                    </h4>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success_msg'))
                        <div class="alert alert-success">{{ session('success_msg') }}</div>
                    @endif

                    <form method="POST" action="{{ $isEdit ? route('product-bundles.update', $bundle) : route('product-bundles.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.name') }}</label>
                            <input class="form-control" name="name" value="{{ old('name', $bundle->name) }}" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.slug') }}</label>
                                <input class="form-control" name="slug" value="{{ old('slug', $bundle->slug) }}" placeholder="{{ trans('plugins/product-bundles::bundles.form.placeholders.slug') }}">
                                <div class="form-text">{{ trans('plugins/product-bundles::bundles.form.fields.slug_help') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.image') }}</label>
                                @php
                                    // Prefer Botble's media picker if available.
                                    $imageValue = old('image', $bundle->image);
                                @endphp
                                @if (class_exists('Form') && method_exists(Form::class, 'mediaImage'))
                                    {!! Form::mediaImage('image', $imageValue) !!}
                                @else
                                    <input class="form-control" name="image" value="{{ $imageValue }}" placeholder="{{ trans('plugins/product-bundles::bundles.form.placeholders.image') }}">
                                @endif
                                <div class="form-text">{{ trans('plugins/product-bundles::bundles.form.fields.image_help') }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.description') }}</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description', $bundle->description) }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.type') }}</label>
                                <select class="form-select" name="type" id="pb_type">
                                    @foreach (['fixed' => trans('plugins/product-bundles::bundles.form.types.fixed'), 'mix' => trans('plugins/product-bundles::bundles.form.types.mix')] as $k => $label)
                                        <option value="{{ $k }}" @selected(old('type', $bundle->type) === $k)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.pricing_rule') }}</label>
                                <select class="form-select" name="pricing_type">
                                    @foreach (['fixed_total', 'percent_off', 'amount_off'] as $k)
                                        <option value="{{ $k }}" @selected(old('pricing_type', $bundle->pricing_type) === $k)>
                                            {{ trans('plugins/product-bundles::bundles.form.pricing_types.' . $k) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.pricing_value') }}</label>
                                <input class="form-control" name="pricing_value" type="number" step="0.01" value="{{ old('pricing_value', $bundle->pricing_value) }}" required>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.start_date') }}</label>
                                <input class="form-control" name="start_date" type="datetime-local"
                                    value="{{ old('start_date', optional($bundle->start_date)->format('Y-m-d\\TH:i')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.end_date') }}</label>
                                <input class="form-control" name="end_date" type="datetime-local"
                                    value="{{ old('end_date', optional($bundle->end_date)->format('Y-m-d\\TH:i')) }}">
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                            @checked(old('is_active', $bundle->is_active))>
                                        <label class="form-check-label">{{ trans('plugins/product-bundles::bundles.form.fields.active') }}</label>
                                    </div>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                            @checked(old('is_featured', $bundle->is_featured))>
                                        <label class="form-check-label">{{ trans('plugins/product-bundles::bundles.form.fields.featured') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.attach_products') }}</label>
                            <select class="form-select pb-product-select" name="attached_product_ids[]" multiple data-include-variations="0" data-placeholder="{{ trans('plugins/product-bundles::bundles.form.placeholders.select_products') }}">
                                @foreach ($attachedSelected as $pid)
                                    @php $pid = (int) $pid; @endphp
                                    <option value="{{ $pid }}" selected>{{ $productTextMap[$pid] ?? ('#' . $pid) }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ trans('plugins/product-bundles::bundles.form.fields.attach_help') }}</div>
                        </div>

                        <div id="pb_fixed_box" class="border rounded p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="mb-0">{{ trans('plugins/product-bundles::bundles.form.sections.fixed_items') }}</h5>
                                <button class="btn btn-sm btn-outline-primary" type="button" id="pb_add_fixed_item">{{ trans('plugins/product-bundles::bundles.form.actions.add_item') }}</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle" id="pb_fixed_table">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('plugins/product-bundles::bundles.form.table.product') }}</th>
                                            <th width="120">{{ trans('plugins/product-bundles::bundles.form.table.qty') }}</th>
                                            <th> </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $rows = old('fixed_items') ?: $fixedItems->map(fn($i) => ['product_id' => $i->product_id, 'variation_id' => $i->variation_id, 'quantity' => $i->quantity])->toArray();
                                        @endphp

                                        @forelse ($rows as $i => $row)
                                            @php $pid = (int) ($row['product_id'] ?? 0); @endphp
                                            <tr>
                                                <td>
                                                    <select class="form-select form-select-sm pb-product-select" name="fixed_items[{{ $i }}][product_id]" data-include-variations="1" data-placeholder="{{ trans('plugins/product-bundles::bundles.form.placeholders.search_product') }}">
                                                        <option value=""></option>
                                                        @if ($pid > 0)
                                                            <option value="{{ $pid }}" selected>{{ $productTextMap[$pid] ?? ('#' . $pid) }}</option>
                                                        @endif
                                                    </select>
                                                </td>
                                                <td><input class="form-control form-control-sm" name="fixed_items[{{ $i }}][quantity]" type="number" min="1" value="{{ $row['quantity'] ?? 1 }}"></td>
                                                <td class="text-end"><button class="btn btn-sm btn-outline-danger pb-remove-row" type="button">{{ trans('plugins/product-bundles::bundles.form.actions.remove') }}</button></td>
                                            </tr>
                                        @empty
                                            <tr class="pb-empty-row">
                                                <td colspan="3" class="text-muted">{{ trans('plugins/product-bundles::bundles.form.empties.no_fixed_items') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="pb_mix_box" class="border rounded p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="mb-0">{{ trans('plugins/product-bundles::bundles.form.sections.mix_groups') }}</h5>
                                <button class="btn btn-sm btn-outline-primary" type="button" id="pb_add_group">{{ trans('plugins/product-bundles::bundles.form.actions.add_group') }}</button>
                            </div>

                            <div id="pb_groups">
                                @php
                                    $groupRows = old('mix_groups') ?: $mixGroups->map(function($g){
                                        return [
                                            'name' => $g->name,
                                            'choose_min' => $g->choose_min,
                                            'choose_max' => $g->choose_max,
                                            'items' => $g->items->map(fn($it)=>['product_id'=>$it->product_id,'variation_id'=>$it->variation_id])->toArray(),
                                        ];
                                    })->toArray();
                                @endphp

                                @forelse ($groupRows as $gIndex => $gRow)
                                    @include('plugins/product-bundles::admin.partials.group', ['gIndex' => $gIndex, 'gRow' => $gRow, 'productTextMap' => $productTextMap])
                                @empty
                                    <div class="text-muted pb-no-groups">{{ trans('plugins/product-bundles::bundles.form.empties.no_groups') }}</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" type="submit">{{ trans('plugins/product-bundles::bundles.form.actions.save') }}</button>
                            <a class="btn btn-secondary" href="{{ route('product-bundles.index') }}">{{ trans('plugins/product-bundles::bundles.form.actions.back') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ trans('plugins/product-bundles::bundles.form.sections.tips') }}</h5></div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>{{ trans('plugins/product-bundles::bundles.form.tips_list.fixed_total') }}</li>
                        <li>{{ trans('plugins/product-bundles::bundles.form.tips_list.percent_off') }}</li>
                        <li>{{ trans('plugins/product-bundles::bundles.form.tips_list.attach') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer')
<script>
(function () {
    const adminCfg = {
        ajaxUrl: "{{ route('product-bundles.ajax.products') }}",
        placeholderSearch: "{{ trans('plugins/product-bundles::bundles.form.placeholders.search_product') }}",
        placeholderSelectProducts: "{{ trans('plugins/product-bundles::bundles.form.placeholders.select_products') }}",
        txtNoFixed: "{{ trans('plugins/product-bundles::bundles.form.empties.no_fixed_items') }}",
        txtNoGroups: "{{ trans('plugins/product-bundles::bundles.form.empties.no_groups') }}",
        txtNoGroupItems: "{{ trans('plugins/product-bundles::bundles.form.empties.no_group_items') }}",
        btnRemove: "{{ trans('plugins/product-bundles::bundles.form.actions.remove') }}",
    };

    function initSelect2(el, placeholder) {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) {
            return;
        }

        const $el = jQuery(el);
        if ($el.data('select2')) {
            return;
        }

        const includeVariations = parseInt($el.data('include-variations') || el.getAttribute('data-include-variations') || 0, 10) || 0;

        $el.select2({
            width: '100%',
            allowClear: true,
            placeholder: placeholder || '',
            ajax: {
                url: adminCfg.ajaxUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term || '', page: params.page || 1, include_variations: includeVariations };
                },
                processResults: function (data) {
                    return {
                        results: data && data.results ? data.results : [],
                        pagination: data && data.pagination ? data.pagination : { more: false }
                    };
                },
                cache: true
            }
        });
    }

    // init existing selects
    document.querySelectorAll('.pb-product-select').forEach(function (el) {
        const ph = el.getAttribute('data-placeholder') || adminCfg.placeholderSearch;
        initSelect2(el, ph);
    });

    const typeSelect = document.getElementById('pb_type');
    const fixedBox = document.getElementById('pb_fixed_box');
    const mixBox = document.getElementById('pb_mix_box');

    function toggleBoxes() {
        const t = typeSelect.value;
        fixedBox.style.display = (t === 'fixed') ? '' : 'none';
        mixBox.style.display = (t === 'mix') ? '' : 'none';
    }
    toggleBoxes();
    typeSelect.addEventListener('change', toggleBoxes);

    // fixed items
    const fixedTableBody = document.querySelector('#pb_fixed_table tbody');
    const addFixedBtn = document.getElementById('pb_add_fixed_item');

    function countRows(tbody) {
        return tbody.querySelectorAll('tr:not(.pb-empty-row)').length;
    }

    addFixedBtn.addEventListener('click', function () {
        fixedTableBody.querySelector('.pb-empty-row')?.remove();
        const i = countRows(fixedTableBody);

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select class="form-select form-select-sm pb-product-select" name="fixed_items[${i}][product_id]" data-include-variations="1" data-placeholder="${adminCfg.placeholderSearch}">
                    <option value=""></option>
                </select>
            </td>
            <td><input class="form-control form-control-sm" name="fixed_items[${i}][quantity]" type="number" min="1" value="1"></td>
            <td class="text-end"><button class="btn btn-sm btn-outline-danger pb-remove-row" type="button">${adminCfg.btnRemove}</button></td>
        `;
        fixedTableBody.appendChild(tr);

        initSelect2(tr.querySelector('.pb-product-select'), adminCfg.placeholderSearch);
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.pb-remove-row');
        if (!btn) {
            return;
        }

        const tr = btn.closest('tr');
        const tbody = btn.closest('tbody');
        tr?.remove();

        // Fixed table empties
        if (tbody && tbody.closest('#pb_fixed_table')) {
            if (!fixedTableBody.querySelector('tr:not(.pb-empty-row)')) {
                const empty = document.createElement('tr');
                empty.className = 'pb-empty-row';
                empty.innerHTML = `<td colspan="3" class="text-muted">${adminCfg.txtNoFixed}</td>`;
                fixedTableBody.appendChild(empty);
            }
            return;
        }

        // Group table empties
        if (tbody && !tbody.querySelector('tr:not(.pb-empty-row)')) {
            const empty = document.createElement('tr');
            empty.className = 'pb-empty-row';
            empty.innerHTML = `<td colspan="2" class="text-muted">${adminCfg.txtNoGroupItems}</td>`;
            tbody.appendChild(empty);
        }
    });

    // mix groups
    const groupsWrap = document.getElementById('pb_groups');
    const addGroupBtn = document.getElementById('pb_add_group');

    function groupIndex() {
        return groupsWrap.querySelectorAll('.pb-group').length;
    }

    addGroupBtn.addEventListener('click', function () {
        groupsWrap.querySelector('.pb-no-groups')?.remove();
        const gi = groupIndex();

        const div = document.createElement('div');
        div.innerHTML = document.getElementById('pb_group_template').innerHTML.replaceAll('__GI__', gi);
        const groupEl = div.firstElementChild;
        groupsWrap.appendChild(groupEl);

        groupEl.querySelectorAll('.pb-product-select').forEach(function (el) {
            initSelect2(el, adminCfg.placeholderSearch);
        });
    });

    document.addEventListener('click', function (e) {
        const rmGroup = e.target.closest('.pb-remove-group');
        if (rmGroup) {
            rmGroup.closest('.pb-group')?.remove();
            if (!groupsWrap.querySelector('.pb-group')) {
                const no = document.createElement('div');
                no.className = 'text-muted pb-no-groups';
                no.textContent = adminCfg.txtNoGroups;
                groupsWrap.appendChild(no);
            }
            return;
        }

        const addItem = e.target.closest('.pb-add-group-item');
        if (addItem) {
            const group = addItem.closest('.pb-group');
            const gi = group.getAttribute('data-gi');
            const tbody = group.querySelector('tbody');
            tbody.querySelector('.pb-empty-row')?.remove();

            const idx = countRows(tbody);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select class="form-select form-select-sm pb-product-select" name="mix_groups[${gi}][items][${idx}][product_id]" data-include-variations="1" data-placeholder="${adminCfg.placeholderSearch}">
                        <option value=""></option>
                    </select>
                </td>
                <td class="text-end"><button class="btn btn-sm btn-outline-danger pb-remove-row" type="button">${adminCfg.btnRemove}</button></td>
            `;
            tbody.appendChild(tr);

            initSelect2(tr.querySelector('.pb-product-select'), adminCfg.placeholderSearch);
        }
    });
})();
</script>

<script type="text/template" id="pb_group_template">
    @include('plugins/product-bundles::admin.partials.group', ['gIndex' => '__GI__', 'gRow' => ['name' => '', 'choose_min' => 1, 'choose_max' => 1, 'items' => []], 'productTextMap' => $productTextMap])
</script>
@endpush
