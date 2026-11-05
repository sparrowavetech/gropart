@php
    $currentDateFrom = request('date_from', request('order_date_from', ''));
    $currentDateTo = request('date_to', request('order_date_to', ''));
    $currentDateType = request('date_type', 'order_date');
    $bulkDownloadUrl = !empty($isVendor)
        ? route('marketplace.vendor.seller-invoicing.bulk-download')
        : (Route::has('seller-invoicing.bulk-download') ? route('seller-invoicing.bulk-download') : '');
@endphp

<div class="card mb-3 si-date-range-filter-card shadow-sm border">
    <div class="card-header py-2 px-3 bg-light-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-calendar-search text-primary fs-3"></i>
            <span class="fw-bold text-dark fs-5 mb-0">{{ trans('plugins/seller-invoicing::seller-invoicing.search_by_date_range') }}</span>
        </div>
        @if(!empty($currentDateFrom) || !empty($currentDateTo))
            <span class="badge bg-primary-lt text-primary px-2 py-1">
                <i class="ti ti-filter me-1"></i> {{ $currentDateFrom ?: 'Start' }} &rarr; {{ $currentDateTo ?: 'Today' }}
            </span>
        @endif
    </div>

    <div class="card-body p-3">
        <form class="si-date-filter-form row g-2 align-items-end" method="GET" onsubmit="return false;">
            <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                <label class="form-label text-muted small fw-semibold mb-1">
                    {{ trans('plugins/seller-invoicing::seller-invoicing.quick_range') }}
                </label>
                <select class="form-select form-select-sm si-quick-period">
                    <option value="">{{ trans('plugins/seller-invoicing::seller-invoicing.quick_range') }}</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="last_7_days">Last 7 Days</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                </select>
            </div>

            @if(empty($isOrderTable))
                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label text-muted small fw-semibold mb-1">
                        Filter By Field
                    </label>
                    <select class="form-select form-select-sm si-date-type">
                        <option value="order_date" {{ $currentDateType === 'order_date' ? 'selected' : '' }}>{{ trans('plugins/seller-invoicing::seller-invoicing.order_date') }}</option>
                        <option value="issued_at" {{ $currentDateType === 'issued_at' ? 'selected' : '' }}>{{ trans('plugins/seller-invoicing::seller-invoicing.invoice_date') }}</option>
                    </select>
                </div>
            @endif

            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <label class="form-label text-muted small fw-semibold mb-1">
                    <i class="ti ti-calendar me-1"></i>{{ trans('plugins/seller-invoicing::seller-invoicing.order_date_from') }}
                </label>
                <div class="input-group input-group-sm datepicker">
                    <input
                        type="text"
                        class="form-control si-date-from"
                        placeholder="YYYY-MM-DD"
                        name="date_from"
                        value="{{ $currentDateFrom }}"
                        data-input=""
                        readonly="readonly"
                    >
                    <button class="btn btn-icon btn-light" type="button" data-toggle="data-toggle" aria-label="Select date">
                        <i class="ti ti-calendar"></i>
                    </button>
                    <button class="btn btn-icon btn-light text-danger" type="button" data-clear="data-clear" aria-label="Clear date">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <label class="form-label text-muted small fw-semibold mb-1">
                    <i class="ti ti-calendar me-1"></i>{{ trans('plugins/seller-invoicing::seller-invoicing.order_date_to') }}
                </label>
                <div class="input-group input-group-sm datepicker">
                    <input
                        type="text"
                        class="form-control si-date-to"
                        placeholder="YYYY-MM-DD"
                        name="date_to"
                        value="{{ $currentDateTo }}"
                        data-input=""
                        readonly="readonly"
                    >
                    <button class="btn btn-icon btn-light" type="button" data-toggle="data-toggle" aria-label="Select date">
                        <i class="ti ti-calendar"></i>
                    </button>
                    <button class="btn btn-icon btn-light text-danger" type="button" data-clear="data-clear" aria-label="Clear date">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-auto ms-auto d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary si-btn-apply-filter px-3">
                    <i class="ti ti-filter me-1"></i> Apply
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary si-btn-reset-filter px-3" {{ empty($currentDateFrom) && empty($currentDateTo) ? 'style=display:none;' : '' }}>
                    <i class="ti ti-rotate-2 me-1"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

