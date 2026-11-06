@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header-action')
    <x-core::button
        tag="a"
        href="{{ $order->is_finished ? route('orders.edit', $order->id) : route('orders.view-incomplete-order', $order->id) }}"
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

    @if($order->is_finished)
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center">
                <x-core::icon name="ti ti-info-circle" class="me-2 fs-3" />
                <span>{{ trans('plugins/advanced-cod::advanced-cod.order_already_placed') }}</span>
            </div>
        </div>
    @endif

    @if(!$p['flags']['cod'])
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center">
                <x-core::icon name="ti ti-alert-triangle" class="me-2 fs-3" />
                <span>{{ trans('plugins/advanced-cod::advanced-cod.cod_not_eligible_for_all_items') }}</span>
            </div>
        </div>
    @endif

    @if(!empty($p['stale_attempt']))
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center">
                <x-core::icon name="ti ti-info-circle" class="me-2 fs-3" />
                <span>{{ trans('plugins/advanced-cod::advanced-cod.stale_attempt_notice') }}</span>
            </div>
        </div>
    @endif

    @if($p['locked'])
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center">
                <x-core::icon name="ti ti-lock" class="me-2 fs-3" />
                <span>{{ trans('plugins/advanced-cod::advanced-cod.existing_attempt_or_receipt_warning') }}</span>
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
                @php
                    $receipts = \SparroWave\AdvancedCod\Supports\Settlement::applies($order) ? \SparroWave\AdvancedCod\Supports\Settlement::summary($order) : null;
                    $actualReceived = ($receipts['received'] ?? 0) / 100;
                    $outstanding = max(0, $p['total'] - $actualReceived);
                @endphp
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-sm border bg-body-tertiary h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-secondary small fw-medium d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.order_total_with_fee') }}</span>
                                <div class="h3 mb-0 fw-bold text-dark">{{ format_price($p['total']) }}</div>
                                <small class="text-muted d-block mt-1">Fee: {{ format_price($p['fee']) }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-sm border border-primary-subtle bg-primary-lt h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-primary small fw-semibold d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.required_advance') }}</span>
                                <div class="h3 mb-0 fw-bold text-primary" id="cod-preview-advance">{{ format_price($p['advance']) }}</div>
                                <small class="text-primary-emphasis d-block mt-1">{{ $p['frozen'] ? trans('plugins/advanced-cod::advanced-cod.locked_intent') : trans('plugins/advanced-cod::advanced-cod.current_setting') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-sm border border-warning-subtle bg-warning-lt h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-warning-emphasis small fw-semibold d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.remaining_cod_delivery') }}</span>
                                <div class="h3 mb-0 fw-bold text-warning-emphasis" id="cod-preview-remaining">{{ format_price($p['remaining']) }}</div>
                                <small class="text-muted d-block mt-1">{{ trans('plugins/advanced-cod::advanced-cod.collected_on_delivery') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-sm border border-danger-subtle bg-danger-lt h-100">
                            <div class="card-body p-3 text-center">
                                <span class="text-danger small fw-semibold d-block text-uppercase mb-1">{{ trans('plugins/advanced-cod::advanced-cod.customer_outstanding') }}</span>
                                <div class="h3 mb-0 fw-bold text-danger">{{ format_price($outstanding) }}</div>
                                <small class="text-muted d-block mt-1">{{ trans('plugins/advanced-cod::advanced-cod.net_received') }}: {{ format_price($actualReceived) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>

    <!-- Customer Online Link Card -->
    <div class="col-12">
        <x-core::card class="mb-4 border-start border-3 border-info">
            <x-core::card.body class="py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <strong class="text-info-emphasis d-block">{{ trans('plugins/advanced-cod::advanced-cod.advance_pending_notice') }}</strong>
                    <span class="text-muted small">{{ trans('plugins/advanced-cod::advanced-cod.share_recovery_link') }}:</span>
                    <a href="{{ route('public.checkout.recover', $order->token) }}" target="_blank" rel="noopener" class="text-decoration-underline ms-1 small fw-semibold" id="checkout-recovery-url">
                        {{ route('public.checkout.recover', $order->token) }}
                    </a>
                </div>
                <div>
                    <x-core::button
                        type="button"
                        size="sm"
                        color="info"
                        class="btn-outline-info"
                        id="btn-copy-recovery-link"
                        icon="ti ti-copy"
                    >
                        {{ trans('plugins/advanced-cod::advanced-cod.copy_link') }}
                    </x-core::button>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>

    <!-- Confirmation Form -->
    @if(!$order->is_finished && !$p['retry_required'])
    <div class="col-12">
        <x-core::card class="mb-4">
            <x-core::card.header>
                <x-core::card.title>
                    <x-core::icon name="ti ti-shield-check" class="me-2 text-primary" />
                    {{ trans('plugins/advanced-cod::advanced-cod.confirm_and_place') }}
                </x-core::card.title>
            </x-core::card.header>
            <x-core::card.body class="p-4">
                <form action="{{ route('advanced-cod.confirm.store', $order->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="fingerprint" value="{{ $p['fingerprint'] }}">
                    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string)\Illuminate\Support\Str::uuid()) }}">

                    <div class="mb-4">
                        <label for="cod-confirm-mode" class="form-label fw-medium">{{ trans('plugins/advanced-cod::advanced-cod.confirmation_method') }}</label>
                        <select name="mode" id="cod-confirm-mode" class="form-select">
                            <option value="verified" @selected(old('mode') === 'verified')>
                                {{ trans('plugins/advanced-cod::advanced-cod.use_verified_online') }}
                            </option>
                            @if(!$p['locked'] && auth()->user()?->hasPermission('advanced-cod.manual-receipt'))
                                <option value="manual" @selected(old('mode', 'manual') === 'manual')>
                                    {{ trans('plugins/advanced-cod::advanced-cod.manual_advance_received') }}
                                </option>
                            @endif
                            @if(!$p['locked'] && !$p['frozen'] && get_payment_setting('admin_full_cod_override', 'cod', 0) && auth()->user()?->hasPermission('advanced-cod.override'))
                                <option value="full_cod" @selected(old('mode') === 'full_cod')>
                                    {{ trans('plugins/advanced-cod::advanced-cod.full_cod_override') }}
                                </option>
                            @endif
                        </select>
                    </div>

                    <!-- Manual Advance Fields Container -->
                    <div id="cod-manual-fields" class="card card-sm bg-body-tertiary border p-3 mb-4">
                        <h6 class="fw-bold mb-3 text-secondary d-flex align-items-center">
                            <x-core::icon name="ti ti-file-invoice" class="me-2 text-primary" />
                            {{ trans('plugins/advanced-cod::advanced-cod.manual_advance_receipt_details') }}
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium" for="cod-manual-amount">{{ trans('plugins/advanced-cod::advanced-cod.actual_advance_received') }} ({{ get_application_currency()->title }})</label>
                                <input class="form-control" type="number" step="0.01" min="0.01" name="amount" id="cod-manual-amount" value="{{ old('amount', $p['advance']) }}">
                                <small class="text-muted d-block mt-1">{{ trans('plugins/advanced-cod::advanced-cod.must_match_required_advance', ['amount' => format_price($p['advance'])]) }}</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium" for="cod-manual-method">{{ trans('plugins/advanced-cod::advanced-cod.receipt_method') }}</label>
                                <select class="form-select" name="method" id="cod-manual-method">
                                    <option value="upi" @selected(old('method', 'upi') === 'upi')>{{ trans('plugins/advanced-cod::advanced-cod.upi') }}</option>
                                    <option value="cash" @selected(old('method') === 'cash')>{{ trans('plugins/advanced-cod::advanced-cod.cash') }}</option>
                                    <option value="bank" @selected(old('method') === 'bank')>{{ trans('plugins/advanced-cod::advanced-cod.bank_transfer') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium" for="cod-manual-reference">{{ trans('plugins/advanced-cod::advanced-cod.receipt_reference') }}</label>
                                <input class="form-control" name="reference" id="cod-manual-reference" maxlength="120" value="{{ old('reference') }}" placeholder="{{ trans('plugins/advanced-cod::advanced-cod.reference_placeholder') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium" for="cod-manual-time">{{ trans('plugins/advanced-cod::advanced-cod.received_at') }} ({{ config('app.timezone') }})</label>
                                <input class="form-control" type="datetime-local" name="occurred_at" id="cod-manual-time" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium" for="cod-confirm-note">{{ trans('plugins/advanced-cod::advanced-cod.evidence_reason') }}</label>
                        <textarea class="form-control" name="note" id="cod-confirm-note" required maxlength="1000" rows="3" placeholder="{{ trans('plugins/advanced-cod::advanced-cod.evidence_reason_placeholder') }}">{{ old('note') }}</textarea>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <x-core::button type="submit" color="primary" icon="ti ti-check" :disabled="!$p['flags']['cod']">
                            {{ trans('plugins/advanced-cod::advanced-cod.confirm_and_place') }}
                        </x-core::button>
                    </div>
                </form>
            </x-core::card.body>
        </x-core::card>
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
                copyBtn.innerHTML = '<span class="d-inline-flex align-items-center"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="20 6 9 17 4 12"></polyline></svg> {{ trans("plugins/advanced-cod::advanced-cod.link_copied") }}</span>';
                setTimeout(() => { copyBtn.innerHTML = originalText; }, 2500);
            });
        });
    }
});
</script>
@endsection
