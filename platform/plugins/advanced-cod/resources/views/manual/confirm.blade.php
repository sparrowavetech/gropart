@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="row">
    <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 text-dark fw-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-primary" style="vertical-align: -3px;"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                {{ trans('plugins/advanced-cod::advanced-cod.manual_confirmation_title') }} — <span class="text-primary">{{ $order->code }}</span>
            </h4>
        </div>
        <div>
            <a href="{{ $order->is_finished ? route('orders.edit', $order->id) : route('orders.view-incomplete-order', $order->id) }}" class="btn btn-outline-secondary">
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

    @if($order->is_finished)
        <div class="col-12 mb-3">
            <div class="alert alert-info d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>This order has already been placed.</span>
            </div>
        </div>
    @endif

    @if(!$p['flags']['cod'])
        <div class="col-12 mb-3">
            <div class="alert alert-warning d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <span>COD is not enabled for every item in this order. Confirmation is blocked.</span>
            </div>
        </div>
    @endif

    @if(!empty($p['stale_attempt']))
        <div class="col-12 mb-3">
            <div class="alert alert-info d-flex align-items-center" style="background-color: #f0fdf4; border-color: #bbf7d0; color: #166534;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>{{ trans('plugins/advanced-cod::advanced-cod.stale_attempt_notice') }}</span>
            </div>
        </div>
    @endif

    @if($p['locked'])
        <div class="col-12 mb-3">
            <div class="alert alert-warning d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>A recorded gateway attempt or receipt exists on this order. Reconcile existing payments or use the verified advance option below.</span>
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
                @php
                    $receipts = \SparroWave\AdvancedCod\Supports\Settlement::applies($order) ? \SparroWave\AdvancedCod\Supports\Settlement::summary($order) : null;
                    $actualReceived = ($receipts['received'] ?? 0) / 100;
                    $outstanding = max(0, $p['total'] - $actualReceived);
                @endphp
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 rounded bg-light border text-center">
                            <span class="text-muted d-block small mb-1">{{ trans('plugins/advanced-cod::advanced-cod.order_total_with_fee') }}</span>
                            <h4 class="fw-bold mb-0 text-dark">{{ format_price($p['total']) }}</h4>
                            <small class="text-muted">Incl. fee: {{ format_price($p['fee']) }}</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 rounded bg-primary bg-opacity-10 border border-primary text-center">
                            <span class="text-primary d-block small mb-1 fw-bold">{{ trans('plugins/advanced-cod::advanced-cod.required_advance') }}</span>
                            <h4 class="fw-bold mb-0 text-primary" id="cod-preview-advance">{{ format_price($p['advance']) }}</h4>
                            <small class="text-primary">{{ $p['frozen'] ? 'Locked intent' : 'Current setting' }}</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 rounded bg-warning bg-opacity-10 border border-warning text-center">
                            <span class="text-warning-emphasis d-block small mb-1 fw-bold">{{ trans('plugins/advanced-cod::advanced-cod.remaining_cod_delivery') }}</span>
                            <h4 class="fw-bold mb-0 text-warning-emphasis" id="cod-preview-remaining">{{ format_price($p['remaining']) }}</h4>
                            <small class="text-muted">Collected on delivery</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 rounded bg-danger bg-opacity-10 border border-danger text-center">
                            <span class="text-danger d-block small mb-1 fw-bold">{{ trans('plugins/advanced-cod::advanced-cod.customer_outstanding') }}</span>
                            <h4 class="fw-bold mb-0 text-danger">{{ format_price($outstanding) }}</h4>
                            <small class="text-muted">Net received: {{ format_price($actualReceived) }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Online Link Card -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 border-start border-4 border-info">
            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <strong class="text-info-emphasis d-block">{{ trans('plugins/advanced-cod::advanced-cod.advance_pending_notice') }}</strong>
                    <span class="text-muted small">{{ trans('plugins/advanced-cod::advanced-cod.share_recovery_link') }}:</span>
                    <a href="{{ route('public.checkout.recover', $order->token) }}" target="_blank" rel="noopener" class="text-decoration-underline ms-1 small fw-semibold" id="checkout-recovery-url">
                        {{ route('public.checkout.recover', $order->token) }}
                    </a>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-info" id="btn-copy-recovery-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        {{ trans('plugins/advanced-cod::advanced-cod.copy_link') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Form -->
    @if(!$order->is_finished && !$p['retry_required'])
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-primary"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    {{ trans('plugins/advanced-cod::advanced-cod.confirm_and_place') }}
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('advanced-cod.confirm.store', $order->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="fingerprint" value="{{ $p['fingerprint'] }}">
                    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string)\Illuminate\Support\Str::uuid()) }}">

                    <div class="mb-4">
                        <label for="cod-confirm-mode" class="form-label fw-bold">{{ trans('plugins/advanced-cod::advanced-cod.confirmation_method') }}</label>
                        <select name="mode" id="cod-confirm-mode" class="form-select form-select-lg">
                            <option value="verified" @selected(old('mode') === 'verified')>
                                {{ trans('plugins/advanced-cod::advanced-cod.use_verified_online') }}
                            </option>
                            @if(!$p['locked'] && auth()->user()->hasPermission('advanced-cod.manual-receipt'))
                                <option value="manual" @selected(old('mode', 'manual') === 'manual')>
                                    {{ trans('plugins/advanced-cod::advanced-cod.manual_advance_received') }}
                                </option>
                            @endif
                            @if(!$p['locked'] && !$p['frozen'] && get_payment_setting('admin_full_cod_override', 'cod', 0) && auth()->user()->hasPermission('advanced-cod.override'))
                                <option value="full_cod" @selected(old('mode') === 'full_cod')>
                                    {{ trans('plugins/advanced-cod::advanced-cod.full_cod_override') }}
                                </option>
                            @endif
                        </select>
                    </div>

                    <!-- Manual Advance Fields Container -->
                    <div id="cod-manual-fields" class="p-3 mb-4 rounded bg-light border">
                        <h6 class="fw-bold mb-3 text-secondary d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                            Manual Advance Receipt Details
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="cod-manual-amount">{{ trans('plugins/advanced-cod::advanced-cod.actual_advance_received') }} ({{ get_application_currency()->title }})</label>
                                <input class="form-control" type="number" step="0.01" min="0.01" name="amount" id="cod-manual-amount" value="{{ old('amount', $p['advance']) }}">
                                <small class="text-muted">Must match required advance: {{ format_price($p['advance']) }}</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="cod-manual-method">{{ trans('plugins/advanced-cod::advanced-cod.receipt_method') }}</label>
                                <select class="form-select" name="method" id="cod-manual-method">
                                    <option value="upi" @selected(old('method', 'upi') === 'upi')>{{ trans('plugins/advanced-cod::advanced-cod.upi') }}</option>
                                    <option value="cash" @selected(old('method') === 'cash')>{{ trans('plugins/advanced-cod::advanced-cod.cash') }}</option>
                                    <option value="bank" @selected(old('method') === 'bank')>{{ trans('plugins/advanced-cod::advanced-cod.bank_transfer') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="cod-manual-reference">{{ trans('plugins/advanced-cod::advanced-cod.receipt_reference') }}</label>
                                <input class="form-control" name="reference" id="cod-manual-reference" maxlength="120" value="{{ old('reference') }}" placeholder="e.g. UPI Ref / Cash receipt # / UTR">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="cod-manual-time">{{ trans('plugins/advanced-cod::advanced-cod.received_at') }} ({{ config('app.timezone') }})</label>
                                <input class="form-control" type="datetime-local" name="occurred_at" id="cod-manual-time" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold" for="cod-confirm-note">{{ trans('plugins/advanced-cod::advanced-cod.evidence_reason') }}</label>
                        <textarea class="form-control" name="note" id="cod-confirm-note" required maxlength="1000" rows="3" placeholder="{{ trans('plugins/advanced-cod::advanced-cod.evidence_reason_placeholder') }}">{{ old('note') }}</textarea>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-success btn-lg px-4" type="submit" @disabled(!$p['flags']['cod'])>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            {{ trans('plugins/advanced-cod::advanced-cod.confirm_and_place') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeSelect = document.getElementById('cod-confirm-mode');
    const manualFields = document.getElementById('cod-manual-fields');
    const previewAdvance = document.getElementById('cod-preview-advance');
    const previewRemaining = document.getElementById('cod-preview-remaining');

    function updateView() {
        if (!modeSelect) return;
        const isManual = modeSelect.value === 'manual';
        const isFullCod = modeSelect.value === 'full_cod';

        if (previewAdvance) {
            previewAdvance.textContent = isFullCod ? @json(format_price(0)) : @json(format_price($p['advance']));
        }
        if (previewRemaining) {
            previewRemaining.textContent = isFullCod ? @json(format_price($p['total'])) : @json(format_price($p['remaining']));
        }

        if (manualFields) {
            manualFields.style.display = isManual ? 'block' : 'none';
            manualFields.querySelectorAll('input, select').forEach(el => {
                el.required = isManual;
            });
        }
    }

    if (modeSelect) {
        modeSelect.addEventListener('change', updateView);
        updateView();
    }

    const copyBtn = document.getElementById('btn-copy-recovery-link');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const url = document.getElementById('checkout-recovery-url').href;
            navigator.clipboard.writeText(url).then(function () {
                const originalText = copyBtn.innerHTML;
                copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="20 6 9 17 4 12"></polyline></svg> {{ trans("plugins/advanced-cod::advanced-cod.link_copied") }}';
                setTimeout(() => { copyBtn.innerHTML = originalText; }, 2500);
            });
        });
    }
});
</script>
@endsection
