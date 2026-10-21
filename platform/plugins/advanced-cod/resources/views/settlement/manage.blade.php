@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="row">
    <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 text-dark fw-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-primary" style="vertical-align: -3px;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                {{ trans('plugins/advanced-cod::advanced-cod.settlement_management') }} — <span class="text-primary">{{ $order->code }}</span>
            </h4>
        </div>
        <div>
            <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-outline-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                {{ trans('plugins/advanced-cod::advanced-cod.back_to_order') }}
            </a>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="col-12 mb-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>{{ $errors->first() }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('success_msg'))
        <div class="col-12 mb-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>{{ session('success_msg') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Financial KPI Summary Cards -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-success"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    {{ trans('plugins/advanced-cod::advanced-cod.order_financial_overview') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="p-3 rounded bg-light border text-center h-100">
                            <span class="text-muted d-block small mb-1">{{ trans('plugins/advanced-cod::advanced-cod.order_total_with_fee') }}</span>
                            <h5 class="fw-bold mb-0 text-dark">{{ format_price($s['total'] / 100) }}</h5>
                            <small class="text-muted">Fee: {{ format_price($s['fee'] / 100) }}</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="p-3 rounded bg-primary bg-opacity-10 border border-primary text-center h-100">
                            <span class="text-primary d-block small mb-1 fw-bold">Advance Required / Paid</span>
                            <h5 class="fw-bold mb-0 text-primary">{{ format_price($s['advance_received'] / 100) }}</h5>
                            <small class="text-primary">Req: {{ format_price($s['required'] / 100) }}</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="p-3 rounded bg-success bg-opacity-10 border border-success text-center h-100">
                            <span class="text-success d-block small mb-1 fw-bold">Cash Collected</span>
                            <h5 class="fw-bold mb-0 text-success">{{ format_price(($s['courier'] + $s['direct']) / 100) }}</h5>
                            <small class="text-muted">Courier: {{ format_price($s['courier'] / 100) }}</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="p-3 rounded bg-info bg-opacity-10 border border-info text-center h-100">
                            <span class="text-info-emphasis d-block small mb-1 fw-bold">{{ trans('plugins/advanced-cod::advanced-cod.remitted_net') }}</span>
                            <h5 class="fw-bold mb-0 text-info-emphasis">{{ format_price($s['remitted'] / 100) }}</h5>
                            <small class="text-muted">Ded: {{ format_price($s['deductions'] / 100) }}</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="p-3 rounded bg-warning bg-opacity-10 border border-warning text-center h-100">
                            <span class="text-warning-emphasis d-block small mb-1 fw-bold">{{ trans('plugins/advanced-cod::advanced-cod.courier_awaiting_remittance') }}</span>
                            <h5 class="fw-bold mb-0 text-warning-emphasis">{{ format_price($s['remittance_pending'] / 100) }}</h5>
                            <small class="text-muted">In transit</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <div class="p-3 rounded bg-danger bg-opacity-10 border border-danger text-center h-100">
                            <span class="text-danger d-block small mb-1 fw-bold">{{ trans('plugins/advanced-cod::advanced-cod.customer_outstanding') }}</span>
                            <h5 class="fw-bold mb-0 text-danger">{{ format_price($s['outstanding'] / 100) }}</h5>
                            <small class="text-muted">{{ $s['label'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Entry Form -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-primary"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    {{ trans('plugins/advanced-cod::advanced-cod.record_settlement') }}
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('advanced-cod.settlement.store', $order->id) }}" id="settlement-record-form">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string)\Illuminate\Support\Str::uuid()) }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="cod-kind">{{ trans('plugins/advanced-cod::advanced-cod.operation') }}</label>
                            <select class="form-select form-select-lg" name="kind" id="cod-kind">
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
                            <label class="form-label fw-bold" for="cod-source">{{ trans('plugins/advanced-cod::advanced-cod.cash_collected_by') }}</label>
                            <select class="form-select form-select-lg" name="source" id="cod-source">
                                <option value="courier" @selected(old('source') === 'courier')>{{ trans('plugins/advanced-cod::advanced-cod.courier') }}</option>
                                <option value="direct" @selected(old('source') === 'direct')>{{ trans('plugins/advanced-cod::advanced-cod.merchant_direct') }}</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="wrapper-cod-reversal" style="display: none;">
                            <label class="form-label fw-bold text-danger" for="cod-reversal">{{ trans('plugins/advanced-cod::advanced-cod.original_entry_id') }}</label>
                            <select class="form-select form-select-lg" name="reversal_of" id="cod-reversal">
                                <option value="">-- Select entry to reverse --</option>
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
                            <label class="form-label fw-bold" for="cod-amount" id="label-cod-amount">{{ trans('plugins/advanced-cod::advanced-cod.net_amount') }} ({{ $s['currency'] }})</label>
                            <input class="form-control form-control-lg" id="cod-amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', '0.00') }}" required>
                            <small class="text-muted" id="help-cod-amount">Enter the cash collected or net money received.</small>
                        </div>

                        <div class="col-md-6" id="wrapper-cod-fee" style="display: none;">
                            <label class="form-label fw-bold" for="cod-fee">{{ trans('plugins/advanced-cod::advanced-cod.courier_deductions') }} ({{ $s['currency'] }})</label>
                            <input class="form-control form-control-lg" id="cod-fee" name="fee" type="number" min="0" step="0.01" value="{{ old('fee', '0.00') }}" required>
                            <small class="text-muted">Courier shipping/service deductions. Net received + deductions clears courier balance.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="cod-reference">{{ trans('plugins/advanced-cod::advanced-cod.receipt_reference') }}</label>
                            <input class="form-control" id="cod-reference" name="reference" maxlength="120" value="{{ old('reference') }}" placeholder="e.g. Courier Remittance Ref / Bank UTR / Receipt #" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="cod-time">{{ trans('plugins/advanced-cod::advanced-cod.received_at') }} ({{ config('app.timezone') }})</label>
                            <input class="form-control" id="cod-time" name="occurred_at" type="datetime-local" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" for="cod-note">{{ trans('plugins/advanced-cod::advanced-cod.evidence_reason') }}</label>
                            <textarea class="form-control" id="cod-note" name="note" maxlength="2000" rows="3" placeholder="Enter bank transaction details, courier settlement notes, or audit evidence..." required>{{ old('note') }}</textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <button class="btn btn-primary btn-lg px-4" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                {{ trans('plugins/advanced-cod::advanced-cod.record_settlement') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Settlement History Table -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-primary"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    {{ trans('plugins/advanced-cod::advanced-cod.settlement_history') }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">ID / Time</th>
                                <th>{{ trans('plugins/advanced-cod::advanced-cod.operation') }}</th>
                                <th>Source</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Deductions</th>
                                <th>Reference / Note</th>
                                <th class="pe-3">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($s['entries'] as $entry)
                                @php
                                    $isReversed = in_array($entry->id, $s['reversed']);
                                    $badgeClass = match($entry->kind) {
                                        'collection' => 'bg-success',
                                        'remittance' => 'bg-info',
                                        'refund' => 'bg-warning text-dark',
                                        'reversal' => 'bg-danger',
                                        default => 'bg-secondary',
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
                                            <span class="badge bg-dark ms-1">of #{{ $entry->reversal_of }}</span>
                                        @endif
                                        @if($isReversed)
                                            <span class="badge bg-secondary ms-1">Reversed</span>
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
                                        User #{{ $entry->user_id }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2 text-muted opacity-50"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                        <p class="mb-0">{{ trans('plugins/advanced-cod::advanced-cod.no_settlement_history') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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

    function updateFormFields() {
        if (!kindSelect) return;
        const kind = kindSelect.value;

        // Reset display
        wrapperSource.style.display = (kind === 'collection') ? 'block' : 'none';
        wrapperReversal.style.display = (kind === 'reversal') ? 'block' : 'none';
        wrapperFee.style.display = (kind === 'remittance') ? 'block' : 'none';

        if (kind === 'reversal') {
            amountInput.value = '0.00';
            amountInput.readOnly = true;
            feeInput.value = '0.00';
            if (helpAmount) helpAmount.textContent = 'Original entry amount will be automatically reversed.';
        } else {
            amountInput.readOnly = false;
            if (kind === 'collection') {
                if (helpAmount) helpAmount.textContent = 'Enter actual cash amount collected from the customer.';
            } else if (kind === 'remittance') {
                if (helpAmount) helpAmount.textContent = 'Enter the net money deposited by courier into your bank account.';
            } else if (kind === 'refund') {
                if (helpAmount) helpAmount.textContent = 'Enter cash refund returned to customer.';
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
