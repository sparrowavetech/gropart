@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-xl-6 col-lg-7 col-md-12">
                        <form action="{{ route('seller-invoicing.gst-reports.index') }}" method="GET" class="row g-2 align-items-center">
                            <div class="col-sm-5 col-12">
                                <label class="form-label small text-muted mb-1">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.period') }}</label>
                                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach ($periods as $pVal => $pLabel)
                                        <option value="{{ $pVal }}" @selected($selectedPeriod === $pVal)>{{ $pLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-7 col-12">
                                <label class="form-label small text-muted mb-1">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.store') }}</label>
                                <select name="store_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.all_stores') }}</option>
                                    @foreach ($stores as $s)
                                        <option value="{{ $s->id }}" @selected($selectedStoreId === $s->id)>
                                            {{ $s->name }} ({{ $s->tax_id ?: ($s->gstin ?: 'No GSTIN') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="col-xl-6 col-lg-5 col-md-12 d-flex flex-wrap align-items-end justify-content-xl-end gap-2 mt-2 mt-lg-auto">
                        <a href="{{ route('seller-invoicing.gst-reports.index', ['period' => $selectedPeriod, 'store_id' => $selectedStoreId, 'refresh' => 1]) }}" class="btn btn-sm btn-outline-secondary text-nowrap" title="{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.refresh_report') }}">
                            <x-core::icon name="ti ti-refresh" class="me-1" /> {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.refresh_report') }}
                        </a>

                        <form method="POST" action="{{ route('seller-invoicing.gst-reports.send-notifications') }}" class="d-inline" onsubmit="return confirm('Send monthly GST report readiness email notifications to active sellers and administrator for {{ $selectedPeriod }}?');">
                            @csrf
                            <input type="hidden" name="period" value="{{ $selectedPeriod }}">
                            @if ($selectedStoreId)
                                <input type="hidden" name="store_id" value="{{ $selectedStoreId }}">
                            @endif
                            <button type="submit" class="btn btn-sm btn-info text-white text-nowrap" title="{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.send_notification_btn') }}">
                                <x-core::icon name="ti ti-mail-forward" class="me-1" /> {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.send_notification_btn') }}
                            </button>
                        </form>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-success dropdown-toggle text-nowrap" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <x-core::icon name="ti ti-download" class="me-1" /> {{ trans('plugins/seller-invoicing::seller-invoicing.download_invoices') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('seller-invoicing.gst-reports.export-excel', ['period' => $selectedPeriod, 'store_id' => $selectedStoreId]) }}">
                                        <x-core::icon name="ti ti-file-spreadsheet" class="me-2 text-success" /> {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.export_excel') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('seller-invoicing.gst-reports.export-json', ['period' => $selectedPeriod, 'store_id' => $selectedStoreId]) }}">
                                        <x-core::icon name="ti ti-code" class="me-2 text-primary" /> {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.export_json') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('seller-invoicing.gst-reports.export-csv-zip', ['period' => $selectedPeriod, 'store_id' => $selectedStoreId]) }}">
                                        <x-core::icon name="ti ti-file-zip" class="me-2 text-warning" /> {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.export_csv_zip') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="col-sm-6 col-lg-3 mb-3 d-flex flex-column">
        <div class="card card-sm shadow-sm border-start border-primary border-3 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar">
                            <x-core::icon name="ti ti-shopping-cart" />
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium text-dark fs-3">
                            ₹{{ number_format((float) ($report['total_taxable_sales'] ?? 0), 2) }}
                        </div>
                        <div class="text-muted small">
                            {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.kpi_taxable_sales') }}
                            ({{ $report['order_count'] ?? 0 }} {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.kpi_orders') }})
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3 mb-3 d-flex flex-column">
        <div class="card card-sm shadow-sm border-start border-warning border-3 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-warning text-white avatar">
                            <x-core::icon name="ti ti-percentage" />
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium text-dark fs-3">
                            ₹{{ number_format((float) ($report['total_output_tax'] ?? 0), 2) }}
                        </div>
                        <div class="text-muted small">
                            {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.kpi_output_gst') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3 mb-3 d-flex flex-column">
        <div class="card card-sm shadow-sm border-start border-success border-3 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-success text-white avatar">
                            <x-core::icon name="ti ti-receipt-refund" />
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium text-success fs-3">
                            ₹{{ number_format((float) ($report['itc_gropart_fees'] ?? 0), 2) }}
                        </div>
                        <div class="text-muted small" title="{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.kpi_itc_helper') }}">
                            {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.kpi_itc_gropart') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3 mb-3 d-flex flex-column">
        <div class="card card-sm shadow-sm border-start border-danger border-3 h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-danger text-white avatar">
                            <x-core::icon name="ti ti-cash" />
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium text-dark fs-3">
                            ₹{{ number_format((float) ($report['net_gst_payable'] ?? 0), 2) }}
                        </div>
                        <div class="text-muted small" title="{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.kpi_net_helper') }}">
                            {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.kpi_net_payable') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Tabs -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom">
                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="#tab-gstr3b" class="nav-link active fw-bold" data-bs-toggle="tab" aria-selected="true" role="tab">
                            <x-core::icon name="ti ti-file-text" class="me-1 text-primary" /> {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.tab_gstr3b') }}
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#tab-gstr1" class="nav-link fw-bold" data-bs-toggle="tab" aria-selected="false" tabindex="-1" role="tab">
                            <x-core::icon name="ti ti-table" class="me-1 text-info" /> {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.tab_gstr1') }}
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#tab-hsn" class="nav-link fw-bold" data-bs-toggle="tab" aria-selected="false" tabindex="-1" role="tab">
                            <x-core::icon name="ti ti-box" class="me-1 text-warning" /> {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.tab_hsn') }}
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#tab-itc" class="nav-link fw-bold" data-bs-toggle="tab" aria-selected="false" tabindex="-1" role="tab">
                            <x-core::icon name="ti ti-receipt-tax" class="me-1 text-success" /> {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.tab_itc') }}
                            <span class="badge bg-success-subtle text-success ms-1">₹{{ number_format((float) ($report['itc_gropart_fees'] ?? 0), 2) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content">
                    <!-- Tab 1: GSTR-3B -->
                    <div class="tab-pane active show p-3" id="tab-gstr3b" role="tabpanel">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <x-core::icon name="ti ti-info-circle" class="me-1" />
                            {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.itc_note') }}
                        </div>

                        <h5 class="fw-bold text-dark mt-2 mb-3">
                            3.1 {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.nature_of_supplies') }}
                        </h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-vcenter table-bordered table-striped card-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.nature_of_supplies') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.taxable_value') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.integrated_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.central_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.state_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['gstr3b']['table_3_1'] ?? [] as $k => $r)
                                    <tr>
                                        <td>{{ $r['description'] ?? $k }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format((float) ($r['txval'] ?? 0), 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) ($r['iamt'] ?? 0), 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) ($r['camt'] ?? 0), 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) ($r['samt'] ?? 0), 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h5 class="fw-bold text-success mt-4 mb-3">
                            4. {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.tab_itc') }}
                        </h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-vcenter table-bordered table-striped card-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Details</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.taxable_value') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.integrated_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.central_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.state_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.total_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $itcRow = $report['gstr3b']['table_4_itc']['itc_available']['all_other_itc'] ?? []; @endphp
                                    <tr class="table-success-subtle">
                                        <td><strong>{{ $itcRow['description'] ?? '(5) All other ITC' }}</strong></td>
                                        <td class="text-end fw-bold">₹{{ number_format((float) ($itcRow['txval'] ?? 0), 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) ($itcRow['iamt'] ?? 0), 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) ($itcRow['camt'] ?? 0), 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) ($itcRow['samt'] ?? 0), 2) }}</td>
                                        <td class="text-end fw-bold text-success">₹{{ number_format((float) ($itcRow['total'] ?? 0), 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card bg-warning-subtle border-warning p-3 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.kpi_net_payable') }}</h5>
                                    <div class="text-muted small">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.kpi_net_helper') }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="fs-2 fw-bold text-dark">₹{{ number_format((float) ($report['net_gst_payable'] ?? 0), 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: GSTR-1 -->
                    <div class="tab-pane p-3" id="tab-gstr1" role="tabpanel">
                        <h5 class="fw-bold text-dark mb-2">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.b2b_title') }}</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-vcenter table-bordered table-striped card-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.customer_gstin') }}</th>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.customer_name') }}</th>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.invoice_number') }}</th>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.invoice_date') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.invoice_value') }}</th>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.place_of_supply') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.rate') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.taxable_value') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.total_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report['gstr1']['b2b'] ?? [] as $b2b)
                                    <tr>
                                        <td><code>{{ $b2b['ctin'] }}</code></td>
                                        <td>{{ $b2b['cname'] }}</td>
                                        <td>{{ $b2b['inum'] }}</td>
                                        <td>{{ $b2b['idt'] }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format((float) $b2b['val'], 2) }}</td>
                                        <td>{{ $b2b['pos'] }}</td>
                                        <td class="text-end">{{ $b2b['rate'] }}%</td>
                                        <td class="text-end">₹{{ number_format((float) $b2b['txval'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) ($b2b['iamt'] + $b2b['camt'] + $b2b['samt']), 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">No B2B supplies with customer GSTIN in this period.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <h5 class="fw-bold text-dark mt-4 mb-2">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.b2cs_title') }}</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-vcenter table-bordered table-striped card-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.type') }}</th>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.place_of_supply') }}</th>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.supply_type') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.rate') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.taxable_value') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.integrated_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.central_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.state_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report['gstr1']['b2cs'] ?? [] as $b2cs)
                                    <tr>
                                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $b2cs['typ'] }}</span></td>
                                        <td><strong>{{ $b2cs['pos'] }}</strong></td>
                                        <td>{{ $b2cs['sply_ty'] }}</td>
                                        <td class="text-end">{{ $b2cs['rate'] }}%</td>
                                        <td class="text-end fw-bold">₹{{ number_format((float) $b2cs['txval'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) $b2cs['iamt'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) $b2cs['camt'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) $b2cs['samt'], 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">No B2CS supplies in this period.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 3: Table 12 HSN Summary -->
                    <div class="tab-pane p-3" id="tab-hsn" role="tabpanel">
                        <h5 class="fw-bold text-dark mb-2">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.tab_hsn') }}</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-vcenter table-bordered table-striped card-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.hsn_code') }}</th>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.description') }}</th>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.uqc') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.total_qty') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.invoice_value') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.taxable_value') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.integrated_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.central_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.state_tax') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report['gstr1']['hsn'] ?? [] as $h)
                                    <tr>
                                        <td><code>{{ $h['hsn_sc'] }}</code></td>
                                        <td>{{ $h['desc'] }}</td>
                                        <td>{{ $h['uqc'] }}</td>
                                        <td class="text-end">{{ $h['qty'] }}</td>
                                        <td class="text-end">₹{{ number_format((float) $h['val'], 2) }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format((float) $h['txval'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) $h['iamt'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) $h['camt'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) $h['samt'], 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">No HSN items recorded.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 4: Marketplace Fees & ITC Sheet -->
                    <div class="tab-pane p-3" id="tab-itc" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold text-success mb-1">
                                    <x-core::icon name="ti ti-receipt-tax" class="me-1" />
                                    {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.itc_description') }}
                                </h5>
                                <div class="text-muted small">
                                    {{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.itc_note') }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success fs-5 px-3 py-2">
                                    Total Claimable ITC: ₹{{ number_format((float) ($report['itc_gropart_fees'] ?? 0), 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-vcenter table-bordered table-striped card-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.invoice_number') }}</th>
                                        <th>{{ trans('plugins/seller-invoicing::seller-invoicing.invoice_date') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.taxable_value') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.central_tax') }} (9%)</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.state_tax') }} (9%)</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.integrated_tax') }} (18%)</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.gst_reports.total_tax') }}</th>
                                        <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.grand_total') }}</th>
                                        <th class="text-center">{{ trans('plugins/seller-invoicing::seller-invoicing.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report['gstr3b']['itc_invoices'] ?? [] as $inv)
                                    <tr>
                                        <td><code>{{ $inv['number'] }}</code></td>
                                        <td>{{ $inv['date'] }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format((float) $inv['subtotal'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) $inv['cgst'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) $inv['sgst'], 2) }}</td>
                                        <td class="text-end">₹{{ number_format((float) $inv['igst'], 2) }}</td>
                                        <td class="text-end fw-bold text-success">₹{{ number_format((float) $inv['tax_amount'], 2) }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format((float) $inv['grand_total'], 2) }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('seller-invoicing.show', $inv['id']) }}" class="btn btn-sm btn-icon btn-outline-primary" target="_blank" title="{{ trans('plugins/seller-invoicing::seller-invoicing.view_details') }}">
                                                <x-core::icon name="ti ti-eye" />
                                            </a>
                                            <a href="{{ route('seller-invoicing.download', $inv['id']) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="{{ trans('plugins/seller-invoicing::seller-invoicing.download_pdf') }}">
                                                <x-core::icon name="ti ti-download" />
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">No seller fee invoices issued in this period.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

