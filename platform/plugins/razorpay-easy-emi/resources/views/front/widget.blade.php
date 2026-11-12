@php
    /** @var array $resolution */
@endphp

@if (! empty($resolution['eligible']))
    <div class="razorpay-easy-emi-container my-3"
         id="razorpay-easy-emi-box-{{ $resolution['product_id'] }}"
         data-product-id="{{ $resolution['product_id'] }}"
         data-key="{{ $resolution['key'] }}"
         data-amount="{{ $resolution['amount'] }}"
         data-price="{{ $resolution['price'] }}"
         data-currency="{{ $resolution['currency'] ?? 'INR' }}"
         data-scheme="{{ $resolution['scheme'] ?? 'standard' }}"
         data-offer-mode="{{ $resolution['offer_mode'] ?? 'auto' }}"
         data-offer-ids='@json($resolution['offer_ids'] ?? [])'
         data-min-amount="{{ $resolution['min_amount'] ?? 2500 }}"
    >
        {{-- Optional Custom Promotional Badge above Widget (if explicitly configured) --}}
        @if (! empty($resolution['custom_badge']))
            <div class="mb-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-7 fw-semibold">
                    <i class="ti ti-discount-2 me-1"></i> {{ $resolution['custom_badge'] }}
                </span>
            </div>
        @endif

        {{-- Razorpay Dual-Column Affordability Card (Image 1 Exact Design) --}}
        <div class="rzp-card-header">
            <span class="rzp-header-title">EMI plans and Offers</span>
            <div class="rzp-powered-by">
                <span>powered by</span>
                <svg viewBox="0 0 16 18" fill="none" class="rzp-logo-bolt">
                    <path d="M9.5 0L1 9.5H6.5L4 18L15 7H9.5L12 0H9.5Z" fill="#ffffff"/>
                </svg>
                <span class="rzp-logo-text">Razorpay</span>
            </div>
        </div>

        <div class="rzp-card-body">
            {{-- Column 1: EMI Plans --}}
            <div class="rzp-card-col rzp-col-left">
                <div class="rzp-col-title">
                    EMI plans starting from <span id="rzp-starting-emi-val">₹{{ number_format($resolution['starting_emi']) }}</span>/month
                </div>
                <div class="rzp-icons-row">
                    {{-- Zest / Cardless --}}
                    <div class="rzp-bank-icon-wrap" title="Cardless EMI / Zest">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="12" fill="#10B981"/><path d="M7 8h10l-6 8h6" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    {{-- HDFC Bank --}}
                    <div class="rzp-bank-icon-wrap" title="HDFC Bank">
                        <svg viewBox="0 0 24 24"><rect width="24" height="24" rx="3" fill="#004C8F"/><rect x="8" y="8" width="8" height="8" fill="#ED1C24"/><rect x="10" y="4" width="4" height="16" fill="#fff"/><rect x="4" y="10" width="16" height="4" fill="#fff"/><rect x="10" y="10" width="4" height="4" fill="#004C8F"/></svg>
                    </div>
                    {{-- ICICI Bank --}}
                    <div class="rzp-bank-icon-wrap" title="ICICI Bank">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="12" fill="#F37021"/><path d="M12 6a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-1.5 6h3v6h-3z" fill="#fff"/><path d="M7.5 12c0-2.5 2-4.5 4.5-4.5" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </div>
                    {{-- Axis Bank --}}
                    <div class="rzp-bank-icon-wrap" title="Axis Bank">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="12" fill="#97144D"/><path d="M12 6l5 12h-3.5l-3-7.5-1.5 3.5h3L11 16H8z" fill="#fff"/></svg>
                    </div>
                    <div class="rzp-more-pill">+7</div>
                </div>
                <button type="button" class="rzp-btn-view-plans" data-bs-toggle="modal" data-bs-target="#razorpay-emi-plans-modal">
                    <span>View plans</span>
                    <i class="ti ti-chevron-right fs-6"></i>
                </button>
            </div>

            {{-- Column 2: Savings With Offers --}}
            <div class="rzp-card-col rzp-col-right">
                <div class="rzp-col-title">
                    Savings of <span class="rzp-highlight-green" id="rzp-savings-val">₹{{ number_format($resolution['max_savings']) }}</span> with offers
                </div>
                <div class="rzp-icons-row">
                    {{-- HDFC Bank --}}
                    <div class="rzp-bank-icon-wrap" title="HDFC Bank">
                        <svg viewBox="0 0 24 24"><rect width="24" height="24" rx="3" fill="#004C8F"/><rect x="8" y="8" width="8" height="8" fill="#ED1C24"/><rect x="10" y="4" width="4" height="16" fill="#fff"/><rect x="4" y="10" width="16" height="4" fill="#fff"/><rect x="10" y="10" width="4" height="4" fill="#004C8F"/></svg>
                    </div>
                    {{-- ICICI Bank --}}
                    <div class="rzp-bank-icon-wrap" title="ICICI Bank">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="12" fill="#F37021"/><path d="M12 6a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-1.5 6h3v6h-3z" fill="#fff"/><path d="M7.5 12c0-2.5 2-4.5 4.5-4.5" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </div>
                    {{-- Axis Bank --}}
                    <div class="rzp-bank-icon-wrap" title="Axis Bank">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="12" fill="#97144D"/><path d="M12 6l5 12h-3.5l-3-7.5-1.5 3.5h3L11 16H8z" fill="#fff"/></svg>
                    </div>
                    <div class="rzp-more-pill">+2</div>
                </div>
                <button type="button" class="rzp-btn-view-offers" data-bs-toggle="modal" data-bs-target="#razorpay-offers-modal">
                    <span>View offers</span>
                    <i class="ti ti-chevron-right fs-6"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal 1: EMI Plans Modal --}}
    <div class="modal fade" id="razorpay-emi-plans-modal" tabindex="-1" aria-labelledby="razorpayEmiPlansModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-header rzp-modal-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary text-white rounded">
                            <i class="ti ti-credit-card"></i>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="razorpayEmiPlansModalLabel">EMI Payment Plans</h5>
                            <small class="text-muted">Available across 15+ participating banks & card issuers</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Highlight banner --}}
                    @if (($resolution['scheme'] ?? '') === 'no_cost')
                        <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-3 border-0 bg-success-subtle text-success-emphasis rounded-2">
                            <i class="ti ti-discount-check fs-3 me-2 flex-shrink-0"></i>
                            <div class="small">
                                <strong>⭐ No Cost EMI Available:</strong> The bank interest is subsidized as an upfront instant discount on eligible tenures. You pay 0% effective interest!
                            </div>
                        </div>
                    @elseif (($resolution['scheme'] ?? '') === 'low_cost')
                        <div class="alert alert-warning d-flex align-items-center py-2 px-3 mb-3 border-0 bg-warning-subtle text-warning-emphasis rounded-2">
                            <i class="ti ti-trending-down fs-3 me-2 flex-shrink-0"></i>
                            <div class="small">
                                <strong>📉 Low Cost EMI Available:</strong> Enjoy subsidized, reduced interest rates on select tenures with upfront merchant discounts. Pay significantly lower interest!
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info d-flex align-items-center py-2 px-3 mb-3 border-0 bg-info-subtle text-info-emphasis rounded-2">
                            <i class="ti ti-info-circle fs-3 me-2 flex-shrink-0"></i>
                            <div class="small">
                                <strong>Credit Card EMI:</strong> Convert your purchase of <strong>₹<span class="rzp-modal-price">{{ number_format($resolution['price']) }}</span></strong> into easy monthly installments.
                            </div>
                        </div>
                    @endif

                    {{-- Bank Selector Navigation Pills --}}
                    <div class="mb-3">
                        <ul class="nav nav-pills rzp-bank-nav-pills gap-2 flex-wrap" id="rzp-bank-tabs" role="tablist">
                            @foreach ($resolution['bank_plans'] ?? [] as $index => $bank)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                            id="tab-bank-{{ $bank['code'] }}"
                                            data-bs-toggle="pill"
                                            data-bs-target="#content-bank-{{ $bank['code'] }}"
                                            type="button" role="tab"
                                            aria-controls="content-bank-{{ $bank['code'] }}"
                                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                        {{ $bank['name'] }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Bank Plans Tables Tab Content --}}
                    <div class="tab-content border rounded-2 overflow-hidden" id="rzp-bank-tabs-content">
                        @foreach ($resolution['bank_plans'] ?? [] as $index => $bank)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                 id="content-bank-{{ $bank['code'] }}"
                                 role="tabpanel"
                                 aria-labelledby="tab-bank-{{ $bank['code'] }}">
                                <div class="table-responsive">
                                    <table class="table rzp-tenure-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Tenure</th>
                                                <th>Monthly Installment</th>
                                                <th>Interest Rate</th>
                                                <th>Total Cost</th>
                                                <th class="text-end">Savings</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bank['plans'] ?? [] as $plan)
                                                <tr>
                                                    <td class="fw-bold">{{ $plan['months'] }} Months</td>
                                                    <td class="fw-semibold text-primary">₹{{ number_format($plan['monthly_emi']) }}/mo</td>
                                                    <td>
                                                        @if (!empty($plan['is_no_cost']))
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                                ⭐ 0% No Cost EMI
                                                            </span>
                                                        @elseif (!empty($plan['is_low_cost']))
                                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                                                📉 Low Cost ({{ $plan['annual_rate'] }}% p.a.)
                                                            </span>
                                                        @else
                                                            <span class="text-muted">{{ $plan['annual_rate'] }}% p.a.</span>
                                                        @endif
                                                    </td>
                                                    <td class="fw-semibold">₹{{ number_format($plan['total']) }}</td>
                                                    <td class="text-end">
                                                        @if ($plan['savings'] > 0)
                                                            <span class="badge bg-success text-white">Save ₹{{ number_format($plan['savings']) }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-muted small mt-3">
                        <i class="ti ti-info-circle me-1"></i>
                        EMI approval and interest charges are governed by your issuing bank's policies. Select <strong>Razorpay</strong> at checkout to complete payment via EMI.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal 2: Available Offers Modal --}}
    <div class="modal fade" id="razorpay-offers-modal" tabindex="-1" aria-labelledby="razorpayOffersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-header rzp-modal-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-success text-white rounded">
                            <i class="ti ti-discount-2"></i>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="razorpayOffersModalLabel">Available Bank & Payment Offers</h5>
                            <small class="text-muted">Instant discounts, cashbacks & subvention offers</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                    {{-- Group 1: Bank EMI Offers --}}
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark text-uppercase small d-flex align-items-center gap-2 mb-3 pb-1 border-bottom">
                            <i class="ti ti-credit-card text-primary"></i>
                            Bank EMI Cashback & Discounts
                        </h6>
                        @if (! empty($resolution['emi_offers']))
                            <div class="row g-3">
                                @foreach ($resolution['emi_offers'] as $offer)
                                    <div class="col-md-6">
                                        <div class="rzp-offer-card h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                    <span class="badge bg-light text-dark border fw-semibold">{{ $offer->bank_issuer }}</span>
                                                    @if ($offer->isNoCostEmi())
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">⭐ No Cost EMI</span>
                                                    @elseif ($offer->isLowCostEmi())
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">📉 Low Cost</span>
                                                    @else
                                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">💳 Cashback</span>
                                                    @endif
                                                </div>
                                                <div class="fw-bold text-dark small mb-1">{{ $offer->clean_title }}</div>
                                                @if (! empty($offer->description) && trim(strtolower($offer->description)) !== trim(strtolower($offer->clean_title)))
                                                    <div class="text-muted small mb-2">{{ $offer->description }}</div>
                                                @else
                                                    <div class="text-muted small mb-2">{{ $offer->discount_summary }}</div>
                                                @endif
                                            </div>
                                            <div class="pt-2 border-top d-flex justify-content-between align-items-center small">
                                                @if ($offer->min_order_amount > 0)
                                                    <span class="text-secondary"><i class="ti ti-shopping-cart text-muted me-1"></i>Min: <strong>₹{{ number_format($offer->min_order_amount) }}</strong></span>
                                                @else
                                                    <span class="text-muted"><i class="ti ti-sparkles text-success me-1"></i>No Min Spend</span>
                                                @endif
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 10.5px;">
                                                    <i class="ti ti-bolt me-1"></i>Auto-applied at checkout
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted small p-3 bg-light rounded text-center">No specific bank EMI cashback offers active currently.</div>
                        @endif
                    </div>

                    {{-- Group 2: Other Payment Offers --}}
                    <div>
                        <h6 class="fw-bold text-dark text-uppercase small d-flex align-items-center gap-2 mb-3 pb-1 border-bottom">
                            <i class="ti ti-gift text-success"></i>
                            Other Payment & Card Offers (Non-EMI)
                        </h6>
                        @if (! empty($resolution['other_offers']))
                            <div class="row g-3">
                                @foreach ($resolution['other_offers'] as $offer)
                                    <div class="col-md-6">
                                        <div class="rzp-offer-card h-100 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                    <span class="badge bg-light text-dark border fw-semibold">{{ $offer->bank_issuer }}</span>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">🎁 Special Offer</span>
                                                </div>
                                                <div class="fw-bold text-dark small mb-1">{{ $offer->clean_title }}</div>
                                                @if (! empty($offer->description) && trim(strtolower($offer->description)) !== trim(strtolower($offer->clean_title)))
                                                    <div class="text-muted small mb-2">{{ $offer->description }}</div>
                                                @else
                                                    <div class="text-muted small mb-2">{{ $offer->discount_summary }}</div>
                                                @endif
                                            </div>
                                            <div class="pt-2 border-top d-flex justify-content-between align-items-center small">
                                                @if ($offer->min_order_amount > 0)
                                                    <span class="text-secondary"><i class="ti ti-shopping-cart text-muted me-1"></i>Min: <strong>₹{{ number_format($offer->min_order_amount) }}</strong></span>
                                                @else
                                                    <span class="text-muted"><i class="ti ti-sparkles text-success me-1"></i>No Min Spend</span>
                                                @endif
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 10.5px;">
                                                    <i class="ti ti-tag me-1"></i>Avail in payment window
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted small p-3 bg-light rounded text-center">No other payment offers active currently.</div>
                        @endif
                    </div>

                    <div class="text-muted small mt-4 pt-2 border-top">
                        <i class="ti ti-check-circle text-success me-1"></i>
                        Offers are auto-applied at checkout when eligible card or payment method is used in the Razorpay payment window.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
