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

<style>
    #razorpay_easy_emi_meta_box {
        border: 1px solid #bfdbfe !important;
        box-shadow: 0 10px 28px rgba(37, 99, 235, 0.08) !important;
        border-radius: 8px !important;
        overflow: hidden !important;
    }

    #razorpay_easy_emi_meta_box .card-header {
        background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%) !important;
        border-bottom: 1px solid #dbeafe !important;
        padding: 14px 20px !important;
    }

    #razorpay_easy_emi_meta_box .card-title {
        color: #1e40af !important;
        font-weight: 700 !important;
        font-size: 14.5px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    #razorpay_easy_emi_meta_box .card-body {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f7fe 50%, #fbfdfc 100%) !important;
        padding: 22px !important;
    }

    /* 1. Hero Availability Bar (COD Design Language) */
    /* Universal Section Typography (100% Consistent Across All Sections) */
    .rzp-meta-title {
        font-size: 13.5px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 2px !important;
        line-height: 1.35 !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .rzp-meta-subtitle {
        font-size: 12px !important;
        font-weight: 400 !important;
        color: #64748b !important;
        margin-bottom: 0 !important;
        line-height: 1.45 !important;
        display: block !important;
    }

    .rzp-hero-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 22px;
    }

    .rzp-status-toggle-wrap {
        display: inline-flex;
        background: #e2e8f0;
        padding: 4px;
        border-radius: 8px;
        gap: 3px;
        flex-shrink: 0;
    }

    .rzp-status-toggle-btn {
        border: none;
        background: transparent;
        padding: 7px 15px;
        font-size: 12.5px;
        font-weight: 600;
        border-radius: 6px;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .rzp-status-toggle-btn:hover {
        color: #0f172a;
    }

    .rzp-status-toggle-btn.active {
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12);
        color: #1e40af;
    }

    .rzp-status-toggle-btn.active.is-enabled {
        color: #047857;
    }

    .rzp-status-toggle-btn.active.is-disabled {
        color: #b91c1c;
    }

    /* 2. Scheme Selector Cards */
    .rzp-scheme-card {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .rzp-scheme-card:hover {
        border-color: #93c5fd;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.08);
        transform: translateY(-1px);
    }

    .rzp-scheme-card.active {
        border-color: #2563eb;
        background: #f8faff;
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.14);
    }

    .rzp-scheme-card .check-circle {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: transparent;
        transition: all 0.2s ease;
    }

    .rzp-scheme-card.active .check-circle {
        border-color: #2563eb;
        background: #2563eb;
        color: #ffffff;
    }

    .rzp-scheme-badge {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
        margin-bottom: 8px;
    }

    /* 3. Offers Management Panel */
    .rzp-offers-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .rzp-offers-panel-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 18px;
    }

    /* Nav Tabs for Category Filtering */
    .rzp-category-tabs {
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 8px 16px 0 16px;
        display: flex;
        gap: 6px;
    }

    .rzp-tab-btn {
        border: 1px solid transparent;
        border-bottom: none;
        background: transparent;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 16px;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: -1px;
    }

    .rzp-tab-btn:hover {
        color: #0f172a;
        background: #ffffff;
    }

    .rzp-tab-btn.active {
        color: #1e40af;
        background: #ffffff;
        border-color: #e2e8f0;
        border-top: 2px solid #2563eb;
    }

    /* Spacious Offer Card */
    .rzp-offer-box {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        padding: 12px 16px;
        margin-bottom: 10px;
        transition: all 0.15s ease;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .rzp-offer-box:hover {
        border-color: #3b82f6;
        background: #f8faff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.06);
    }

    .rzp-offer-box.is-checked {
        border-color: #2563eb;
        background: #eff6ff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
    }

    .rzp-offer-title {
        font-size: 13.5px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.45;
        white-space: normal !important;
        word-break: break-word;
    }

    .rzp-offer-desc {
        font-size: 12px;
        color: #64748b;
        line-height: 1.4;
        margin-top: 4px;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .ti-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        100% { transform: rotate(360deg); }
    }
</style>

<div class="razorpay-easy-emi-metabox">
    {{-- 1. Hero Availability Bar (Clean COD-style toggle) --}}
    <div class="rzp-hero-bar">
        <div>
            <div class="rzp-meta-title">
                <svg width="18" height="18" viewBox="0 0 16 18" fill="none" class="flex-shrink-0">
                    <path d="M9.5 0L1 9.5H6.5L4 18L15 7H9.5L12 0H9.5Z" fill="#2b83ea"/>
                </svg>
                <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_label') }}</span>
                <span id="rzp_status_badge" class="badge bg-secondary-subtle text-secondary border ms-1 fs-7"></span>
            </div>
            <div class="rzp-meta-subtitle">
                {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_help') }}
            </div>
        </div>

        {{-- Hidden backing select for standard form persistence --}}
        <select class="d-none" name="razorpay_emi_status" id="razorpay_emi_status">
            <option value="inherit" @selected($status === 'inherit')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_inherit') }}</option>
            <option value="enabled" @selected($status === 'enabled')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_enabled') }}</option>
            <option value="disabled" @selected($status === 'disabled')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.status_disabled') }}</option>
        </select>

        {{-- Visual Segmented Switcher --}}
        <div class="rzp-status-toggle-wrap">
            <button type="button" class="rzp-status-toggle-btn @if($status === 'inherit') active @endif" data-val="inherit">
                <i class="ti ti-settings fs-6"></i>
                <span>Store Default</span>
            </button>
            <button type="button" class="rzp-status-toggle-btn is-enabled @if($status === 'enabled') active @endif" data-val="enabled">
                <i class="ti ti-check fs-6"></i>
                <span>Enable</span>
            </button>
            <button type="button" class="rzp-status-toggle-btn is-disabled @if($status === 'disabled') active @endif" data-val="disabled">
                <i class="ti ti-ban fs-6"></i>
                <span>Disable</span>
            </button>
        </div>
    </div>

    {{-- When Disabled Notice --}}
    <div id="rzp_disabled_notice" class="alert alert-warning d-flex align-items-center mb-0 py-3 px-3 border-0 bg-warning-subtle text-warning-emphasis rounded-2" style="display: {{ $status === 'disabled' ? 'flex' : 'none' }};">
        <i class="ti ti-alert-triangle fs-3 me-2 flex-shrink-0"></i>
        <div>
            <strong>EMI Plans & Badges Disabled:</strong>
            Razorpay Affordability widget and catalog badges will be completely hidden for this product.
        </div>
    </div>

    {{-- Main Dependent Body --}}
    <div id="rzp_dependent_content" style="display: {{ $status === 'disabled' ? 'none' : 'block' }};">
        {{-- 2. Scheme Selector Visual Cards --}}
        <div class="mb-4">
            <div class="mb-2">
                <div class="rzp-meta-title">
                    <i class="ti ti-chart-arrows text-primary"></i>
                    <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.scheme_label') }}</span>
                </div>
                <div class="rzp-meta-subtitle">
                    Select which financing scheme to offer to buyers for this product
                </div>
            </div>

            {{-- Backing select --}}
            <select class="d-none" name="razorpay_emi_type" id="razorpay_emi_type">
                <option value="standard" @selected($emiType === 'standard')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.standard') }}</option>
                <option value="no_cost" @selected($emiType === 'no_cost')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.no_cost') }}</option>
                <option value="low_cost" @selected($emiType === 'low_cost')>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.schemes.low_cost') }}</option>
            </select>

            <div class="row g-3">
                {{-- Card A: No Cost EMI --}}
                <div class="col-md-4">
                    <div class="rzp-scheme-card @if($emiType === 'no_cost') active @endif" data-scheme="no_cost">
                        <div class="check-circle"><i class="ti ti-check"></i></div>
                        <div>
                            <span class="rzp-scheme-badge bg-success-subtle text-success border border-success-subtle">
                                <i class="ti ti-bolt me-1"></i>0% Subvention
                            </span>
                            <h6 class="fw-bold text-dark mb-1">No Cost EMI</h6>
                            <p class="text-muted small mb-0" style="font-size: 12px; line-height: 1.45;">
                                Zero interest to buyer. Subvention discount is subsidized by platform or merchant.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card B: Low Cost EMI --}}
                <div class="col-md-4">
                    <div class="rzp-scheme-card @if($emiType === 'low_cost') active @endif" data-scheme="low_cost">
                        <div class="check-circle"><i class="ti ti-check"></i></div>
                        <div>
                            <span class="rzp-scheme-badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                <i class="ti ti-trending-down me-1"></i>Subsidized
                            </span>
                            <h6 class="fw-bold text-dark mb-1">Low Cost EMI</h6>
                            <p class="text-muted small mb-0" style="font-size: 12px; line-height: 1.45;">
                                Reduced interest rate (7% p.a.). Buyer gets lower monthly installments.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card C: Standard EMI --}}
                <div class="col-md-4">
                    <div class="rzp-scheme-card @if($emiType === 'standard') active @endif" data-scheme="standard">
                        <div class="check-circle"><i class="ti ti-check"></i></div>
                        <div>
                            <span class="rzp-scheme-badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                <i class="ti ti-credit-card me-1"></i>Bank Standard
                            </span>
                            <h6 class="fw-bold text-dark mb-1">Standard Bank EMI</h6>
                            <p class="text-muted small mb-0" style="font-size: 12px; line-height: 1.45;">
                                Buyer pays standard bank card interest (13–15% p.a.). Zero cost to merchant.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Subvention Payer Row (Smoothly visible when No Cost or Low Cost) --}}
        <div id="rzp_subvention_row" class="card border mb-4 shadow-none bg-white" style="display: {{ in_array($emiType, ['no_cost', 'low_cost']) ? 'block' : 'none' }};">
            <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="rzp-meta-title">
                        <i class="ti ti-shield-dollar text-primary"></i>
                        <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.subvention_label') }}</span>
                    </div>
                    <div class="rzp-meta-subtitle">
                        {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.subvention_help') }}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="razorpay_emi_subvention_payer" id="subvention_platform" value="platform" @checked($subvention === 'platform')>
                        <label class="form-check-label fw-semibold small cursor-pointer" for="subvention_platform">
                            <i class="ti ti-building text-primary me-1"></i>Platform / Store Owner
                        </label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="razorpay_emi_subvention_payer" id="subvention_seller" value="seller" @checked($subvention === 'seller')>
                        <label class="form-check-label fw-semibold small cursor-pointer" for="subvention_seller">
                            <i class="ti ti-user-check text-success me-1"></i>Product Seller / Vendor
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Promotional Badge & Minimum Amount Parameters --}}
        <div class="row g-3 mb-4">
            {{-- Custom Badge --}}
            <div class="col-md-6">
                <div class="form-group mb-0">
                    <label for="razorpay_emi_custom_badge" class="form-label fw-bold d-flex justify-content-between align-items-center mb-1">
                        <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.badge_label') }}</span>
                        <span id="rzp_badge_live_preview" class="badge bg-primary-subtle text-primary border" style="font-size: 11px;">
                            {{ $customBadge ?: 'Badge Preview' }}
                        </span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <svg class="icon icon-tabler icon-tabler-tag text-muted" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" />
                            </svg>
                        </span>
                        <input type="text" class="form-control" name="razorpay_emi_custom_badge" id="razorpay_emi_custom_badge" value="{{ $customBadge }}" placeholder="{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.badge_placeholder') }}">
                    </div>
                    <div class="form-text text-muted mt-1" style="font-size: 12px;">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.badge_help') }}</div>
                </div>
            </div>

            {{-- Custom Minimum Amount --}}
            <div class="col-md-6">
                <div class="form-group mb-0">
                    <label for="razorpay_emi_min_amount" class="form-label fw-bold mb-1">
                        <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.min_amount_label') }}</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white fw-bold">₹</span>
                        <input type="number" step="0.01" class="form-control" name="razorpay_emi_min_amount" id="razorpay_emi_min_amount" value="{{ $minAmount }}" placeholder="{{ setting('razorpay_easy_emi_min_amount', 2500) }}">
                    </div>
                    <div class="form-text text-muted mt-1" style="font-size: 12px;">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.min_amount_help') }}</div>
                </div>
            </div>
        </div>

        {{-- 5. Razorpay Offers & Bank Cashback Section --}}
        <div class="rzp-offers-panel">
            {{-- Panel Header --}}
            <div class="rzp-offers-panel-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <svg class="icon icon-tabler icon-tabler-discount-2 text-primary flex-shrink-0" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9 15l6 -6" />
                        <circle cx="9.5" cy="9.5" r=".5" fill="currentColor" />
                        <circle cx="14.5" cy="14.5" r=".5" fill="currentColor" />
                        <path d="M5 7.2a2.2 2.2 0 0 1 2.2 -2.2h1a2.2 2.2 0 0 0 1.55 -.64l.7 -.7a2.2 2.2 0 0 1 3.12 0l.7 .7a2.2 2.2 0 0 0 1.55 .64h1a2.2 2.2 0 0 1 2.2 2.2v1a2.2 2.2 0 0 0 .64 1.55l.7 .7a2.2 2.2 0 0 1 0 3.12l-.7 .7a2.2 2.2 0 0 0 -.64 1.55v1a2.2 2.2 0 0 1 -2.2 2.2h-1a2.2 2.2 0 0 0 -1.55 .64l-.7 .7a2.2 2.2 0 0 1 -3.12 0l-.7 -.7a2.2 2.2 0 0 0 -1.55 -.64h-1a2.2 2.2 0 0 1 -2.2 -2.2v-1a2.2 2.2 0 0 0 -.64 -1.55l-.7 -.7a2.2 2.2 0 0 1 0 -3.12l.7 -.7a2.2 2.2 0 0 0 .64 -1.55v-1z" />
                    </svg>
                    <div>
                        <div class="rzp-meta-title">Razorpay Offers & Bank Promotions</div>
                        <div class="rzp-meta-subtitle">Manage active cashback, subvention and payment offers for this product</div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- Hidden backing select for offer mode --}}
                    <select class="d-none" name="razorpay_emi_offer_mode" id="razorpay_emi_offer_mode">
                        <option value="auto" @selected($offerMode === 'auto')>auto</option>
                        <option value="custom" @selected($offerMode === 'custom')>custom</option>
                    </select>

                    {{-- Mode Toggle Buttons --}}
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary @if($offerMode === 'auto') active @endif" id="btn_mode_auto">
                            <i class="ti ti-sparkles me-1"></i>Automatic Mode
                        </button>
                        <button type="button" class="btn btn-outline-primary @if($offerMode === 'custom') active @endif" id="btn_mode_custom">
                            <i class="ti ti-list-check me-1"></i>Custom Selection
                        </button>
                    </div>

                    {{-- Sync Button --}}
                    <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" id="btn-sync-offers-product" data-url="{{ route('razorpay-easy-emi.settings.sync-offers') }}">
                        <i class="ti ti-refresh" id="product-sync-icon"></i>
                        <span id="product-sync-text">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.sync_offers_btn') }}</span>
                    </button>
                </div>
            </div>

            {{-- Auto Mode Notification Banner --}}
            <div id="rzp_auto_mode_banner" class="alert alert-light border-0 border-bottom mb-0 py-2 px-3 small d-flex align-items-center justify-content-between" style="display: {{ $offerMode === 'auto' ? 'flex' : 'none' }}; background: #f0fdf4;">
                <div class="d-flex align-items-center gap-2 text-success-emphasis">
                    <i class="ti ti-circle-check fs-5 text-success"></i>
                    <span><strong>Auto Mode Active:</strong> Razorpay natively evaluates and shows all eligible account offers for customer order total.</span>
                </div>
                <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none py-0" id="btn_switch_to_custom">
                    Switch to Curated Offers &rarr;
                </button>
            </div>

            {{-- Offers Toolbar (Global Master Checkbox & Counters) --}}
            <div class="p-3 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="master_checkbox_all">
                        <label class="form-check-label fw-bold small text-dark cursor-pointer ms-1" for="master_checkbox_all">
                            Select All Available Offers
                        </label>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" id="global_selected_pill">0 Selected</span>
                </div>

                <div>
                    <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none py-0 small" id="btn_clear_all_offers">
                        <i class="ti ti-trash me-1"></i>Clear All
                    </button>
                </div>
            </div>

            {{-- Category Nav Tabs (Eliminates horizontal cramping) --}}
            <div class="rzp-category-tabs">
                <button type="button" class="rzp-tab-btn active" data-target="tab_pane_bank_emi">
                    <i class="ti ti-credit-card text-primary"></i>
                    <span>Bank EMI Offers (<span id="emi_tab_count">{{ $emiOffers->count() }}</span>)</span>
                </button>
                <button type="button" class="rzp-tab-btn" data-target="tab_pane_other_offers">
                    <i class="ti ti-gift text-success"></i>
                    <span>Other Payment Offers (<span id="other_tab_count">{{ $otherOffers->count() }}</span>)</span>
                </button>
                <button type="button" class="rzp-tab-btn" data-target="tab_pane_all_offers">
                    <i class="ti ti-list text-secondary"></i>
                    <span>All Offers (<span id="all_tab_count">{{ $allOffers->count() }}</span>)</span>
                </button>
            </div>

            {{-- Tab Panes Content --}}
            <div class="p-3">
                @if ($allOffers->isEmpty())
                    <div class="alert alert-warning py-3 px-3 small mb-0 text-center">
                        <i class="ti ti-info-circle fs-4 d-block mb-1"></i>
                        {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.no_offers_found') }}
                    </div>
                @else
                    {{-- PANE 1: Bank EMI Offers (Full-width, zero cramping) --}}
                    <div class="rzp-tab-pane" id="tab_pane_bank_emi" style="display: block;">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div class="form-check mb-0">
                                <input class="form-check-input cursor-pointer master-category-checkbox" type="checkbox" id="master_checkbox_emi">
                                <label class="form-check-label fw-bold small text-dark cursor-pointer ms-1" for="master_checkbox_emi">
                                    Select All Bank EMI Offers
                                </label>
                            </div>
                            <span class="badge bg-light text-primary border" id="emi_count_pill">
                                <span id="emi_selected_count">0</span> of <span id="emi_total_count">{{ $emiOffers->count() }}</span> selected
                            </span>
                        </div>

                        <div class="rzp-offers-list" style="max-height: 420px; overflow-y: auto;">
                            @forelse ($emiOffers as $offer)
                                @php
                                    $isChecked = in_array($offer->id, $selectedOfferIds, true);
                                    $offerScheme = $offer->isNoCostEmi() ? 'no_cost' : ($offer->isLowCostEmi() ? 'low_cost' : 'cashback');
                                    $hasDifferentDesc = ! empty($offer->description) && trim(strtolower($offer->description)) !== trim(strtolower($offer->clean_title));
                                @endphp
                                <div class="rzp-offer-box emi-offer-item @if($isChecked) is-checked @endif" data-offer-scheme="{{ $offerScheme }}" data-offer-id="{{ $offer->id }}">
                                    <input class="form-check-input flex-shrink-0 mt-1 emi-offer-checkbox emi-type-checkbox cursor-pointer" type="checkbox" name="razorpay_emi_offer_ids[]" value="{{ $offer->id }}" data-offer-scheme="{{ $offerScheme }}" @checked($isChecked)>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-light text-dark border fw-bold">{{ $offer->bank_issuer }}</span>
                                            @if ($offer->isNoCostEmi())
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">⭐ No Cost EMI</span>
                                            @elseif ($offer->isLowCostEmi())
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">📉 Low Cost EMI</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">💳 Bank Cashback</span>
                                            @endif
                                            @if ($offer->min_order_amount > 0)
                                                <span class="badge bg-light text-secondary border">Min Order: ₹{{ number_format($offer->min_order_amount) }}</span>
                                            @else
                                                <span class="badge bg-light text-success border">No Min Spend</span>
                                            @endif
                                        </div>
                                        <div class="rzp-offer-title">{{ $offer->clean_title }}</div>
                                        @if ($hasDifferentDesc)
                                            <div class="rzp-offer-desc">{{ $offer->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 text-muted text-center small">No bank EMI offers cached.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- PANE 2: Other Payment Offers (Full-width, zero cramping) --}}
                    <div class="rzp-tab-pane" id="tab_pane_other_offers" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div class="form-check mb-0">
                                <input class="form-check-input cursor-pointer master-category-checkbox" type="checkbox" id="master_checkbox_other">
                                <label class="form-check-label fw-bold small text-dark cursor-pointer ms-1" for="master_checkbox_other">
                                    Select All Other Offers (Cards & UPI)
                                </label>
                            </div>
                            <span class="badge bg-light text-success border" id="other_count_pill">
                                <span id="other_selected_count">0</span> of <span id="other_total_count">{{ $otherOffers->count() }}</span> selected
                            </span>
                        </div>

                        <div class="rzp-offers-list" style="max-height: 420px; overflow-y: auto;">
                            @forelse ($otherOffers as $offer)
                                @php
                                    $isChecked = in_array($offer->id, $selectedOfferIds, true);
                                    $hasDifferentDesc = ! empty($offer->description) && trim(strtolower($offer->description)) !== trim(strtolower($offer->clean_title));
                                @endphp
                                <div class="rzp-offer-box non-emi-offer-item @if($isChecked) is-checked @endif" data-offer-scheme="other" data-offer-id="{{ $offer->id }}">
                                    <input class="form-check-input flex-shrink-0 mt-1 emi-offer-checkbox non-emi-checkbox cursor-pointer" type="checkbox" name="razorpay_emi_offer_ids[]" value="{{ $offer->id }}" data-offer-scheme="other" @checked($isChecked)>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-light text-dark border fw-bold">{{ $offer->bank_issuer }}</span>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">🎁 Special Offer</span>
                                            @if ($offer->min_order_amount > 0)
                                                <span class="badge bg-light text-secondary border">Min Order: ₹{{ number_format($offer->min_order_amount) }}</span>
                                            @else
                                                <span class="badge bg-light text-success border">No Min Spend</span>
                                            @endif
                                        </div>
                                        <div class="rzp-offer-title">{{ $offer->clean_title }}</div>
                                        @if ($hasDifferentDesc)
                                            <div class="rzp-offer-desc">{{ $offer->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 text-muted text-center small">No other payment offers cached.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- PANE 3: All Offers Combined --}}
                    <div class="rzp-tab-pane" id="tab_pane_all_offers" style="display: none;">
                        <div class="alert alert-light border py-2 px-3 small mb-3 text-muted">
                            <i class="ti ti-info-circle me-1"></i>Showing all active Razorpay offers across all categories.
                        </div>
                        <div class="rzp-offers-list" id="all_offers_mirror_container" style="max-height: 450px; overflow-y: auto;">
                            {{-- Mirrored dynamically or rendered via allOffers --}}
                            @foreach ($allOffers as $offer)
                                @php
                                    $isChecked = in_array($offer->id, $selectedOfferIds, true);
                                    $isEmi = $offer->isEmiOffer();
                                    $offerScheme = $offer->isNoCostEmi() ? 'no_cost' : ($offer->isLowCostEmi() ? 'low_cost' : ($isEmi ? 'cashback' : 'other'));
                                    $hasDifferentDesc = ! empty($offer->description) && trim(strtolower($offer->description)) !== trim(strtolower($offer->clean_title));
                                @endphp
                                <div class="rzp-offer-box all-pane-offer-item @if($isChecked) is-checked @endif" data-offer-scheme="{{ $offerScheme }}" data-offer-id="{{ $offer->id }}">
                                    <input class="form-check-input flex-shrink-0 mt-1 emi-offer-checkbox all-type-checkbox cursor-pointer" type="checkbox" data-target-id="{{ $offer->id }}" @checked($isChecked)>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-light text-dark border fw-bold">{{ $offer->bank_issuer }}</span>
                                            @if ($offer->isNoCostEmi())
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">⭐ No Cost EMI</span>
                                            @elseif ($offer->isLowCostEmi())
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">📉 Low Cost EMI</span>
                                            @elseif ($isEmi)
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">💳 Bank Cashback</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">🎁 Special Offer</span>
                                            @endif
                                            @if ($offer->min_order_amount > 0)
                                                <span class="badge bg-light text-secondary border">Min Order: ₹{{ number_format($offer->min_order_amount) }}</span>
                                            @else
                                                <span class="badge bg-light text-success border">No Min Spend</span>
                                            @endif
                                        </div>
                                        <div class="rzp-offer-title">{{ $offer->clean_title }}</div>
                                        @if ($hasDifferentDesc)
                                            <div class="rzp-offer-desc">{{ $offer->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div id="product-sync-feedback" class="mt-2 small fw-semibold text-success d-none"></div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        // Elements
        var statusSelect = document.getElementById('razorpay_emi_status');
        var statusToggleBtns = document.querySelectorAll('.rzp-status-toggle-btn');
        var statusBadge = document.getElementById('rzp_status_badge');
        var disabledNotice = document.getElementById('rzp_disabled_notice');
        var dependentContent = document.getElementById('rzp_dependent_content');

        var schemeSelect = document.getElementById('razorpay_emi_type');
        var schemeCards = document.querySelectorAll('.rzp-scheme-card');
        var subventionRow = document.getElementById('rzp_subvention_row');

        var customBadgeInput = document.getElementById('razorpay_emi_custom_badge');
        var customBadgePreview = document.getElementById('rzp_badge_live_preview');

        var offerModeSelect = document.getElementById('razorpay_emi_offer_mode');
        var btnModeAuto = document.getElementById('btn_mode_auto');
        var btnModeCustom = document.getElementById('btn_mode_custom');
        var btnSwitchCustom = document.getElementById('btn_switch_to_custom');
        var autoModeBanner = document.getElementById('rzp_auto_mode_banner');

        var masterCheckAll = document.getElementById('master_checkbox_all');
        var masterCheckEmi = document.getElementById('master_checkbox_emi');
        var masterCheckOther = document.getElementById('master_checkbox_other');
        var btnClearAll = document.getElementById('btn_clear_all_offers');
        var syncBtn = document.getElementById('btn-sync-offers-product');

        // Tab Navigation
        var tabButtons = document.querySelectorAll('.rzp-tab-btn');
        var tabPanes = document.querySelectorAll('.rzp-tab-pane');

        tabButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetId = this.getAttribute('data-target');
                tabButtons.forEach(function (b) { b.classList.remove('active'); });
                tabPanes.forEach(function (p) {
                    p.classList.remove('active', 'show');
                    p.style.display = 'none';
                });

                this.classList.add('active');
                var targetPane = document.getElementById(targetId);
                if (targetPane) {
                    targetPane.classList.add('active', 'show');
                    targetPane.style.display = 'block';
                }
            });
        });

        // 1. Status Switching (Store Default / Enable / Disable)
        function setStatus(val) {
            if (statusSelect) statusSelect.value = val;
            statusToggleBtns.forEach(function (btn) {
                if (btn.getAttribute('data-val') === val) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            if (val === 'disabled') {
                if (disabledNotice) disabledNotice.style.setProperty('display', 'flex', 'important');
                if (dependentContent) dependentContent.style.setProperty('display', 'none', 'important');
                if (statusBadge) {
                    statusBadge.className = 'badge bg-danger-subtle text-danger border ms-1 fs-7';
                    statusBadge.textContent = 'Disabled For Product';
                }
            } else if (val === 'enabled') {
                if (disabledNotice) disabledNotice.style.setProperty('display', 'none', 'important');
                if (dependentContent) dependentContent.style.setProperty('display', 'block', 'important');
                if (statusBadge) {
                    statusBadge.className = 'badge bg-success-subtle text-success border ms-1 fs-7';
                    statusBadge.textContent = 'Explicitly Enabled';
                }
            } else {
                if (disabledNotice) disabledNotice.style.setProperty('display', 'none', 'important');
                if (dependentContent) dependentContent.style.setProperty('display', 'block', 'important');
                if (statusBadge) {
                    statusBadge.className = 'badge bg-secondary-subtle text-secondary border ms-1 fs-7';
                    statusBadge.textContent = 'Inheriting Default';
                }
            }
        }

        statusToggleBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setStatus(this.getAttribute('data-val'));
            });
        });

        // 2. Scheme Selection (No Cost / Low Cost / Standard)
        function setScheme(schemeVal) {
            if (schemeSelect) schemeSelect.value = schemeVal;
            schemeCards.forEach(function (card) {
                if (card.getAttribute('data-scheme') === schemeVal) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });

            // Subvention row visibility
            if (subventionRow) {
                if (schemeVal === 'no_cost' || schemeVal === 'low_cost') {
                    subventionRow.style.setProperty('display', 'block', 'important');
                } else {
                    subventionRow.style.setProperty('display', 'none', 'important');
                }
            }

            applySchemeFilter();
        }

        schemeCards.forEach(function (card) {
            card.addEventListener('click', function () {
                setScheme(this.getAttribute('data-scheme'));
            });
        });

        // 3. Custom Badge Live Preview
        if (customBadgeInput && customBadgePreview) {
            customBadgeInput.addEventListener('input', function () {
                var text = this.value.trim();
                customBadgePreview.textContent = text || 'Badge Preview';
            });
        }

        // 4. Offer Mode Switching (Auto vs Custom)
        function setOfferMode(mode) {
            if (offerModeSelect) offerModeSelect.value = mode;

            if (mode === 'auto') {
                if (btnModeAuto) btnModeAuto.classList.add('active');
                if (btnModeCustom) btnModeCustom.classList.remove('active');
                if (autoModeBanner) autoModeBanner.style.setProperty('display', 'flex', 'important');
            } else {
                if (btnModeAuto) btnModeAuto.classList.remove('active');
                if (btnModeCustom) btnModeCustom.classList.add('active');
                if (autoModeBanner) autoModeBanner.style.setProperty('display', 'none', 'important');
            }
        }

        if (btnModeAuto) {
            btnModeAuto.addEventListener('click', function () {
                setOfferMode('auto');
            });
        }
        if (btnModeCustom) {
            btnModeCustom.addEventListener('click', function () {
                setOfferMode('custom');
            });
        }
        if (btnSwitchCustom) {
            btnSwitchCustom.addEventListener('click', function () {
                setOfferMode('custom');
            });
        }

        // 5. Offer Item Visual State Helper
        function updateOfferBoxState(box, isChecked) {
            if (isChecked) {
                box.classList.add('is-checked');
            } else {
                box.classList.remove('is-checked');
            }
        }

        // Synchronize mirroring checkbox in Pane 3 (All Offers)
        function syncMirrorCheckbox(offerId, isChecked) {
            var mirrorCb = document.querySelector('.all-type-checkbox[data-target-id="' + offerId + '"]');
            if (mirrorCb) {
                mirrorCb.checked = isChecked;
                var mirrorBox = mirrorCb.closest('.rzp-offer-box');
                if (mirrorBox) updateOfferBoxState(mirrorBox, isChecked);
            }
        }

        // 6. Master Checkbox States & Counter Calculation
        function updateMasterStates() {
            // Category 1: EMI
            var emiVisibleBoxes = Array.from(document.querySelectorAll('#tab_pane_bank_emi .emi-offer-item')).filter(function (b) {
                return b.style.display !== 'none';
            });
            var emiCheckedBoxes = emiVisibleBoxes.filter(function (b) {
                var cb = b.querySelector('.emi-type-checkbox');
                return cb && cb.checked;
            });

            if (masterCheckEmi) {
                if (emiVisibleBoxes.length > 0) {
                    if (emiCheckedBoxes.length === emiVisibleBoxes.length) {
                        masterCheckEmi.checked = true;
                        masterCheckEmi.indeterminate = false;
                    } else if (emiCheckedBoxes.length > 0) {
                        masterCheckEmi.checked = false;
                        masterCheckEmi.indeterminate = true;
                    } else {
                        masterCheckEmi.checked = false;
                        masterCheckEmi.indeterminate = false;
                    }
                } else {
                    masterCheckEmi.checked = false;
                    masterCheckEmi.indeterminate = false;
                }
            }

            var emiSelectedEl = document.getElementById('emi_selected_count');
            var emiTotalEl = document.getElementById('emi_total_count');
            var emiTabCountEl = document.getElementById('emi_tab_count');
            if (emiSelectedEl) emiSelectedEl.textContent = emiCheckedBoxes.length;
            if (emiTotalEl) emiTotalEl.textContent = emiVisibleBoxes.length;
            if (emiTabCountEl) emiTabCountEl.textContent = emiVisibleBoxes.length;

            // Category 2: Other
            var otherVisibleBoxes = Array.from(document.querySelectorAll('#tab_pane_other_offers .non-emi-offer-item')).filter(function (b) {
                return b.style.display !== 'none';
            });
            var otherCheckedBoxes = otherVisibleBoxes.filter(function (b) {
                var cb = b.querySelector('.non-emi-checkbox');
                return cb && cb.checked;
            });

            if (masterCheckOther) {
                if (otherVisibleBoxes.length > 0) {
                    if (otherCheckedBoxes.length === otherVisibleBoxes.length) {
                        masterCheckOther.checked = true;
                        masterCheckOther.indeterminate = false;
                    } else if (otherCheckedBoxes.length > 0) {
                        masterCheckOther.checked = false;
                        masterCheckOther.indeterminate = true;
                    } else {
                        masterCheckOther.checked = false;
                        masterCheckOther.indeterminate = false;
                    }
                } else {
                    masterCheckOther.checked = false;
                    masterCheckOther.indeterminate = false;
                }
            }

            var otherSelectedEl = document.getElementById('other_selected_count');
            var otherTotalEl = document.getElementById('other_total_count');
            var otherTabCountEl = document.getElementById('other_tab_count');
            if (otherSelectedEl) otherSelectedEl.textContent = otherCheckedBoxes.length;
            if (otherTotalEl) otherTotalEl.textContent = otherVisibleBoxes.length;
            if (otherTabCountEl) otherTabCountEl.textContent = otherVisibleBoxes.length;

            // Global Master Checkbox
            var totalVisible = emiVisibleBoxes.length + otherVisibleBoxes.length;
            var totalChecked = emiCheckedBoxes.length + otherCheckedBoxes.length;

            var allTabCountEl = document.getElementById('all_tab_count');
            if (allTabCountEl) allTabCountEl.textContent = totalVisible;

            if (masterCheckAll) {
                if (totalVisible > 0) {
                    if (totalChecked === totalVisible) {
                        masterCheckAll.checked = true;
                        masterCheckAll.indeterminate = false;
                    } else if (totalChecked > 0) {
                        masterCheckAll.checked = false;
                        masterCheckAll.indeterminate = true;
                    } else {
                        masterCheckAll.checked = false;
                        masterCheckAll.indeterminate = false;
                    }
                } else {
                    masterCheckAll.checked = false;
                    masterCheckAll.indeterminate = false;
                }
            }

            var globalPill = document.getElementById('global_selected_pill');
            if (globalPill) {
                globalPill.textContent = totalChecked + ' Selected';
                if (totalChecked > 0) {
                    globalPill.className = 'badge bg-primary text-white px-2 py-1';
                } else {
                    globalPill.className = 'badge bg-secondary-subtle text-secondary border px-2 py-1';
                }
            }
        }

        // 7. Click Master Category Checkbox (EMI)
        if (masterCheckEmi) {
            masterCheckEmi.addEventListener('change', function () {
                var isChecked = this.checked;
                document.querySelectorAll('#tab_pane_bank_emi .emi-offer-item').forEach(function (box) {
                    if (box.style.display !== 'none') {
                        var cb = box.querySelector('.emi-type-checkbox');
                        if (cb) {
                            cb.checked = isChecked;
                            updateOfferBoxState(box, isChecked);
                            syncMirrorCheckbox(box.getAttribute('data-offer-id'), isChecked);
                        }
                    }
                });
                if (isChecked) setOfferMode('custom');
                updateMasterStates();
            });
        }

        // 8. Click Master Category Checkbox (Other)
        if (masterCheckOther) {
            masterCheckOther.addEventListener('change', function () {
                var isChecked = this.checked;
                document.querySelectorAll('#tab_pane_other_offers .non-emi-offer-item').forEach(function (box) {
                    if (box.style.display !== 'none') {
                        var cb = box.querySelector('.non-emi-checkbox');
                        if (cb) {
                            cb.checked = isChecked;
                            updateOfferBoxState(box, isChecked);
                            syncMirrorCheckbox(box.getAttribute('data-offer-id'), isChecked);
                        }
                    }
                });
                if (isChecked) setOfferMode('custom');
                updateMasterStates();
            });
        }

        // 9. Click Global Master Checkbox
        if (masterCheckAll) {
            masterCheckAll.addEventListener('change', function () {
                var isChecked = this.checked;
                document.querySelectorAll('.rzp-offer-box:not(.all-pane-offer-item)').forEach(function (box) {
                    if (box.style.display !== 'none') {
                        var cb = box.querySelector('.emi-offer-checkbox');
                        if (cb) {
                            cb.checked = isChecked;
                            updateOfferBoxState(box, isChecked);
                            syncMirrorCheckbox(box.getAttribute('data-offer-id'), isChecked);
                        }
                    }
                });
                if (isChecked) setOfferMode('custom');
                updateMasterStates();
            });
        }

        // 10. Clear All
        if (btnClearAll) {
            btnClearAll.addEventListener('click', function () {
                document.querySelectorAll('.rzp-offer-box').forEach(function (box) {
                    var cb = box.querySelector('.emi-offer-checkbox');
                    if (cb) {
                        cb.checked = false;
                        updateOfferBoxState(box, false);
                    }
                });
                updateMasterStates();
            });
        }

        // 11. Individual Offer Card Click
        document.querySelectorAll('.rzp-offer-box').forEach(function (box) {
            box.addEventListener('click', function (e) {
                var cb = box.querySelector('.emi-offer-checkbox');
                if (!cb) return;

                var isAllPane = box.classList.contains('all-pane-offer-item');
                var offerId = box.getAttribute('data-offer-id');

                var newCheckedState;
                if (e.target.type === 'checkbox') {
                    newCheckedState = e.target.checked;
                } else {
                    newCheckedState = !cb.checked;
                    cb.checked = newCheckedState;
                }

                updateOfferBoxState(box, newCheckedState);

                // If clicked inside All Pane, sync the real form checkbox in category pane
                if (isAllPane) {
                    var realCb = document.querySelector('.emi-type-checkbox[value="' + offerId + '"], .non-emi-checkbox[value="' + offerId + '"]');
                    if (realCb) {
                        realCb.checked = newCheckedState;
                        var realBox = realCb.closest('.rzp-offer-box');
                        if (realBox) updateOfferBoxState(realBox, newCheckedState);
                    }
                } else {
                    syncMirrorCheckbox(offerId, newCheckedState);
                }

                if (newCheckedState) setOfferMode('custom');
                updateMasterStates();
            });
        });

        // 12. Scheme Filter Logic (No Cost vs Low Cost vs Standard)
        function applySchemeFilter() {
            var currentScheme = schemeSelect ? schemeSelect.value : 'standard';
            var emiItems = document.querySelectorAll('.emi-offer-item');
            var allPaneItems = document.querySelectorAll('.all-pane-offer-item');

            emiItems.forEach(function (item) {
                var offerScheme = item.getAttribute('data-offer-scheme');
                var cb = item.querySelector('.emi-type-checkbox');
                var shouldShow = true;

                if (currentScheme === 'standard') {
                    if (offerScheme === 'no_cost' || offerScheme === 'low_cost') {
                        shouldShow = false;
                    }
                } else if (currentScheme === 'no_cost') {
                    if (offerScheme === 'low_cost') {
                        shouldShow = false;
                    }
                } else if (currentScheme === 'low_cost') {
                    if (offerScheme === 'no_cost') {
                        shouldShow = false;
                    }
                }

                if (shouldShow) {
                    item.style.setProperty('display', 'flex', 'important');
                } else {
                    item.style.setProperty('display', 'none', 'important');
                    if (cb && cb.checked) {
                        cb.checked = false;
                        updateOfferBoxState(item, false);
                        syncMirrorCheckbox(item.getAttribute('data-offer-id'), false);
                    }
                }
            });

            // Also filter matching items in All Offers pane
            allPaneItems.forEach(function (item) {
                var offerScheme = item.getAttribute('data-offer-scheme');
                var shouldShow = true;
                if (currentScheme === 'standard') {
                    if (offerScheme === 'no_cost' || offerScheme === 'low_cost') shouldShow = false;
                } else if (currentScheme === 'no_cost') {
                    if (offerScheme === 'low_cost') shouldShow = false;
                } else if (currentScheme === 'low_cost') {
                    if (offerScheme === 'no_cost') shouldShow = false;
                }
                if (shouldShow) {
                    item.style.setProperty('display', 'flex', 'important');
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            });

            updateMasterStates();
        }

        // 13. Sync Offers AJAX
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
                        feedback.textContent = 'Network error while syncing offers.';
                        feedback.classList.remove('d-none');
                    }
                });
            });
        }

        // Initialize state
        setStatus('{{ $status }}');
        setScheme('{{ $emiType }}');
        setOfferMode('{{ $offerMode }}');
        updateMasterStates();
    })();
</script>
