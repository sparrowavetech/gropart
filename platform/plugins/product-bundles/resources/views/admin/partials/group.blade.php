@php
    $gi = $gIndex;
    $items = $gRow['items'] ?? [];
    $productTextMap = $productTextMap ?? [];
@endphp

<div class="pb-group border rounded p-3 mb-3" data-gi="{{ $gi }}">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-bold">{{ trans('plugins/product-bundles::bundles.form.sections.group') }}</div>
        <button class="btn btn-sm btn-outline-danger pb-remove-group" type="button">
            {{ trans('plugins/product-bundles::bundles.form.actions.remove_group') }}
        </button>
    </div>

    <div class="row g-3 mb-2">
        <div class="col-md-6">
            <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.group_name') }}</label>
            <input class="form-control form-control-sm" name="mix_groups[{{ $gi }}][name]" value="{{ $gRow['name'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.min') }}</label>
            <input class="form-control form-control-sm" type="number" min="0" name="mix_groups[{{ $gi }}][choose_min]" value="{{ $gRow['choose_min'] ?? 1 }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ trans('plugins/product-bundles::bundles.form.fields.max') }}</label>
            <input class="form-control form-control-sm" type="number" min="1" name="mix_groups[{{ $gi }}][choose_max]" value="{{ $gRow['choose_max'] ?? 1 }}">
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-bold">{{ trans('plugins/product-bundles::bundles.form.sections.items') }}</div>
        <button class="btn btn-sm btn-outline-primary pb-add-group-item" type="button">
            {{ trans('plugins/product-bundles::bundles.form.actions.add_item') }}
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>{{ trans('plugins/product-bundles::bundles.form.table.product') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $ii => $row)
                    @php $pid = (int) ($row['product_id'] ?? 0); @endphp
                    <tr>
                        <td>
                            <select class="form-select form-select-sm pb-product-select" name="mix_groups[{{ $gi }}][items][{{ $ii }}][product_id]" data-include-variations="1" data-placeholder="{{ trans('plugins/product-bundles::bundles.form.placeholders.search_product') }}">
                                <option value=""></option>
                                @if ($pid > 0)
                                    <option value="{{ $pid }}" selected>{{ $productTextMap[$pid] ?? ('#' . $pid) }}</option>
                                @endif
                            </select>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger pb-remove-row" type="button">
                                {{ trans('plugins/product-bundles::bundles.form.actions.remove') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="pb-empty-row">
                        <td colspan="2" class="text-muted">{{ trans('plugins/product-bundles::bundles.form.empties.no_group_items') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
