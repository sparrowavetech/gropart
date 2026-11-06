@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header-action')
    <x-core::button
        tag="a"
        href="{{ route('orders.edit', $order->id) }}"
        icon="ti ti-arrow-left"
    >
        {{ trans('plugins/advanced-cod::advanced-cod.back_to_order') }}
    </x-core::button>
@endpush

@section('content')
<div class="row row-cards">
    @if(isset($errors) && $errors->any())
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <x-core::icon name="ti ti-alert-circle" class="me-2 fs-3" />
                    <strong>{{ $errors->first() }}</strong>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('success_msg'))
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <x-core::icon name="ti ti-check" class="me-2 fs-3" />
                    <strong>{{ session('success_msg') }}</strong>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Financial KPI Summary Cards -->
    <div class="col-12">
        <x-core::card class="mb-4">
            <x-core::card.header>
                <x-core::card.title>
                    <x-core::icon name="ti ti-cash" class="me-2 text-primary" />
                    {{ trans('plugins/advanced-cod::advanced-cod.order_financial_overview') }}
                </x-core::card.title>
            </x-core::card.header>
            <x-core::card.body>
                <div class="row g-3">
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="card card-sm border bg-body-tertiary h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-secondary small fw-medium d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.order_total_with_fee') }}</span>
                                <div class="h3 mb-0 fw-bold text-dark">{{ format_price($s['total'] / 100) }}</div>
                                <small class="text-muted d-block mt-1">Fee: {{ format_price($s['fee'] / 100) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="card card-sm border border-primary-subtle bg-primary-lt h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-primary small fw-semibold d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.advance_required_or_paid') }}</span>
                                <div class="h3 mb-0 fw-bold text-primary">{{ format_price($s['advance_received'] / 100) }}</div>
                                <small class="text-primary-emphasis d-block mt-1">Req: {{ format_price($s['required'] / 100) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="card card-sm border border-success-subtle bg-success-lt h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-success small fw-semibold d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.cash_collected') }}</span>
                                <div class="h3 mb-0 fw-bold text-success">{{ format_price(($s['courier'] + $s['direct']) / 100) }}</div>
                                <small class="text-muted d-block mt-1">Courier: {{ format_price($s['courier'] / 100) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="card card-sm border border-info-subtle bg-info-lt h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-info-emphasis small fw-semibold d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.remitted_net') }}</span>
                                <div class="h3 mb-0 fw-bold text-info-emphasis">{{ format_price($s['remitted'] / 100) }}</div>
                                <small class="text-muted d-block mt-1">Ded: {{ format_price($s['deductions'] / 100) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="card card-sm border border-warning-subtle bg-warning-lt h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-warning-emphasis small fw-semibold d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.courier_awaiting_remittance') }}</span>
                                <div class="h3 mb-0 fw-bold text-warning-emphasis">{{ format_price($s['remittance_pending'] / 100) }}</div>
                                <small class="text-muted d-block mt-1">{{ trans('plugins/advanced-cod::advanced-cod.in_transit') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="card card-sm border border-danger-subtle bg-danger-lt h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-danger small fw-semibold d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.customer_outstanding') }}</span>
                                <div class="h3 mb-0 fw-bold text-danger">{{ format_price($s['outstanding'] / 100) }}</div>
                                <small class="text-muted d-block mt-1">{{ $s['label'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>

    <!-- Settlement Entry Form -->
    <div class="col-12">
        <x-core::card class="mb-4">
            <x-core::card.header>
                <x-core::card.title>
                    <x-core::icon name="ti ti-pencil-plus" class="me-2 text-primary" />
                    {{ trans('plugins/advanced-cod::advanced-cod.record_settlement') }}
                </x-core::card.title>
            </x-core::card.header>
            <x-core::card.body class="p-4">
                <form method="POST" action="{{ route('advanced-cod.settlement.store', $order->id) }}" id="settlement-record-form">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string)\Illuminate\Support\Str::uuid()) }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium" for="cod-kind">{{ trans('plugins/advanced-cod::advanced-cod.operation') }}</label>
                            <select class="form-select" name="kind" id="cod-kind">
                                <option value="collection" @selected(old('kind') === 'collection')>
                                    {{ trans('plugins/advanced-cod::advanced-cod.customer_cash_collected') }}
                                </option>
                                <option value="remittance" @selected(old('kind') === 'remittance')>
                                    {{ trans('plugins/advanced-cod::advanced-cod.courier_remittance_received') }}
                                </option>
                                <option value="refund" @selected(old('kind') === 'refund')>
                                    {{ trans('plugins/advanced-cod::advanced-cod.cash_refunded_to_customer') }}
                                </option>
                                <option value="reversal" @selected(old('kind') === 'reversal')>
                                    {{ trans('plugins/advanced-cod::advanced-cod.reverse_entry') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6" id="wrapper-cod-source">
                            <label class="form-label fw-medium" for="cod-source">{{ trans('plugins/advanced-cod::advanced-cod.cash_collected_by') }}</label>
                            <select class="form-select" name="source" id="cod-source">
                                <option value="courier" @selected(old('source') === 'courier')>{{ trans('plugins/advanced-cod::advanced-cod.courier') }}</option>
                                <option value="direct" @selected(old('source') === 'direct')>{{ trans('plugins/advanced-cod::advanced-cod.merchant_direct') }}</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="wrapper-cod-reversal" style="display: none;">
                            <label class="form-label fw-medium text-danger" for="cod-reversal">{{ trans('plugins/advanced-cod::advanced-cod.original_entry_id') }}</label>
                            <select class="form-select" name="reversal_of" id="cod-reversal">
                                <option value="">{{ trans('plugins/advanced-cod::advanced-cod.select_entry_to_reverse') }}</option>
                                @foreach($s['entries'] as $entry)
                                    @if($entry->kind !== 'reversal' && !in_array($entry->id, $s['reversed']))
                                        <option value="{{ $entry->id }}" @selected(old('reversal_of') == $entry->id)>
                                            #{{ $entry->id }} — {{ ucfirst($entry->kind) }} ({{ format_price($entry->amount_paise / 100) }}) - Ref: {{ $entry->reference }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6" id="wrapper-cod-amount">
                            <label class="form-label fw-medium" for="cod-amount" id="label-cod-amount">{{ trans('plugins/advanced-cod::advanced-cod.net_amount') }} ({{ $s['currency'] }})</label>
                            <input class="form-control" id="cod-amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', '0.00') }}" required>
                            <small class="text-muted d-block mt-1" id="help-cod-amount">{{ trans('plugins/advanced-cod::advanced-cod.help_cash_collected') }}</small>
                        </div>

                        <div class="col-md-6" id="wrapper-cod-fee" style="display: none;">
                            <label class="form-label fw-medium" for="cod-fee">{{ trans('plugins/advanced-cod::advanced-cod.courier_deductions') }} ({{ $s['currency'] }})</label>
                            <input class="form-control" id="cod-fee" name="fee" type="number" min="0" step="0.01" value="{{ old('fee', '0.00') }}" required>
                            <small class="text-muted d-block mt-1">{{ trans('plugins/advanced-cod::advanced-cod.help_courier_deductions') }}</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium" for="cod-reference">{{ trans('plugins/advanced-cod::advanced-cod.receipt_reference') }}</label>
                            <input class="form-control" id="cod-reference" name="reference" maxlength="120" value="{{ old('reference') }}" placeholder="{{ trans('plugins/advanced-cod::advanced-cod.reference_placeholder') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium" for="cod-time">{{ trans('plugins/advanced-cod::advanced-cod.received_at') }} ({{ config('app.timezone') }})</label>
                            <input class="form-control" id="cod-time" name="occurred_at" type="datetime-local" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium" for="cod-note">{{ trans('plugins/advanced-cod::advanced-cod.evidence_reason') }}</label>
                            <textarea class="form-control" id="cod-note" name="note" maxlength="2000" rows="3" placeholder="{{ trans('plugins/advanced-cod::advanced-cod.notes_placeholder') }}" required>{{ old('note') }}</textarea>
                        </div>

                        <div class="col-12 mt-3">
                            <x-core::button type="submit" color="primary" icon="ti ti-device-floppy">
                                {{ trans('plugins/advanced-cod::advanced-cod.record_settlement') }}
                            </x-core::button>
                        </div>
                    </div>
                </form>
            </x-core::card.body>
        </x-core::card>
    </div>

    <!-- Settlement History Table -->
    <div class="col-12">
        <x-core::card>
            <x-core::card.header>
                <x-core::card.title>
                    <x-core::icon name="ti ti-history" class="me-2 text-primary" />
                    {{ trans('plugins/advanced-cod::advanced-cod.settlement_history') }}
                </x-core::card.title>
            </x-core::card.header>
            <div class="table-responsive">
                <table class="table card-table table-vcenter table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">{{ trans('plugins/advanced-cod::advanced-cod.table_id_time') }}</th>
                            <th>{{ trans('plugins/advanced-cod::advanced-cod.operation') }}</th>
                            <th>{{ trans('plugins/advanced-cod::advanced-cod.table_source') }}</th>
                            <th class="text-end">{{ trans('plugins/advanced-cod::advanced-cod.table_amount') }}</th>
                            <th class="text-end">{{ trans('plugins/advanced-cod::advanced-cod.table_deductions') }}</th>
                            <th>{{ trans('plugins/advanced-cod::advanced-cod.table_reference_note') }}</th>
                            <th class="pe-3">{{ trans('plugins/advanced-cod::advanced-cod.table_recorded_by') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($s['entries'] as $entry)
                            @php
                                $isReversed = in_array($entry->id, $s['reversed']);
                                $badgeClass = match($entry->kind) {
                                    'collection' => 'bg-success text-white',
                                    'remittance' => 'bg-info text-white',
                                    'refund' => 'bg-warning text-dark',
                                    'reversal' => 'bg-danger text-white',
                                    default => 'bg-secondary text-white',
                                };
                            @endphp
                            <tr class="{{ $isReversed ? 'table-secondary opacity-75' : '' }}">
                                <td class="ps-3">
                                    <strong class="text-dark">#{{ $entry->id }}</strong><br>
                                    <small class="text-muted">{{ $entry->occurred_at }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }} px-2 py-1">
                                        {{ ucfirst($entry->kind) }}
                                    </span>
                                    @if($entry->reversal_of)
                                        <span class="badge bg-dark text-white ms-1">{{ trans('plugins/advanced-cod::advanced-cod.badge_reversal_of', ['id' => $entry->reversal_of]) }}</span>
                                    @endif
                                    @if($isReversed)
                                        <span class="badge bg-secondary text-white ms-1">{{ trans('plugins/advanced-cod::advanced-cod.badge_reversed') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-capitalize">{{ $entry->source }}</span>
                                </td>
                                <td class="text-end fw-bold {{ $entry->kind === 'refund' ? 'text-danger' : 'text-success' }}">
                                    {{ $entry->kind === 'refund' ? '-' : '' }}{{ format_price($entry->amount_paise / 100) }}
                                </td>
                                <td class="text-end text-muted">
                                    {{ $entry->fee_paise > 0 ? format_price($entry->fee_paise / 100) : '-' }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $entry->reference }}</div>
                                    <small class="text-muted">{{ Str::limit($entry->note, 60) }}</small>
                                </td>
                                <td class="pe-3 text-muted small">
                                    {{ trans('plugins/advanced-cod::advanced-cod.user_id_label', ['id' => $entry->user_id]) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <x-core::icon name="ti ti-receipt-off" class="fs-1 text-muted opacity-50 mb-2 d-block" />
                                    <p class="mb-0">{{ trans('plugins/advanced-cod::advanced-cod.no_settlement_history') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-core::card>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const kindSelect = document.getElementById('cod-kind');
    const wrapperSource = document.getElementById('wrapper-cod-source');
    const wrapperReversal = document.getElementById('wrapper-cod-reversal');
    const wrapperFee = document.getElementById('wrapper-cod-fee');
    const amountInput = document.getElementById('cod-amount');
    const feeInput = document.getElementById('cod-fee');
    const helpAmount = document.getElementById('help-cod-amount');

    const helpTexts = {
        collection: @json(trans('plugins/advanced-cod::advanced-cod.help_cash_collected')),
        remittance: @json(trans('plugins/advanced-cod::advanced-cod.help_remittance_received')),
        refund: @json(trans('plugins/advanced-cod::advanced-cod.help_cash_refunded')),
        reversal: @json(trans('plugins/advanced-cod::advanced-cod.help_reversal'))
    };

    function updateFormFields() {
        if (!kindSelect) return;
        const kind = kindSelect.value;

        // Reset display
        if (wrapperSource) wrapperSource.style.display = (kind === 'collection') ? 'block' : 'none';
        if (wrapperReversal) wrapperReversal.style.display = (kind === 'reversal') ? 'block' : 'none';
        if (wrapperFee) wrapperFee.style.display = (kind === 'remittance') ? 'block' : 'none';

        if (kind === 'reversal') {
            amountInput.value = '0.00';
            amountInput.readOnly = true;
            feeInput.value = '0.00';
            if (helpAmount) helpAmount.textContent = helpTexts.reversal;
        } else {
            amountInput.readOnly = false;
            if (helpAmount && helpTexts[kind]) {
                helpAmount.textContent = helpTexts[kind];
            }
        }
    }

    if (kindSelect) {
        kindSelect.addEventListener('change', updateFormFields);
        updateFormFields();
    }
});
</script>
@endsection
