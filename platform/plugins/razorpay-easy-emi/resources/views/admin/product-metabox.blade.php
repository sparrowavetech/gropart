@php
    $config = \SparroWave\RazorpayEasyEmi\Models\RazorpayEmiProductConfig::query()->where('product_id', $model->id)->first();
    $allOffers = \SparroWave\RazorpayEasyEmi\Models\RazorpayEmiCachedOffer::query()
        ->where('is_active', true)
        ->get();

    $emiOffers = $allOffers->filter(fn ($o) => $o->isEmiOffer());
    $otherOffers = $allOffers->filter(fn ($o) => $o->isNonEmiOffer());

    $status = $config?->status ?? 'inherit';
    $emiType = $config?->emi_type?->getValue() ?? 'standard';
    $offerMode = $config?->offer_mode ?? 'auto';
    $selectedOfferIds = $config?->offer_ids ?? [];
    $subvention = $config?->subvention_payer?->getValue() ?? 'platform';
    $customBadge = $config?->custom_badge ?? '';
    $minAmount = $config?->min_amount ? $config->min_amount : '';
@endphp

<div class="razorpay-easy-emi-metabox">
    {{-- Card 1: Scheme & General Config --}}
    <div class="row g-3 mb-3">
        {{-- 1. EMI Availability --}}
        <div class="col-md-6">
            <div class="form-group mb-0">
                <label for="razorpay_emi_status" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_label') }}</label>
                <select class="form-select" name="razorpay_emi_status" id="razorpay_emi_status">
                    <option value="inherit" @selected($status === 'inherit')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_inherit') }}</option>
                    <option value="enabled" @selected($status === 'enabled')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_enabled') }}</option>
                    <option value="disabled" @selected($status === 'disabled')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_disabled') }}</option>
                </select>
                <div class="form-text text-muted small mt-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_help') }}</div>
            </div>
        </div>

        {{-- 2. Offered EMI Scheme --}}
        <div class="col-md-6" id="razorpay_scheme_col">
            <div class="form-group mb-0">
                <label for="razorpay_emi_type" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.scheme_label') }}</label>
                <select class="form-select" name="razorpay_emi_type" id="razorpay_emi_type">
                    <option value="standard" @selected($emiType === 'standard')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.standard') }}</option>
                    <option value="no_cost" @selected($emiType === 'no_cost')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.no_cost') }}</option>
                    <option value="low_cost" @selected($emiType === 'low_cost')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.low_cost') }}</option>
                </select>
                <div class="form-text text-muted small mt-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.scheme_help') }}</div>
            </div>
        </div>

        {{-- 3. Subvention Payer (Visible when No Cost or Low Cost) --}}
        <div class="col-md-6" id="razorpay_subvention_col" style="display: {{ in_array($emiType, ['no_cost', 'low_cost']) ? 'block' : 'none' }};">
            <div class="form-group mb-0">
                <label for="razorpay_emi_subvention_payer" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.subvention_label') }}</label>
                <select class="form-select" name="razorpay_emi_subvention_payer" id="razorpay_emi_subvention_payer">
                    <option value="platform" @selected($subvention === 'platform')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.subvention.platform') }}</option>
                    <option value="seller" @selected($subvention === 'seller')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.subvention.seller') }}</option>
                </select>
                <div class="form-text text-muted small mt-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.subvention_help') }}</div>
            </div>
        </div>

        {{-- 4. Custom Promotional Badge --}}
        <div class="col-md-6 razorpay-dependent-field">
            <div class="form-group mb-0">
                <label for="razorpay_emi_custom_badge" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.badge_label') }}</label>
                <input type="text" class="form-control" name="razorpay_emi_custom_badge" id="razorpay_emi_custom_badge" value="{{ $customBadge }}" placeholder="{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.badge_placeholder') }}">
                <div class="form-text text-muted small mt-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.badge_help') }}</div>
            </div>
        </div>

        {{-- 5. Minimum Order Threshold --}}
        <div class="col-md-6 razorpay-dependent-field">
            <div class="form-group mb-0">
                <label for="razorpay_emi_min_amount" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.min_amount_label') }}</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control" name="razorpay_emi_min_amount" id="razorpay_emi_min_amount" value="{{ $minAmount }}" placeholder="{{ setting('razorpay_easy_emi_min_amount', 2500) }}">
                </div>
                <div class="form-text text-muted small mt-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.min_amount_help') }}</div>
            </div>
        </div>
    </div>

    {{-- Standard Scheme Notice --}}
    <div class="alert alert-info d-flex align-items-center mb-3 py-2 px-3 border-0 bg-info-subtle text-info-emphasis rounded-2" id="razorpay_standard_emi_note" style="display: {{ $emiType === 'standard' ? 'flex' : 'none' }};">
        <i class="ti ti-info-circle fs-3 me-2 flex-shrink-0"></i>
        <div class="small">
            <strong>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.standard') }}:</strong>
            {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.standard_emi_info') }}
        </div>
    </div>

    {{-- Card 2: Categorized Offers Management --}}
    <div class="card border rounded-2 shadow-none bg-body-tertiary razorpay-dependent-field" id="razorpay_offers_section">
        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="ti ti-discount-2 text-primary fs-4"></i>
                <span class="fw-bold small text-uppercase text-secondary">Razorpay Offers & Bank Promotions</span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2" id="btn-sync-offers-product" data-url="{{ route('razorpay-easy-emi.settings.sync-offers') }}">
                    <i class="ti ti-refresh" id="product-sync-icon"></i>
                    <span id="product-sync-text">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.sync_offers_btn') }}</span>
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            {{-- Offer Mode Selector --}}
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label for="razorpay_emi_offer_mode" class="form-label fw-bold mb-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.offer_mode_label') }}</label>
                    <select class="form-select" name="razorpay_emi_offer_mode" id="razorpay_emi_offer_mode">
                        <option value="auto" @selected($offerMode === 'auto')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.offer_mode_auto') }}</option>
                        <option value="custom" @selected($offerMode === 'custom')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.offer_mode_custom') }}</option>
                    </select>
                    <div class="form-text text-muted small mt-1">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.offer_mode_help') }}</div>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div id="razorpay_auto_mode_badge" class="alert alert-light border py-2 px-3 small w-100 mb-0" style="display: {{ $offerMode === 'auto' ? 'block' : 'none' }};">
                        <i class="ti ti-sparkles text-success me-1"></i>
                        <strong>Auto Mode Active:</strong> All active Razorpay offers matching customer order will appear automatically.
                    </div>
                </div>
            </div>

            {{-- Action Buttons Bar --}}
            <div class="d-flex justify-content-between align-items-center mb-2 pt-2 border-top">
                <div class="small fw-semibold text-muted text-uppercase">
                    Available Offers ({{ $allOffers->count() }} active)
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-link p-0 text-primary text-decoration-none small" id="btn-select-emi-offers">Select All EMI</button>
                    <span class="text-muted small">|</span>
                    <button type="button" class="btn btn-sm btn-link p-0 text-primary text-decoration-none small" id="btn-select-all-offers">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.select_all') }}</button>
                    <span class="text-muted small">|</span>
                    <button type="button" class="btn btn-sm btn-link p-0 text-primary text-decoration-none small" id="btn-deselect-all-offers">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.deselect_all') }}</button>
                </div>
            </div>

            @if ($allOffers->isEmpty())
                <div class="alert alert-warning py-2 px-3 small mb-0">
                    {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.no_offers_found') }}
                </div>
            @else
                <div class="row g-3">
                    {{-- Category 1: Bank EMI Cashback & Discounts --}}
                    <div class="col-md-6">
                        <div class="border rounded-2 bg-white overflow-hidden">
                            <div class="bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <span class="fw-bold small text-dark d-flex align-items-center gap-1">
                                    <i class="ti ti-credit-card text-primary"></i>
                                    Bank EMI Cashback & Discounts ({{ $emiOffers->count() }})
                                </span>
                            </div>
                            <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                                @forelse ($emiOffers as $offer)
                                    @php
                                        $isChecked = in_array($offer->id, $selectedOfferIds, true);
                                    @endphp
                                    <label class="list-group-item list-group-item-action d-flex align-items-start gap-2 py-2 px-3 border-bottom">
                                        <input class="form-check-input flex-shrink-0 mt-1 emi-offer-checkbox emi-type-checkbox" type="checkbox" name="razorpay_emi_offer_ids[]" value="{{ $offer->id }}" @checked($isChecked)>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark small">{{ $offer->clean_title }}</div>
                                            <div class="text-muted mt-1" style="font-size: 11px;">
                                                <span class="badge bg-light text-dark border me-1">{{ $offer->bank_issuer }}</span>
                                                {{ $offer->discount_summary }}
                                                @if ($offer->min_order_amount > 0)
                                                    &bull; Min ₹{{ number_format($offer->min_order_amount) }}
                                                @endif
                                            </div>
                                            <div class="text-secondary mt-1" style="font-size: 10px; font-family: monospace;">
                                                ID: {{ $offer->id }}
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if ($offer->isNoCostEmi())
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">⭐ No Cost</span>
                                            @elseif ($offer->isLowCostEmi())
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 10px;">📉 Low Cost</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 10px;">💳 Cashback</span>
                                            @endif
                                        </div>
                                    </label>
                                @empty
                                    <div class="p-3 text-muted text-center small">No bank EMI offers found.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Category 2: Other Payment Offers (Non-EMI) --}}
                    <div class="col-md-6">
                        <div class="border rounded-2 bg-white overflow-hidden">
                            <div class="bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <span class="fw-bold small text-dark d-flex align-items-center gap-1">
                                    <i class="ti ti-gift text-success"></i>
                                    Other Payment Offers (Non-EMI) ({{ $otherOffers->count() }})
                                </span>
                            </div>
                            <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                                @forelse ($otherOffers as $offer)
                                    @php
                                        $isChecked = in_array($offer->id, $selectedOfferIds, true);
                                    @endphp
                                    <label class="list-group-item list-group-item-action d-flex align-items-start gap-2 py-2 px-3 border-bottom">
                                        <input class="form-check-input flex-shrink-0 mt-1 emi-offer-checkbox non-emi-checkbox" type="checkbox" name="razorpay_emi_offer_ids[]" value="{{ $offer->id }}" @checked($isChecked)>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark small">{{ $offer->clean_title }}</div>
                                            <div class="text-muted mt-1" style="font-size: 11px;">
                                                <span class="badge bg-light text-dark border me-1">{{ $offer->bank_issuer }}</span>
                                                {{ $offer->discount_summary }}
                                                @if ($offer->min_order_amount > 0)
                                                    &bull; Min ₹{{ number_format($offer->min_order_amount) }}
                                                @endif
                                            </div>
                                            <div class="text-secondary mt-1" style="font-size: 10px; font-family: monospace;">
                                                ID: {{ $offer->id }}
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 10px;">🎁 Offer</span>
                                        </div>
                                    </label>
                                @empty
                                    <div class="p-3 text-muted text-center small">No other payment offers found.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div id="product-sync-feedback" class="mt-2 small fw-semibold text-success d-none"></div>
        </div>
    </div>