@push('footer')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.__si_date_range_filter_initialized) {
            return;
        }
        window.__si_date_range_filter_initialized = true;

        const padZero = (num) => String(num).padStart(2, '0');
        const formatDate = (d) => `${d.getFullYear()}-${padZero(d.getMonth() + 1)}-${padZero(d.getDate())}`;

        // Initialize flatpickr on the datepicker input groups
        function initDatepickers() {
            if (typeof flatpickr !== 'undefined') {
                $('.si-date-range-filter-card .datepicker').each(function () {
                    if (!this._flatpickr) {
                        flatpickr(this, {
                            wrap: true,
                            dateFormat: 'Y-m-d',
                            allowInput: false,
                            static: true,
                        });
                    }
                });
            } else if (typeof Botble !== 'undefined' && Botble.initResources) {
                Botble.initResources();
            }
        }
        initDatepickers();

        // Quick Period handler
        $(document).on('change', '.si-quick-period', function () {
            const val = $(this).val();
            const form = $(this).closest('.si-date-filter-form');
            const now = new Date();
            let from = '';
            let to = '';

            if (val === 'today') {
                from = formatDate(now);
                to = formatDate(now);
            } else if (val === 'yesterday') {
                const y = new Date();
                y.setDate(y.getDate() - 1);
                from = formatDate(y);
                to = formatDate(y);
            } else if (val === 'last_7_days') {
                const past = new Date();
                past.setDate(past.getDate() - 6);
                from = formatDate(past);
                to = formatDate(now);
            } else if (val === 'this_month') {
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                from = formatDate(firstDay);
                to = formatDate(lastDay);
            } else if (val === 'last_month') {
                const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);
                from = formatDate(firstDay);
                to = formatDate(lastDay);
            } else if (val === 'this_year') {
                const firstDay = new Date(now.getFullYear(), 0, 1);
                const lastDay = new Date(now.getFullYear(), 11, 31);
                from = formatDate(firstDay);
                to = formatDate(lastDay);
            }

            const fromInput = form.find('.si-date-from')[0];
            const toInput = form.find('.si-date-to')[0];

            if (fromInput && fromInput._flatpickr) {
                fromInput._flatpickr.setDate(from, true);
            } else {
                $(fromInput).val(from);
            }

            if (toInput && toInput._flatpickr) {
                toInput._flatpickr.setDate(to, true);
            } else {
                $(toInput).val(to);
            }
        });

        // Apply Filter
        $(document).on('click', '.si-btn-apply-filter', function (e) {
            e.preventDefault();
            const form = $(this).closest('.si-date-filter-form');
            const from = form.find('.si-date-from').val();
            const to = form.find('.si-date-to').val();
            const typeSelect = form.find('.si-date-type');
            const dateType = typeSelect.length ? typeSelect.val() : 'order_date';

            const currentUrl = new URL(window.location.href);
            const params = new URLSearchParams(currentUrl.search);

            if (from) {
                params.set('date_from', from);
                params.set('order_date_from', from);
            } else {
                params.delete('date_from');
                params.delete('order_date_from');
            }

            if (to) {
                params.set('date_to', to);
                params.set('order_date_to', to);
            } else {
                params.delete('date_to');
                params.delete('order_date_to');
            }

            if (dateType) {
                params.set('date_type', dateType);
            } else {
                params.delete('date_type');
            }

            window.history.pushState({}, '', `${currentUrl.pathname}?${params.toString()}`);
            form.find('.si-btn-reset-filter').show();

            const table = $('.table-wrapper table.dataTable');
            if (table.length && $.fn.DataTable.isDataTable(table)) {
                table.DataTable().ajax.url(window.location.href).load();
            } else {
                window.location.reload();
            }
        });

        // Reset Filter
        $(document).on('click', '.si-btn-reset-filter', function (e) {
            e.preventDefault();
            const form = $(this).closest('.si-date-filter-form');
            const fromInput = form.find('.si-date-from')[0];
            const toInput = form.find('.si-date-to')[0];

            if (fromInput && fromInput._flatpickr) {
                fromInput._flatpickr.clear();
            } else {
                $(fromInput).val('');
            }

            if (toInput && toInput._flatpickr) {
                toInput._flatpickr.clear();
            } else {
                $(toInput).val('');
            }

            form.find('.si-quick-period').val('');
            $(this).hide();

            const currentUrl = new URL(window.location.href);
            const params = new URLSearchParams(currentUrl.search);
            params.delete('date_from');
            params.delete('date_to');
            params.delete('order_date_from');
            params.delete('order_date_to');
            params.delete('date_type');

            window.history.pushState({}, '', `${currentUrl.pathname}${params.toString() ? '?' + params.toString() : ''}`);

            const table = $('.table-wrapper table.dataTable');
            if (table.length && $.fn.DataTable.isDataTable(table)) {
                table.DataTable().ajax.url(window.location.href).load();
            } else {
                window.location.reload();
            }
        });

        // Checkboxes & Bulk Download ZIP
        const bulkDownloadUrl = '{{ $bulkDownloadUrl }}';

        function updateBulkDownloadButtonState() {
            const table = $('.table-wrapper table.dataTable');
            if (!table.length) return;

            const checked = table.find('tbody input.checkboxes:checked');
            const btn = $('.btn-download-selected-invoices');
            if (!btn.length) return;

            if (checked.length > 0) {
                btn.removeClass('d-none');
                btn.html('<i class="ti ti-download me-1"></i> {{ trans("plugins/seller-invoicing::seller-invoicing.download_invoices") }} (' + checked.length + ')');
            } else {
                btn.addClass('d-none');
            }
        }

        $(document).on('change', '.table-wrapper table.dataTable input.checkboxes, .table-wrapper table.dataTable input.table-check-all', function () {
            setTimeout(updateBulkDownloadButtonState, 50);
        });

        $(document).on('draw.dt', '.table-wrapper table.dataTable', function () {
            $('.table-wrapper table.dataTable input.table-check-all').prop('checked', false);
            updateBulkDownloadButtonState();
        });

        $(document).on('click', '.btn-download-selected-invoices', function (e) {
            e.preventDefault();
            if (!bulkDownloadUrl) return;

            const checked = $('.table-wrapper table.dataTable tbody input.checkboxes:checked');
            if (!checked.length) {
                alert('{{ trans("plugins/seller-invoicing::seller-invoicing.no_invoices_selected") }}');
                return;
            }

            const token = $('meta[name="csrf-token"]').attr('content');
            const form = $('<form>', {
                action: bulkDownloadUrl,
                method: 'POST',
                style: 'display:none;'
            });

            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: token
            }));

            checked.each(function () {
                form.append($('<input>', {
                    type: 'hidden',
                    name: 'ids[]',
                    value: $(this).val()
                }));
            });

            $('body').append(form);
            form.submit();
            form.remove();
        });
    });
</script>
@endpush
