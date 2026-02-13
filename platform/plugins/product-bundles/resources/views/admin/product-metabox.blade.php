@php
    /**
     * @var \Botble\Ecommerce\Models\Product $product
     * @var \Botble\ProductBundles\Models\Bundle|null $bundle
     */
    $bundle = $bundle ?? null;
    $forceBundle = request()->boolean('bundle');
    $isBundle = (bool) old('pb_is_bundle', $bundle ? 1 : ($forceBundle ? 1 : 0));

    $attachedProducts = $bundle?->products ?? collect();
    $fixedItems = $bundle?->items ?? collect();
    $mixGroups = $bundle?->groups ?? collect();

    $attachedSelected = old('attached_product_ids');
    if (is_string($attachedSelected)) {
        $attachedSelected = collect(explode(',', $attachedSelected))
            ->map(fn ($v) => (int) trim($v))
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();
    }
    if (! is_array($attachedSelected)) {
        $attachedSelected = $attachedProducts->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
    }

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

    $rows = old('fixed_items') ?: $fixedItems->map(fn ($i) => [
        'product_id' => $i->product_id,
        'variation_id' => $i->variation_id,
        'quantity' => $i->quantity,
    ])->toArray();

    $groupRows = old('mix_groups') ?: $mixGroups->map(function ($g) {
        return [
            'name' => $g->name,
            'choose_min' => $g->choose_min,
            'choose_max' => $g->choose_max,
            'items' => $g->items->map(fn ($it) => [
                'product_id' => $it->product_id,
                'variation_id' => $it->variation_id,
            ])->toArray(),
        ];
    })->toArray();

    $typeValue = old('type', $bundle->type ?? 'fixed');
    $pricingType = old('pricing_type', $bundle->pricing_type ?? 'percent_off');
    $pricingValue = old('pricing_value', $bundle->pricing_value ?? 0);
@endphp

