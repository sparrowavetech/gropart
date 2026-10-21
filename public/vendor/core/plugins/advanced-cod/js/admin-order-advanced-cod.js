/**
 * Advanced COD - Admin Order Creation Extension
 * Enhances Botble <create-order> Vue component when "Manual COD advance receipt" is selected.
 */
(function () {
    'use strict';

    let config = Object.assign({
        percentage: 38,
        currency_symbol: '₹',
        currency_title: 'INR',
        admin_full_cod_override: false
    }, window.advancedCodConfig || {});

    let isUserEditingAdvance = false;

    // Fetch dynamic settings from server or read from preloaded config
    function loadSettings() {
        if (window.advancedCodConfig && typeof window.advancedCodConfig.percentage === 'number') {
            config = Object.assign(config, window.advancedCodConfig);
            updateCalculations();
            return;
        }

        const settingsUrl = '/admin/advanced-cod/settings';
        fetch(settingsUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
            if (data && typeof data.percentage === 'number') {
                config = Object.assign(config, data);
                updateCalculations();
            }
        })
        .catch(err => {
            console.warn('[AdvancedCOD] Could not fetch settings, using defaults', err);
        });
    }

    function parseAmount(text) {
        if (!text) return 0;
        // Clean currency symbols and commas: "₹ 1,500.50" -> 1500.50
        const cleaned = String(text).replace(/[^0-9.-]+/g, '');
        const val = parseFloat(cleaned);
        return isNaN(val) ? 0 : val;
    }

    function formatMoney(amount) {
        return (config.currency_symbol || '₹') + ' ' + (Number(amount) || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getOrderTotal() {
        // Find total label in summary table
        const totalHeader = document.querySelector('.card-body table tr:last-child h4.d-inline-block, table tr h4.d-inline-block');
        if (totalHeader) {
            return parseAmount(totalHeader.textContent);
        }
        return 0;
    }

    function createPanel() {
        if (document.getElementById('advanced-cod-manual-panel')) {
            return document.getElementById('advanced-cod-manual-panel');
        }

        const now = new Date();
        const localIsoDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);

        const tr = document.createElement('tr');
        tr.id = 'advanced-cod-manual-panel';
        tr.style.display = 'none';
        tr.innerHTML = `
            <td colspan="2" class="p-0">
                <div class="card border border-primary-subtle shadow-sm my-3 bg-white" style="border-radius: 8px; overflow: hidden;">
                    <div class="card-header bg-primary bg-opacity-10 py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-primary small d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            Advanced COD — Manual Advance Receipt
                        </span>
                        <span class="badge bg-primary" id="adv-cod-badge">${config.percentage}% Advance</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2 mb-3 text-center">
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <span class="text-muted d-block small" style="font-size: 0.75rem;">Advance Amount Paid</span>
                                    <span class="fw-bold text-success fs-6" id="adv-cod-display-advance">₹ 0.00</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <span class="text-muted d-block small" style="font-size: 0.75rem;">Courier Collection (COD)</span>
                                    <span class="fw-bold text-warning-emphasis fs-6" id="adv-cod-display-remaining">₹ 0.00</span>
                                </div>
                            </div>
                        </div>

                        <div id="adv-cod-paise-alert" class="alert alert-warning py-1 px-2 mb-2 d-none" style="font-size: 0.75rem;">
                            <span>⚠️ Courier cash has paise (<span id="adv-cod-paise-amount"></span>). Couriers require whole rupees.</span>
                            <a href="javascript:void(0)" class="fw-bold d-inline-block ms-1 text-decoration-underline" id="adv-cod-round-cash-btn">Round to whole cash</a>
                        </div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0" for="adv-cod-input-advance">
                                    Advance Amount Received (<span id="adv-cod-currency-label">${config.currency_symbol}</span>) <span class="text-danger">*</span>
                                </label>
                                <a href="javascript:void(0)" class="small text-decoration-none" id="adv-cod-reset-advance" style="font-size: 0.75rem;">Reset to ${config.percentage}%</a>
                            </div>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="adv-cod-input-advance" placeholder="0.00">
                            <div class="form-text small text-muted" id="adv-cod-calc-hint" style="font-size: 0.75rem;">
                                Courier cash collection is rounded to whole rupees; any fractional paise are absorbed in advance.
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1" for="adv-cod-channel">
                                    Payment Mode <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="adv-cod-channel">
                                    <option value="upi">UPI (GPay / PhonePe / Paytm / QR)</option>
                                    <option value="bank">Bank Transfer (NEFT / IMPS / RTGS)</option>
                                    <option value="cash">Cash / Counter Receipt</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1" for="adv-cod-date">
                                    Receipt Date & Time
                                </label>
                                <input type="datetime-local" class="form-control form-control-sm" id="adv-cod-date" value="${localIsoDate}">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold mb-1" for="adv-cod-reference">
                                Payment Reference / UTR Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="adv-cod-reference" placeholder="e.g. 12-digit UPI Ref / Bank UTR / Receipt No." maxlength="120">
                            <div class="form-text small text-muted" style="font-size: 0.75rem;">
                                Enter transaction UTR / bank reference for accounting & settlement audit trail.
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small mb-1" for="adv-cod-note">
                                Admin Remarks (Optional)
                            </label>
                            <input type="text" class="form-control form-control-sm" id="adv-cod-note" placeholder="Any internal notes regarding this advance receipt...">
                        </div>
                    </div>
                </div>
            </td>
        `;

        return tr;
    }

    function splitCodAmounts(total, pct) {
        if (!total || total <= 0) return { advance: 0, remaining: 0 };
        // Exactly matches CodAmounts::split: Cash collection is in whole rupees (multiples of 100 paise)
        const cashRemaining = Math.min(Math.floor(total), Math.round(total * (1 - pct / 100)));
        const advance = Math.max(0, Math.round((total - cashRemaining) * 100) / 100);
        return { advance, remaining: cashRemaining };
    }

    function updateCalculations(customAdvance = null) {
        const total = getOrderTotal();
        const advanceInput = document.getElementById('adv-cod-input-advance');
        const displayAdvance = document.getElementById('adv-cod-display-advance');
        const displayRemaining = document.getElementById('adv-cod-display-remaining');
        const badge = document.getElementById('adv-cod-badge');
        const hint = document.getElementById('adv-cod-calc-hint');
        const currencyLabel = document.getElementById('adv-cod-currency-label');
        const paiseAlert = document.getElementById('adv-cod-paise-alert');
        const paiseSpan = document.getElementById('adv-cod-paise-amount');
        const roundBtn = document.getElementById('adv-cod-round-cash-btn');

        if (!advanceInput || !displayAdvance || !displayRemaining) return;

        if (currencyLabel) currencyLabel.textContent = config.currency_symbol || '₹';
        if (badge) badge.textContent = config.percentage + '% Advance';
        const resetLink = document.getElementById('adv-cod-reset-advance');
        if (resetLink) resetLink.textContent = 'Reset to ' + config.percentage + '%';

        let advance = 0;
        let remaining = 0;

        if (customAdvance !== null) {
            advance = parseFloat(customAdvance) || 0;
            remaining = Math.max(0, Math.round((total - advance) * 100) / 100);
        } else if (isUserEditingAdvance && advanceInput.value !== '') {
            advance = parseFloat(advanceInput.value) || 0;
            remaining = Math.max(0, Math.round((total - advance) * 100) / 100);
        } else {
            const split = splitCodAmounts(total, config.percentage);
            advance = split.advance;
            remaining = split.remaining;
            advanceInput.value = advance > 0 ? advance.toFixed(2) : '';
        }

        displayAdvance.textContent = formatMoney(advance);
        displayRemaining.textContent = formatMoney(remaining);

        // Check if remaining cash collection has fractional paise
        const hasPaise = Math.round(remaining * 100) % 100 !== 0;
        if (paiseAlert) {
            if (hasPaise && total > 0) {
                paiseAlert.classList.remove('d-none');
                const roundedCash = Math.round(remaining);
                const adjustedAdvance = Math.round((total - roundedCash) * 100) / 100;
                if (paiseSpan) paiseSpan.textContent = formatMoney(remaining);
                if (roundBtn) {
                    roundBtn.textContent = `Round cash to ${formatMoney(roundedCash)} (Advance: ${formatMoney(adjustedAdvance)})`;
                    roundBtn.onclick = function (e) {
                        e.preventDefault();
                        isUserEditingAdvance = true;
                        advanceInput.value = adjustedAdvance.toFixed(2);
                        updateCalculations(adjustedAdvance);
                    };
                }
            } else {
                paiseAlert.classList.add('d-none');
            }
        }

        if (hint) {
            hint.textContent = `Order Total: ${formatMoney(total)} | Advance: ${formatMoney(advance)} | Courier Cash: ${formatMoney(remaining)}${hasPaise ? ' (Has fractional paise)' : ' (Whole rupee)'}`;
        }
    }

    function syncReferenceToTransactionId() {
        const refInput = document.getElementById('adv-cod-reference');
        if (!refInput) return;

        const val = refInput.value.trim();
        // Locate Botble's native child_transaction_id input if present
        const transactionInput = document.querySelector('input[v-model="child_transaction_id"]')
            || document.querySelector('.card-body table tr:nth-last-child(1) input[type="text"]')
            || document.getElementById('child_transaction_id');

        if (transactionInput && transactionInput !== refInput) {
            transactionInput.value = val;
            transactionInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    function init() {
        loadSettings();

        const checkReady = setInterval(() => {
            const paymentMethodSelect = document.getElementById('payment-method');
            const paymentStatusSelect = document.getElementById('payment-status');

            if (paymentMethodSelect && paymentMethodSelect.closest('tr')) {
                clearInterval(checkReady);

                const panel = createPanel();
                const targetRow = paymentStatusSelect ? paymentStatusSelect.closest('tr') : paymentMethodSelect.closest('tr');
                if (targetRow && targetRow.parentNode && !document.getElementById('advanced-cod-manual-panel')) {
                    targetRow.parentNode.insertBefore(panel, targetRow.nextSibling);
                }

                // Bind advance input changes
                const advanceInput = document.getElementById('adv-cod-input-advance');
                if (advanceInput) {
                    advanceInput.addEventListener('input', (e) => {
                        isUserEditingAdvance = true;
                        updateCalculations(e.target.value);
                    });
                }

                // Bind reset advance link
                const resetLink = document.getElementById('adv-cod-reset-advance');
                if (resetLink) {
                    resetLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        isUserEditingAdvance = false;
                        updateCalculations();
                    });
                }

                // Bind reference sync
                const refInput = document.getElementById('adv-cod-reference');
                if (refInput) {
                    refInput.addEventListener('input', syncReferenceToTransactionId);
                }

                // Handler for payment method switch
                function handlePaymentMethodChange() {
                    const method = paymentMethodSelect.value;
                    const isManualCod = method === 'advanced_cod_manual';

                    if (isManualCod) {
                        panel.style.display = '';
                        isUserEditingAdvance = false;
                        updateCalculations();

                        // Automatically set status to completed because advance is received
                        if (paymentStatusSelect && paymentStatusSelect.value !== 'completed') {
                            paymentStatusSelect.value = 'completed';
                            paymentStatusSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        // Hide the redundant raw transaction id row if present
                        const rawTrxRow = targetRow.nextElementSibling?.nextElementSibling;
                        if (rawTrxRow && rawTrxRow.textContent.includes('Transaction ID')) {
                            rawTrxRow.style.display = 'none';
                        }
                    } else {
                        panel.style.display = 'none';
                        const rawTrxRow = targetRow.nextElementSibling?.nextElementSibling;
                        if (rawTrxRow && rawTrxRow.textContent.includes('Transaction ID')) {
                            rawTrxRow.style.display = '';
                        }
                    }
                }

                paymentMethodSelect.addEventListener('change', handlePaymentMethodChange);
                handlePaymentMethodChange();

                // Periodically observe order total changes in Vue DOM
                let lastObservedTotal = getOrderTotal();
                setInterval(() => {
                    if (paymentMethodSelect.value === 'advanced_cod_manual') {
                        const currentTotal = getOrderTotal();
                        if (currentTotal !== lastObservedTotal) {
                            lastObservedTotal = currentTotal;
                            if (!isUserEditingAdvance) {
                                updateCalculations();
                            }
                        }
                    }
                }, 800);
            }
        }, 200);

        setTimeout(() => clearInterval(checkReady), 15000);
    }

    // Axios interceptor to ensure payload has custom fields
    if (window.axios && window.axios.interceptors) {
        window.axios.interceptors.request.use(function (configReq) {
            try {
                const url = configReq.url || '';
                const isCreateRoute = (url.includes('orders/create') || url.includes('ecommerce/orders')) && configReq.method === 'post';

                if (isCreateRoute) {
                    const paymentMethodSelect = document.getElementById('payment-method');
                    if (paymentMethodSelect && paymentMethodSelect.value === 'advanced_cod_manual') {
                        const refInput = document.getElementById('adv-cod-reference');
                        const advInput = document.getElementById('adv-cod-input-advance');
                        const channelSelect = document.getElementById('adv-cod-channel');
                        const dateInput = document.getElementById('adv-cod-date');
                        const noteInput = document.getElementById('adv-cod-note');

                        const reference = refInput ? refInput.value.trim() : '';
                        if (!reference) {
                            const errorMsg = 'Please enter a valid Reference / UTR Number for Manual COD Advance Receipt.';
                            if (window.Botble && window.Botble.showError) {
                                window.Botble.showError(errorMsg);
                            } else {
                                alert(errorMsg);
                            }
                            if (refEl) refEl.focus();
                            return Promise.reject(new Error(errorMsg));
                        }

                        let data = configReq.data;
                        if (typeof data === 'string') {
                            try { data = JSON.parse(data); } catch (e) {}
                        }

                        if (typeof data === 'object' && data !== null) {
                            data.payment_method = 'advanced_cod_manual';
                            data.payment_status = 'completed';
                            data.transaction_id = reference;
                            data.advanced_cod_manual = 1;
                            data.advanced_cod_advance_amount = advInput ? parseFloat(advInput.value) || 0 : 0;
                            data.advanced_cod_channel = channelSelect ? channelSelect.value : 'cash';
                            data.advanced_cod_reference = reference;
                            data.advanced_cod_date = dateInput ? dateInput.value : '';
                            data.advanced_cod_note = noteInput ? noteInput.value : '';

                            configReq.data = data;
                        }
                    }
                }
            } catch (e) {
                console.error('[AdvancedCOD] Axios interceptor error', e);
            }
            return configReq;
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
