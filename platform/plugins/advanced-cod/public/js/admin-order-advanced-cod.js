/**
 * Advanced COD - Admin Order Creation Extension
 * Enhances Botble <create-order> Vue component when "Manual COD advance receipt" is selected,
 * displays rich product metadata in search dropdown, and enforces COD eligibility & cash collection fee.
 */
(function () {
    'use strict';

    let config = Object.assign({
        percentage: 38,
        fee_percentage: 4,
        fee_type: 'percentage',
        fee_fixed: 0,
        currency_symbol: '₹',
        currency_title: 'INR',
        admin_full_cod_override: false
    }, window.advancedCodConfig || {});

    let isUserEditingAdvance = false;
    window.__adminProductSearchCache = window.__adminProductSearchCache || {};

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

    function calculateCodFee(baseAmount) {
        if (!baseAmount || baseAmount <= 0) return 0;
        const pct = typeof config.fee_percentage === 'number' ? config.fee_percentage : 4;
        const fixed = typeof config.fee_fixed === 'number' ? config.fee_fixed : 0;
        if (config.fee_type === 'percentage') {
            return Math.round((baseAmount * (pct / 100) + fixed) * 100) / 100;
        }
        return Math.max(0, fixed);
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
                        <!-- Fee and Total Breakdown -->
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light text-center">
                                    <span class="text-muted d-block small" style="font-size: 0.72rem;">Order Base Amount</span>
                                    <span class="fw-semibold text-dark" id="adv-cod-display-base">₹ 0.00</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light text-center">
                                    <span class="text-muted d-block small" style="font-size: 0.72rem;">Cash Collection Fee (<span id="adv-cod-fee-pct">${config.fee_percentage}%</span>)</span>
                                    <span class="fw-semibold text-primary" id="adv-cod-display-fee">+ ₹ 0.00</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light text-center">
                                    <span class="text-muted d-block small" style="font-size: 0.72rem;">Total With Fee</span>
                                    <span class="fw-bold text-success" id="adv-cod-display-total">₹ 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Split Cards -->
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
        // Cash collection is in whole rupees (multiples of 100 paise)
        const cashRemaining = Math.min(Math.floor(total), Math.round(total * (1 - pct / 100)));
        const advance = Math.max(0, Math.round((total - cashRemaining) * 100) / 100);
        return { advance, remaining: cashRemaining };
    }

    function updateCalculations(customAdvance = null) {
        const baseTotal = getOrderTotal();
        const fee = calculateCodFee(baseTotal);
        const totalWithFee = Math.round((baseTotal + fee) * 100) / 100;

        const advanceInput = document.getElementById('adv-cod-input-advance');
        const displayBase = document.getElementById('adv-cod-display-base');
        const displayFee = document.getElementById('adv-cod-display-fee');
        const displayTotal = document.getElementById('adv-cod-display-total');
        const displayAdvance = document.getElementById('adv-cod-display-advance');
        const displayRemaining = document.getElementById('adv-cod-display-remaining');
        const badge = document.getElementById('adv-cod-badge');
        const hint = document.getElementById('adv-cod-calc-hint');
        const currencyLabel = document.getElementById('adv-cod-currency-label');
        const paiseAlert = document.getElementById('adv-cod-paise-alert');
        const paiseSpan = document.getElementById('adv-cod-paise-amount');
        const roundBtn = document.getElementById('adv-cod-round-cash-btn');
        const feePctSpan = document.getElementById('adv-cod-fee-pct');

        if (!advanceInput || !displayAdvance || !displayRemaining) return;

        if (currencyLabel) currencyLabel.textContent = config.currency_symbol || '₹';
        if (badge) badge.textContent = config.percentage + '% Advance';
        if (feePctSpan) feePctSpan.textContent = (config.fee_percentage || 4) + '%';
        const resetLink = document.getElementById('adv-cod-reset-advance');
        if (resetLink) resetLink.textContent = 'Reset to ' + config.percentage + '%';

        if (displayBase) displayBase.textContent = formatMoney(baseTotal);
        if (displayFee) displayFee.textContent = '+ ' + formatMoney(fee);
        if (displayTotal) displayTotal.textContent = formatMoney(totalWithFee);

        let advance = 0;
        let remaining = 0;

        if (customAdvance !== null) {
            advance = parseFloat(customAdvance) || 0;
            remaining = Math.max(0, Math.round((totalWithFee - advance) * 100) / 100);
        } else if (isUserEditingAdvance && advanceInput.value !== '') {
            advance = parseFloat(advanceInput.value) || 0;
            remaining = Math.max(0, Math.round((totalWithFee - advance) * 100) / 100);
        } else {
            const split = splitCodAmounts(totalWithFee, config.percentage);
            advance = split.advance;
            remaining = split.remaining;
            advanceInput.value = advance > 0 ? advance.toFixed(2) : '';
        }

        displayAdvance.textContent = formatMoney(advance);
        displayRemaining.textContent = formatMoney(remaining);

        // Check if remaining cash collection has fractional paise
        const hasPaise = Math.round(remaining * 100) % 100 !== 0;
        if (paiseAlert) {
            if (hasPaise && totalWithFee > 0) {
                paiseAlert.classList.remove('d-none');
                const roundedCash = Math.round(remaining);
                const adjustedAdvance = Math.round((totalWithFee - roundedCash) * 100) / 100;
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
            hint.textContent = `Base: ${formatMoney(baseTotal)} | Fee (4%): ${formatMoney(fee)} | Total: ${formatMoney(totalWithFee)} | Advance: ${formatMoney(advance)} | Courier Cash: ${formatMoney(remaining)}${hasPaise ? ' (Has fractional paise)' : ' (Whole rupee)'}`;
        }
    }

    function syncReferenceToTransactionId() {
        const refInput = document.getElementById('adv-cod-reference');
        if (!refInput) return;

        const val = refInput.value.trim();
        const transactionInput = document.querySelector('input[v-model="child_transaction_id"]')
            || document.querySelector('.card-body table tr:nth-last-child(1) input[type="text"]')
            || document.getElementById('child_transaction_id');

        if (transactionInput && transactionInput !== refInput) {
            transactionInput.value = val;
            transactionInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    // COD Eligibility Validator for selected products in order create
    function checkSelectedProductsCodEligibility() {
        const paymentMethodSelect = document.getElementById('payment-method');
        if (!paymentMethodSelect) return;

        const manualCodOption = paymentMethodSelect.querySelector('option[value="advanced_cod_manual"]');
        if (!manualCodOption) return;

        // Inspect table rows of selected products
        const productRows = document.querySelectorAll('.card-body table tbody tr');
        let allEligible = true;
        let hasProducts = false;

        productRows.forEach(row => {
            const rowText = row.textContent || '';
            // Ignore subtotal/shipping/total summary rows
            if (row.querySelector('input[type="number"]') || row.querySelector('img.avatar, .avatar')) {
                hasProducts = true;
                const rowKey = row.getAttribute('data-product-id');
                if (rowKey && window.__adminProductSearchCache[rowKey]) {
                    const cached = window.__adminProductSearchCache[rowKey];
                    if (cached.flags && cached.flags.cod === false) {
                        allEligible = false;
                    }
                }
            }
        });

        if (hasProducts && !allEligible) {
            manualCodOption.disabled = true;
            manualCodOption.textContent = 'Manual COD advance receipt (Disabled — contains non-COD item)';

            if (paymentMethodSelect.value === 'advanced_cod_manual') {
                const altOption = paymentMethodSelect.querySelector('option:not([value="advanced_cod_manual"]):not([disabled])');
                if (altOption) {
                    paymentMethodSelect.value = altOption.value;
                    paymentMethodSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    if (window.Botble && window.Botble.showWarning) {
                        window.Botble.showWarning('Advanced COD is not available because one or more selected items do not support COD.');
                    }
                }
            }
        } else {
            manualCodOption.disabled = false;
            manualCodOption.textContent = 'Manual COD advance receipt';
        }
    }

    // Decorate search results with clean badges
    function decorateProductSearchResults() {
        const searchItems = document.querySelectorAll('.box-search-advance.product .list-group-item');
        if (!searchItems.length) return;

        searchItems.forEach(itemEl => {
            if (itemEl.querySelector('.adv-product-meta-row')) return;

            // Find product name or image to match
            const titleEl = itemEl.querySelector('.col.text-truncate');
            if (!titleEl) return;

            const nameSpan = titleEl.querySelector('span:not(.text-info):not(.text-danger):not(.text-warning)');
            const productName = nameSpan ? nameSpan.textContent.trim() : '';

            // Match from cached search responses
            let matchedProduct = null;
            Object.values(window.__adminProductSearchCache).forEach(p => {
                if (p.name && (productName.includes(p.name) || p.name.includes(productName))) {
                    matchedProduct = p;
                }
            });

            if (matchedProduct) {
                const isCod = matchedProduct.cod ?? matchedProduct.flags?.cod;
                if (typeof isCod !== 'undefined') {
                    let metaRow = el.querySelector('.item-search-badges-row');
                    if (!metaRow) {
                        metaRow = document.createElement('div');
                        metaRow.className = 'item-search-badges-row d-flex align-items-center flex-wrap gap-1 mt-1';
                        titleEl.appendChild(metaRow);
                    }

                    if (!metaRow.querySelector('.cod-search-badge')) {
                        const badge = document.createElement('span');
                        badge.className = isCod ? 'badge bg-success-lt text-success cod-search-badge' : 'badge bg-danger-lt text-danger cod-search-badge';
                        badge.style.cssText = 'font-size: 10px; padding: 2px 5px;';
                        badge.innerHTML = isCod ? '<i class="ti ti-truck me-1"></i>COD Available' : '<i class="ti ti-ban me-1"></i>COD Not Eligible';
                        metaRow.appendChild(badge);
                    }
                }
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function init() {
        loadSettings();

        // Observe search panel to decorate items
        const searchContainer = document.querySelector('.box-search-advance.product');
        if (searchContainer) {
            const searchObserver = new MutationObserver(() => {
                decorateProductSearchResults();
            });
            searchObserver.observe(searchContainer, { childList: true, subtree: true });
        }

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

                        if (paymentStatusSelect && paymentStatusSelect.value !== 'completed') {
                            paymentStatusSelect.value = 'completed';
                            paymentStatusSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }

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

                // Observe table changes for COD eligibility and total recalculation
                let lastObservedTotal = getOrderTotal();
                setInterval(() => {
                    checkSelectedProductsCodEligibility();

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

    // Axios interceptors for request and response
    if (window.axios && window.axios.interceptors) {
        // Intercept search responses to cache enriched flags
        window.axios.interceptors.response.use(function (response) {
            try {
                const url = response.config?.url || '';
                if (url.includes('products/get-all-products-and-variations')) {
                    const items = response.data?.data?.data || response.data?.data || [];
                    if (Array.isArray(items)) {
                        items.forEach(item => {
                            if (item.id) {
                                window.__adminProductSearchCache[item.id] = item;
                            }
                        });
                        setTimeout(decorateProductSearchResults, 50);
                    }
                }
            } catch (e) {
                console.error('[AdvancedCOD] Response interceptor error', e);
            }
            return response;
        });

        // Intercept order create request to inject manual COD data
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
                            if (refInput) refInput.focus();
                            return Promise.reject(new Error(errorMsg));
                        }

                        let data = configReq.data;
                        if (typeof data === 'string') {
                            try { data = JSON.parse(data); } catch (e) {}
                        }

                        if (typeof data === 'object' && data !== null) {
                            const baseTotal = getOrderTotal();
                            const fee = calculateCodFee(baseTotal);
                            const totalWithFee = Math.round((baseTotal + fee) * 100) / 100;

                            data.payment_method = 'advanced_cod_manual';
                            data.payment_status = 'completed';
                            data.transaction_id = reference;
                            data.advanced_cod_manual = 1;
                            data.advanced_cod_fee = fee;
                            data.payment_fee = fee;
                            data.advanced_cod_total_with_fee = totalWithFee;
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
                console.error('[AdvancedCOD] Axios request interceptor error', e);
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