<div class="pb-bundle-metabox">
    <div class="form-check mb-3">
        <input type="hidden" name="pb_is_bundle" value="0">
        <input class="form-check-input" type="checkbox" name="pb_is_bundle" id="pb_is_bundle" value="1" @checked($isBundle)>
        <label class="form-check-label" for="pb_is_bundle">{{ trans('plugins/product-bundles::bundles.metabox.is_bundle') }}</label>
    </div>

    <div id="pb_bundle_config">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.type') }}</label>
                <select class="form-select" name="type" id="pb_type">
                    @foreach (['fixed' => trans('plugins/product-bundles::bundles.form.types.fixed'), 'mix' => trans('plugins/product-bundles::bundles.form.types.mix')] as $k => $label)
                        <option value="{{ $k }}" @selected($typeValue === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.pricing_rule') }}</label>
                <select class="form-select" name="pricing_type" id="pb_pricing_type">
                    @foreach (['fixed_total', 'percent_off', 'amount_off'] as $k)
                        <option value="{{ $k }}" @selected($pricingType === $k)>{{ trans('plugins/product-bundles::bundles.form.pricing_types.' . $k) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.pricing_value') }}</label>
                <input class="form-control" name="pricing_value" id="pb_pricing_value" type="number" step="0.01" value="{{ $pricingValue }}">
            </div>
        </div>

        <div class="pb-price-preview border rounded p-3 mb-3" id="pb_price_preview">
            <div class="fw-semibold mb-2">{{ trans('plugins/product-bundles::bundles.form.price_summary.title') }}</div>
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted">{{ trans('plugins/product-bundles::bundles.form.price_summary.base_total') }}</span>
                <span class="fw-semibold" data-pb-price-base>--</span>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted">{{ trans('plugins/product-bundles::bundles.form.price_summary.discount') }}</span>
                <span class="fw-semibold" data-pb-price-discount>--</span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted">{{ trans('plugins/product-bundles::bundles.form.price_summary.final_total') }}</span>
                <span class="fw-semibold" data-pb-price-final>--</span>
            </div>
            <div class="small text-muted mt-2" data-pb-price-note></div>
            <div class="small text-muted mt-2" data-pb-price-empty>{{ trans('plugins/product-bundles::bundles.form.price_summary.empty') }}</div>
        </div>

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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
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
                                <td>
                                    <input class="form-control form-control-sm" name="fixed_items[{{ $i }}][quantity]" type="number" min="1" value="{{ $row['quantity'] ?? 1 }}">
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger pb-remove-row" type="button">{{ trans('plugins/product-bundles::bundles.form.actions.remove') }}</button>
                                </td>
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
                @forelse ($groupRows as $gIndex => $gRow)
                    @include('plugins/product-bundles::admin.partials.group', ['gIndex' => $gIndex, 'gRow' => $gRow, 'productTextMap' => $productTextMap])
                @empty
                    <div class="text-muted pb-no-groups">{{ trans('plugins/product-bundles::bundles.form.empties.no_groups') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@once
    @push('footer')
        <script>
        (function () {
            const bundleToggle = document.getElementById('pb_is_bundle');
            const bundleConfig = document.getElementById('pb_bundle_config');
            if (!bundleToggle || !bundleConfig) {
                return;
            }

            const adminCfg = {
                ajaxUrl: "{{ route('product-bundles.ajax.products') }}",
                pricePreviewUrl: "{{ route('product-bundles.ajax.price-preview') }}",
                priceLoading: "{{ trans('plugins/product-bundles::bundles.form.price_summary.loading') }}",
                priceEmpty: "{{ trans('plugins/product-bundles::bundles.form.price_summary.empty') }}",
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

            function toggleBundle() {
                const enabled = bundleToggle.checked;
                bundleConfig.style.display = enabled ? '' : 'none';
                bundleConfig.querySelectorAll('input, select, textarea').forEach(function (el) {
                    el.disabled = !enabled;
                });
                toggleProductPrice(enabled);
                if (enabled) {
                    queuePricePreview();
                } else {
                    showPriceEmpty(adminCfg.priceEmpty);
                }
            }

            document.querySelectorAll('.pb-product-select').forEach(function (el) {
                const ph = el.getAttribute('data-placeholder') || adminCfg.placeholderSearch;
                initSelect2(el, ph);
            });

            function fieldWrapper(input) {
                if (!input) {
                    return null;
                }

                return input.closest('.col-md-4') || input.closest('.form-group') || input.closest('.mb-3');
            }

            function toggleProductPrice(isBundle) {
                const priceInput = document.querySelector('input[name="price"]');
                const saleInput = document.querySelector('input[name="sale_price"]');
                const saleTypeInput = document.querySelector('input[name="sale_type"]');

                const priceWrap = fieldWrapper(priceInput);
                const saleWrap = fieldWrapper(saleInput);

                if (priceWrap) {
                    priceWrap.style.display = isBundle ? 'none' : '';
                }

                if (saleWrap) {
                    saleWrap.style.display = isBundle ? 'none' : '';
                }

                document.querySelectorAll('.scheduled-time').forEach(function (el) {
                    el.style.display = isBundle ? 'none' : '';
                });

                if (priceInput) {
                    priceInput.disabled = isBundle;
                }

                if (saleInput) {
                    saleInput.disabled = isBundle;
                }

                if (saleTypeInput && isBundle) {
                    saleTypeInput.value = '0';
                }
            }

            const typeSelect = document.getElementById('pb_type');
            const fixedBox = document.getElementById('pb_fixed_box');
            const mixBox = document.getElementById('pb_mix_box');

            function toggleBoxes() {
                if (!typeSelect || !fixedBox || !mixBox) {
                    return;
                }

                const t = typeSelect.value;
                fixedBox.style.display = (t === 'fixed') ? '' : 'none';
                mixBox.style.display = (t === 'mix') ? '' : 'none';
            }
            toggleBoxes();
            typeSelect?.addEventListener('change', toggleBoxes);

            const pricingTypeSelect = document.getElementById('pb_pricing_type');
            const pricingValueInput = document.getElementById('pb_pricing_value');
            const pricePreview = document.getElementById('pb_price_preview');
            const priceBaseEl = pricePreview?.querySelector('[data-pb-price-base]');
            const priceDiscountEl = pricePreview?.querySelector('[data-pb-price-discount]');
            const priceFinalEl = pricePreview?.querySelector('[data-pb-price-final]');
            const priceNoteEl = pricePreview?.querySelector('[data-pb-price-note]');
            const priceEmptyEl = pricePreview?.querySelector('[data-pb-price-empty]');

            let priceTimer = null;
            let priceSeq = 0;

            function setPriceText(el, text) {
                if (el) {
                    el.textContent = text;
                }
            }

            function showPriceLoading() {
                if (!pricePreview) {
                    return;
                }
                setPriceText(priceBaseEl, adminCfg.priceLoading);
                setPriceText(priceDiscountEl, adminCfg.priceLoading);
                setPriceText(priceFinalEl, adminCfg.priceLoading);
                setPriceText(priceNoteEl, '');
                if (priceEmptyEl) {
                    priceEmptyEl.style.display = 'none';
                }
            }

            function showPriceEmpty(message) {
                if (!pricePreview) {
                    return;
                }
                setPriceText(priceBaseEl, '--');
                setPriceText(priceDiscountEl, '--');
                setPriceText(priceFinalEl, '--');
                setPriceText(priceNoteEl, '');
                if (priceEmptyEl) {
                    priceEmptyEl.textContent = message || adminCfg.priceEmpty;
                    priceEmptyEl.style.display = '';
                }
            }

            function buildPricePayload() {
                const payload = {
                    type: typeSelect?.value || 'fixed',
                    pricing_type: pricingTypeSelect?.value || 'percent_off',
                    pricing_value: parseFloat(pricingValueInput?.value || '0') || 0,
                    fixed_items: [],
                    mix_groups: [],
                };

                if (payload.type === 'fixed') {
                    document.querySelectorAll('#pb_fixed_table tbody tr:not(.pb-empty-row)').forEach(function (tr) {
                        const select = tr.querySelector('select[name*="[product_id]"]');
                        const qtyInput = tr.querySelector('input[name*="[quantity]"]');
                        const pid = parseInt(select?.value || '0', 10) || 0;
                        const qty = parseInt(qtyInput?.value || '1', 10) || 1;
                        if (pid > 0) {
                            payload.fixed_items.push({ product_id: pid, quantity: qty });
                        }
                    });
                } else {
                    document.querySelectorAll('#pb_groups .pb-group').forEach(function (group) {
                        const minInput = group.querySelector('input[name$="[choose_min]"]');
                        const min = parseInt(minInput?.value || '0', 10) || 0;
                        const items = [];

                        group.querySelectorAll('select[name*="[items]"][name$="[product_id]"]').forEach(function (sel) {
                            const pid = parseInt(sel.value || '0', 10) || 0;
                            if (pid > 0) {
                                items.push({ product_id: pid });
                            }
                        });

                        payload.mix_groups.push({ choose_min: min, items: items });
                    });
                }

                return payload;
            }

            function updatePricePreview() {
                if (!pricePreview || !adminCfg.pricePreviewUrl) {
                    return;
                }

                if (bundleToggle && !bundleToggle.checked) {
                    showPriceEmpty(adminCfg.priceEmpty);
                    return;
                }

                const payload = buildPricePayload();
                showPriceLoading();

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const seq = ++priceSeq;

                fetch(adminCfg.pricePreviewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(payload),
                })
                    .then(function (resp) {
                        if (!resp.ok) {
                            throw new Error('Bad response');
                        }
                        return resp.json();
                    })
                    .then(function (data) {
                        if (seq !== priceSeq) {
                            return;
                        }

                        if (!data || !data.has_items) {
                            showPriceEmpty(data?.empty || adminCfg.priceEmpty);
                            return;
                        }

                        setPriceText(priceBaseEl, data.base_total_formatted || '--');
                        setPriceText(priceDiscountEl, data.discount_total_formatted || '--');
                        setPriceText(priceFinalEl, data.final_total_formatted || '--');
                        setPriceText(priceNoteEl, data.note || '');
                        if (priceEmptyEl) {
                            priceEmptyEl.style.display = 'none';
                        }
                    })
                    .catch(function () {
                        if (seq !== priceSeq) {
                            return;
                        }
                        showPriceEmpty(adminCfg.priceEmpty);
                    });
            }

            function queuePricePreview() {
                if (!pricePreview) {
                    return;
                }

                if (priceTimer) {
                    clearTimeout(priceTimer);
                }

                priceTimer = setTimeout(updatePricePreview, 200);
            }

            pricingTypeSelect?.addEventListener('change', queuePricePreview);
            pricingValueInput?.addEventListener('input', queuePricePreview);
            typeSelect?.addEventListener('change', queuePricePreview);

            document.addEventListener('change', function (e) {
                if (e.target.closest('#pb_fixed_table') || e.target.closest('#pb_groups')) {
                    queuePricePreview();
                }
            });
            document.addEventListener('input', function (e) {
                if (e.target.closest('#pb_fixed_table') || e.target.closest('#pb_groups')) {
                    queuePricePreview();
                }
            });

            toggleBundle();
            bundleToggle.addEventListener('change', toggleBundle);

            const fixedTableBody = document.querySelector('#pb_fixed_table tbody');
            const addFixedBtn = document.getElementById('pb_add_fixed_item');

            function countRows(tbody) {
                return tbody.querySelectorAll('tr:not(.pb-empty-row)').length;
            }

            addFixedBtn?.addEventListener('click', function () {
                if (!fixedTableBody) {
                    return;
                }

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
                queuePricePreview();
            });

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.pb-remove-row');
                if (!btn) {
                    return;
                }

                const tr = btn.closest('tr');
                const tbody = btn.closest('tbody');
                tr?.remove();

                if (tbody && fixedTableBody && tbody.closest('#pb_fixed_table')) {
                    if (!fixedTableBody.querySelector('tr:not(.pb-empty-row)')) {
                        const empty = document.createElement('tr');
                        empty.className = 'pb-empty-row';
                        empty.innerHTML = `<td colspan="3" class="text-muted">${adminCfg.txtNoFixed}</td>`;
                        fixedTableBody.appendChild(empty);
                    }
                    queuePricePreview();
                    return;
                }

                if (tbody && !tbody.querySelector('tr:not(.pb-empty-row)')) {
                    const empty = document.createElement('tr');
                    empty.className = 'pb-empty-row';
                    empty.innerHTML = `<td colspan="2" class="text-muted">${adminCfg.txtNoGroupItems}</td>`;
                    tbody.appendChild(empty);
                }
                queuePricePreview();
            });

            const groupsWrap = document.getElementById('pb_groups');
            const addGroupBtn = document.getElementById('pb_add_group');

            function groupIndex() {
                return groupsWrap.querySelectorAll('.pb-group').length;
            }

            addGroupBtn?.addEventListener('click', function () {
                if (!groupsWrap) {
                    return;
                }

                groupsWrap.querySelector('.pb-no-groups')?.remove();
                const gi = groupIndex();

                const div = document.createElement('div');
                div.innerHTML = document.getElementById('pb_group_template').innerHTML.replaceAll('__GI__', gi);
                const groupEl = div.firstElementChild;
                groupsWrap.appendChild(groupEl);

                groupEl.querySelectorAll('.pb-product-select').forEach(function (el) {
                    initSelect2(el, adminCfg.placeholderSearch);
                });
                queuePricePreview();
            });

            document.addEventListener('click', function (e) {
                const rmGroup = e.target.closest('.pb-remove-group');
                if (rmGroup) {
                    rmGroup.closest('.pb-group')?.remove();
                    if (groupsWrap && !groupsWrap.querySelector('.pb-group')) {
                        const no = document.createElement('div');
                        no.className = 'text-muted pb-no-groups';
                        no.textContent = adminCfg.txtNoGroups;
                        groupsWrap.appendChild(no);
                    }
                    queuePricePreview();
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
                    queuePricePreview();
                }
            });
        })();
        </script>

        <script type="text/template" id="pb_group_template">
            @include('plugins/product-bundles::admin.partials.group', ['gIndex' => '__GI__', 'gRow' => ['name' => '', 'choose_min' => 1, 'choose_max' => 1, 'items' => []], 'productTextMap' => $productTextMap])
        </script>
    @endpush
@endonce
