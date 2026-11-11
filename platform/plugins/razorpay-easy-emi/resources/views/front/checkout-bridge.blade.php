@php
    /** @var float $cartTotal */
    /** @var float $minThreshold */
    /** @var bool $hasNoCostEmi */
    /** @var array $activeOfferIds */
    /** @var bool $isEligible */
@endphp

{{-- Hidden template for the Checkout EMI Card --}}
<div id="razorpay-checkout-emi-template" style="display: none;">
    <div class="razorpay-checkout-emi-card mt-3 p-3 rounded-3 border bg-light shadow-sm" style="border-left: 4px solid #0d6efd !important;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white px-2 py-1" style="font-size: 11px;">
                    <i class="ti ti-bolt"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.emi_available') }}
                </span>
                <strong class="text-dark" style="font-size: 13px;">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.checkout_emi_heading') }}</strong>
            </div>
            <span class="badge bg-white text-muted border px-2 py-1" style="font-size: 10px;">
                Credit Card EMI
            </span>
        </div>

        <p class="text-muted small mb-2" style="font-size: 11.5px; line-height: 1.4;">
            {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.checkout_emi_desc') }}
        </p>

        <div class="row g-2 mb-2 text-center">
            <div class="col-6">
                <div class="p-2 border rounded-2 bg-white text-start">
                    <div class="text-muted" style="font-size: 10px;">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.checkout_emi_starting') }}</div>
                    <div class="fw-bold text-success fs-6" id="checkout-emi-starting-amount">₹<span class="amount-val">{{ number_format(max(106, round(($cartTotal > 0 ? $cartTotal : 2550) / 24))) }}</span>/mo*</div>
                    <div class="text-muted" style="font-size: 10px;">3 to 24 months plans</div>
                </div>
            </div>
            <div class="col-6">
                <div class="p-2 border rounded-2 bg-white text-start">
                    <div class="text-muted" style="font-size: 10px;">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.checkout_bank_benefit') }}</div>
                    @if ($hasNoCostEmi)
                        <div class="fw-bold text-primary fs-6">⭐ No Cost EMI</div>
                        <div class="text-muted" style="font-size: 10px;">0% Interest Subsidized</div>
                    @else
                        <div class="fw-bold text-dark fs-6">💳 Bank Discounts</div>
                        <div class="text-muted" style="font-size: 10px;">Instant cashback on cards</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-2 rounded bg-white border text-muted small d-flex align-items-center justify-content-between" style="font-size: 11px;">
            <span><strong>Supported Banks:</strong> HDFC, ICICI, SBI, Axis, Kotak, Yes Bank, IDFC & 10+ more</span>
        </div>

        <div class="form-check mt-2 pt-2 border-top">
            <input class="form-check-input" type="checkbox" id="chk_pay_via_emi" checked>
            <label class="form-check-label fw-semibold text-dark small" for="chk_pay_via_emi" style="font-size: 12px; cursor: pointer;">
                {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.checkout_open_emi_directly') }}
            </label>
            <div class="text-muted" style="font-size: 10.5px;">
                {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.checkout_open_emi_help') }}
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        window.__razorpayActiveOffers = @json($activeOfferIds ?? []);
        window.__cartEmiEligible = {{ $isEligible ? 'true' : 'false' }};
        window.__cartMinThreshold = {{ (float) ($minThreshold ?? 2500) }};

        // Trap window.Razorpay instantiation so that even if checkout.js loads dynamically later,
        // we intercept and enhance the options before new Razorpay(options) runs.
        var _realRazorpay = window.Razorpay;

        function wrapRazorpayConstructor(Original) {
            if (Original && Original.__isEmiWrapped) {
                return Original;
            }

            var EnhancedRazorpay = function (options) {
                try {
                    var chkEmi = document.getElementById('chk_pay_via_emi');
                    var wantsEmi = (chkEmi ? chkEmi.checked : true)
                        || sessionStorage.getItem('razorpay_selected_emi') === 'true'
                        || window.__cartEmiEligible === true;

                    if (wantsEmi && options) {
                        // 1. Pre-select EMI method directly in Razorpay Checkout modal
                        options.prefill = options.prefill || {};
                        options.prefill.method = 'emi';

                        // 2. Elevate EMI Plans block to the very top of the sequence
                        options.config = options.config || {};
                        options.config.display = options.config.display || {};
                        options.config.display.blocks = options.config.display.blocks || {};

                        options.config.display.blocks.emi_plans = {
                            name: '{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.emi_payment_block') }}',
                            instruments: [
                                { method: 'emi' },
                                { method: 'cardless_emi' }
                            ]
                        };

                        var existingSequence = options.config.display.sequence || ['block.utib', 'block.other'];
                        var filteredSeq = existingSequence.filter(function (s) { return s !== 'block.emi_plans'; });
                        options.config.display.sequence = ['block.emi_plans'].concat(filteredSeq);

                        options.config.display.preferences = options.config.display.preferences || {};
                        options.config.display.preferences.show_default_blocks = true;

                        // 3. Inject active offers to unlock No Cost EMI / Bank Cashback banners
                        var appliedOffer = sessionStorage.getItem('razorpay_applied_offer');
                        var activeOffers = window.__razorpayActiveOffers || [];

                        options.offers = options.offers || [];
                        if (appliedOffer && options.offers.indexOf(appliedOffer) === -1) {
                            options.offers.push(appliedOffer);
                        }
                        activeOffers.forEach(function (offId) {
                            if (options.offers.indexOf(offId) === -1) {
                                options.offers.push(offId);
                            }
                        });
                    }
                } catch (err) {
                    console.warn('[Razorpay Easy EMI] Bridge options enhancement error:', err);
                }

                return new Original(options);
            };

            EnhancedRazorpay.prototype = Original.prototype;
            EnhancedRazorpay.__isEmiWrapped = true;
            return EnhancedRazorpay;
        }

        if (Object.defineProperty) {
            try {
                Object.defineProperty(window, 'Razorpay', {
                    configurable: true,
                    enumerable: true,
                    get: function () {
                        return _realRazorpay;
                    },
                    set: function (newVal) {
                        _realRazorpay = wrapRazorpayConstructor(newVal);
                    }
                });
            } catch (e) {
                // Fallback if property is not configurable
                if (window.Razorpay) {
                    window.Razorpay = wrapRazorpayConstructor(window.Razorpay);
                }
            }
        }

        // Dock the interactive EMI card under the Razorpay payment method
        function dockCheckoutEmiCard() {
            var rzpRadio = document.querySelector('input[name="payment_method"][value="razorpay"]');
            if (!rzpRadio) return;

            var rzpContainer = rzpRadio.closest('.payment-method-item') || rzpRadio.closest('.list-group-item');
            if (!rzpContainer) return;

            // Check if already docked
            if (rzpContainer.querySelector('.razorpay-checkout-emi-card')) {
                return;
            }

            var template = document.getElementById('razorpay-checkout-emi-template');
            if (!template) return;

            var clone = template.firstElementChild.cloneNode(true);

            // Find collapse wrap or append directly
            var collapseWrap = rzpContainer.querySelector('.payment_collapse_wrap');
            if (collapseWrap) {
                collapseWrap.appendChild(clone);
            } else {
                rzpContainer.appendChild(clone);
            }

            // Sync with session intent
            var isEmiIntent = sessionStorage.getItem('razorpay_selected_emi') === 'true';
            if (isEmiIntent && !rzpRadio.checked) {
                rzpRadio.checked = true;
                rzpRadio.dispatchEvent(new Event('change', { bubbles: true }));
            }

            var chk = clone.querySelector('#chk_pay_via_emi');
            if (chk) {
                chk.addEventListener('change', function () {
                    if (this.checked && !rzpRadio.checked) {
                        rzpRadio.checked = true;
                        rzpRadio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            }
        }

        // Run docking on DOM ready and watch for dynamic reloads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', dockCheckoutEmiCard);
        } else {
            dockCheckoutEmiCard();
        }

        var observer = new MutationObserver(function () {
            dockCheckoutEmiCard();
            if (window.Razorpay && !window.Razorpay.__isEmiWrapped) {
                window.Razorpay = wrapRazorpayConstructor(window.Razorpay);
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    })();
</script>