</div>

<script>
    (function () {
        var statusSelect = document.getElementById('razorpay_emi_status');
        var schemeCol = document.getElementById('razorpay_scheme_col');
        var schemeSelect = document.getElementById('razorpay_emi_type');
        var subventionCol = document.getElementById('razorpay_subvention_col');
        var standardNote = document.getElementById('razorpay_standard_emi_note');
        var offerModeSelect = document.getElementById('razorpay_emi_offer_mode');
        var autoBadge = document.getElementById('razorpay_auto_mode_badge');
        var dependentFields = document.querySelectorAll('.razorpay-dependent-field');
        var syncBtn = document.getElementById('btn-sync-offers-product');
        var btnSelectEmi = document.getElementById('btn-select-emi-offers');
        var btnSelectAll = document.getElementById('btn-select-all-offers');
        var btnDeselectAll = document.getElementById('btn-deselect-all-offers');

        function toggleVisibility() {
            var isStatusDisabled = statusSelect && statusSelect.value === 'disabled';

            if (schemeCol) {
                schemeCol.style.display = isStatusDisabled ? 'none' : '';
            }

            dependentFields.forEach(function (el) {
                el.style.display = isStatusDisabled ? 'none' : '';
            });

            if (isStatusDisabled) {
                if (subventionCol) subventionCol.style.display = 'none';
                if (standardNote) standardNote.style.display = 'none';
                return;
            }

            var currentScheme = schemeSelect ? schemeSelect.value : 'standard';
            var isStandard = currentScheme === 'standard';

            if (subventionCol) {
                subventionCol.style.display = isStandard ? 'none' : 'block';
            }
            if (standardNote) {
                standardNote.style.display = isStandard ? 'flex' : 'none';
            }
        }

        if (statusSelect) {
            statusSelect.addEventListener('change', toggleVisibility);
        }

        if (schemeSelect) {
            schemeSelect.addEventListener('change', toggleVisibility);
        }

        if (offerModeSelect) {
            offerModeSelect.addEventListener('change', function () {
                if (autoBadge) {
                    autoBadge.style.display = (this.value === 'auto') ? 'block' : 'none';
                }
            });
        }

        if (btnSelectEmi) {
            btnSelectEmi.addEventListener('click', function () {
                document.querySelectorAll('.emi-type-checkbox').forEach(function (cb) {
                    cb.checked = true;
                });
                document.querySelectorAll('.non-emi-checkbox').forEach(function (cb) {
                    cb.checked = false;
                });
                if (offerModeSelect) offerModeSelect.value = 'custom';
                if (autoBadge) autoBadge.style.display = 'none';
            });
        }

        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', function () {
                document.querySelectorAll('.emi-offer-checkbox').forEach(function (cb) {
                    cb.checked = true;
                });
                if (offerModeSelect) offerModeSelect.value = 'custom';
                if (autoBadge) autoBadge.style.display = 'none';
            });
        }

        if (btnDeselectAll) {
            btnDeselectAll.addEventListener('click', function () {
                document.querySelectorAll('.emi-offer-checkbox').forEach(function (cb) {
                    cb.checked = false;
                });
                if (offerModeSelect) offerModeSelect.value = 'custom';
                if (autoBadge) autoBadge.style.display = 'none';
            });
        }

        // Live sync button inside product metabox
        if (syncBtn) {
            syncBtn.addEventListener('click', function () {
                var url = this.getAttribute('data-url');
                var icon = document.getElementById('product-sync-icon');
                var text = document.getElementById('product-sync-text');
                var feedback = document.getElementById('product-sync-feedback');

                syncBtn.disabled = true;
                if (icon) icon.classList.add('ti-spin');
                if (text) text.textContent = 'Syncing...';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    syncBtn.disabled = false;
                    if (icon) icon.classList.remove('ti-spin');
                    if (text) text.textContent = '{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.sync_offers_btn') }}';

                    if (data.error) {
                        if (feedback) {
                            feedback.className = 'mt-2 small fw-semibold text-danger';
                            feedback.textContent = data.message || 'Failed to sync offers.';
                            feedback.classList.remove('d-none');
                        }
                    } else {
                        window.location.reload();
                    }
                })
                .catch(function () {
                    syncBtn.disabled = false;
                    if (icon) icon.classList.remove('ti-spin');
                    if (text) text.textContent = '{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.sync_offers_btn') }}';
                    if (feedback) {
                        feedback.className = 'mt-2 small fw-semibold text-danger';
                        feedback.textContent = 'Network or server error while syncing offers.';
                        feedback.classList.remove('d-none');
                    }
                });
            });
        }

        document.querySelectorAll('.emi-offer-checkbox').forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (offerModeSelect) offerModeSelect.value = 'custom';
                if (autoBadge) autoBadge.style.display = 'none';
            });
        });

        toggleVisibility();
    })();
</script>
