/**
 * Farmart Helper - Admin Order Create Enhancements
 * 1. Searchable active coupons selectbox & autocomplete for #add-discounts modal.
 * 2. International country code selector (+91 default) for customer address phone inputs.
 */
(function () {
    'use strict';

    const COUNTRY_CODES = [
        { code: '+91', flag: '🇮🇳', name: 'India (+91)', iso: 'IN' },
        { code: '+1', flag: '🇺🇸', name: 'USA/Canada (+1)', iso: 'US' },
        { code: '+44', flag: '🇬🇧', name: 'UK (+44)', iso: 'GB' },
        { code: '+971', flag: '🇦🇪', name: 'UAE (+971)', iso: 'AE' },
        { code: '+966', flag: '🇸🇦', name: 'Saudi Arabia (+966)', iso: 'SA' },
        { code: '+61', flag: '🇦🇺', name: 'Australia (+61)', iso: 'AU' },
        { code: '+65', flag: '🇸🇬', name: 'Singapore (+65)', iso: 'SG' },
        { code: '+880', flag: '🇧🇩', name: 'Bangladesh (+880)', iso: 'BD' },
        { code: '+977', flag: '🇳🇵', name: 'Nepal (+977)', iso: 'NP' },
        { code: '+974', flag: '🇶🇦', name: 'Qatar (+974)', iso: 'QA' },
        { code: '+968', flag: '🇴🇲', name: 'Oman (+968)', iso: 'OM' },
        { code: '+965', flag: '🇰🇼', name: 'Kuwait (+965)', iso: 'KW' },
    ];

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * 1. Enhance #add-discounts modal with active coupon codes
     */
    function enhanceDiscountModal() {
        const modal = document.getElementById('add-discounts');
        if (!modal) return;

        const couponInput = modal.querySelector('.coupon-code-input') || modal.querySelector('input[v-model*="coupon_code"]');
        if (!couponInput) return;

        const coupons = window.__activeDiscountCoupons || [];

        // Build HTML5 Datalist for autocomplete typing
        let datalist = document.getElementById('farmart-active-coupons-list');
        if (!datalist) {
            datalist = document.createElement('datalist');
            datalist.id = 'farmart-active-coupons-list';
            document.body.appendChild(datalist);
        }

        if (coupons.length > 0 && datalist.children.length === 0) {
            coupons.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.code;
                opt.textContent = c.label || c.title || c.code;
                datalist.appendChild(opt);
            });
        }

        couponInput.setAttribute('list', 'farmart-active-coupons-list');
        couponInput.setAttribute('placeholder', 'Type or select coupon code...');

        // Add a searchable / selectable dropdown right below the coupon input
        if (coupons.length > 0) {
            const container = couponInput.closest('.position-relative') || couponInput.parentNode;
            if (container && !container.querySelector('.farmart-coupon-selector-wrap')) {
                const wrap = document.createElement('div');
                wrap.className = 'farmart-coupon-selector-wrap mt-2';

                let optionsHtml = '<option value="">-- Or select an active coupon --</option>';
                coupons.forEach(c => {
                    optionsHtml += `<option value="${escapeHtml(c.code)}">${escapeHtml(c.label || c.title || c.code)}</option>`;
                });

                wrap.innerHTML = `
                    <label class="form-label text-muted small mb-1 d-flex align-items-center">
                        <i class="ti ti-ticket me-1 text-primary"></i> Available Coupons (${coupons.length})
                    </label>
                    <select class="form-select form-select-sm farmart-coupon-dropdown text-dark">
                        ${optionsHtml}
                    </select>
                `;

                container.appendChild(wrap);

                const selectEl = wrap.querySelector('select');
                selectEl.addEventListener('change', function () {
                    const selectedVal = this.value;
                    if (selectedVal) {
                        couponInput.value = selectedVal;
                        couponInput.dispatchEvent(new Event('input', { bubbles: true }));
                        couponInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });

                couponInput.addEventListener('input', function () {
                    const curVal = this.value.trim().toUpperCase();
                    const matching = coupons.find(c => c.code.toUpperCase() === curVal);
                    if (matching) {
                        selectEl.value = matching.code;
                    } else {
                        selectEl.value = '';
                    }
                });
            }
        }
    }

    function injectModalStyles() {
        if (document.getElementById('farmart-order-create-modal-styles')) return;
        const style = document.createElement('style');
        style.id = 'farmart-order-create-modal-styles';
        style.textContent = `
            #add-customer .modal-dialog,
            #edit-address .modal-dialog {
                max-width: 760px !important;
                width: 90% !important;
            }
            .farmart-phone-input-group {
                display: flex !important;
                flex-wrap: nowrap !important;
                width: 100% !important;
            }
            .farmart-phone-input-group .farmart-phone-country-select {
                width: 125px !important;
                min-width: 125px !important;
                max-width: 125px !important;
                flex: 0 0 125px !important;
                padding-left: 0.5rem !important;
                padding-right: 1.5rem !important;
                font-size: 0.875rem !important;
                cursor: pointer;
                border-top-right-radius: 0 !important;
                border-bottom-right-radius: 0 !important;
            }
            .farmart-phone-input-group input.form-control,
            .farmart-phone-input-group input {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                border-top-left-radius: 0 !important;
                border-bottom-left-radius: 0 !important;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * 2. Enhance phone fields with country code selector in modals
     */
    function enhancePhoneInputInModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
            dialog.classList.add('modal-lg');
            dialog.style.maxWidth = '760px';
            dialog.style.width = '90%';
        }

        let targetPhoneInput = modal.querySelector('input.customer-address-phone')
            || modal.querySelector('input[v-model*="phone"]')
            || modal.querySelector('input[name*="phone"]');

        if (!targetPhoneInput) {
            const phoneInputs = modal.querySelectorAll('input[type="text"], input[type="tel"]');
            phoneInputs.forEach(input => {
                if (targetPhoneInput) return;
                const formGroup = input.closest('.mb-3') || input.parentNode;
                if (formGroup) {
                    const label = formGroup.querySelector('label');
                    if (label && /phone|mobile|contact|फ़ोन|फोन|मोबाइल/i.test(label.textContent)) {
                        targetPhoneInput = input;
                    }
                }
            });
        }

        if (!targetPhoneInput || targetPhoneInput.hasAttribute('data-farmart-phone-enhanced')) return;

        // Mark as enhanced
        targetPhoneInput.setAttribute('data-farmart-phone-enhanced', '1');

        // Create input-group container
        const inputGroup = document.createElement('div');
        inputGroup.className = 'input-group farmart-phone-input-group';
        inputGroup.style.cssText = 'display: flex !important; flex-wrap: nowrap !important; width: 100% !important;';

        // Create country select
        const countrySelect = document.createElement('select');
        countrySelect.className = 'form-select farmart-phone-country-select';
        countrySelect.style.cssText = 'width: 125px !important; min-width: 125px !important; max-width: 125px !important; flex: 0 0 125px !important; font-size: 0.875rem !important; padding-left: 0.5rem !important; padding-right: 1.5rem !important; cursor: pointer;';

        COUNTRY_CODES.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.code;
            opt.dataset.iso = c.iso;
            opt.textContent = `${c.flag} ${c.code}`;
            if (c.code === '+91') {
                opt.selected = true;
            }
            countrySelect.appendChild(opt);
        });

        // Insert input-group before target input and place input inside
        targetPhoneInput.parentNode.insertBefore(inputGroup, targetPhoneInput);
        inputGroup.appendChild(countrySelect);
        inputGroup.appendChild(targetPhoneInput);

        targetPhoneInput.setAttribute('placeholder', 'Mobile number without country code');

        // Initial phone parsing
        function parseAndDisplayPhone() {
            const raw = targetPhoneInput.value.trim();
            if (raw.startsWith('+')) {
                for (const c of COUNTRY_CODES) {
                    if (raw.startsWith(c.code)) {
                        countrySelect.value = c.code;
                        targetPhoneInput.value = raw.slice(c.code.length).trim();
                        break;
                    }
                }
            }
        }

        parseAndDisplayPhone();

        // When Country dropdown changes in modal, auto-align country code
        const countryDropdown = modal.querySelector('select.customer-address-country') || modal.querySelector('select[v-model*="address.country"]');
        if (countryDropdown) {
            countryDropdown.addEventListener('change', function () {
                const iso = (this.value || '').toUpperCase();
                const matched = COUNTRY_CODES.find(c => c.iso === iso);
                if (matched) {
                    countrySelect.value = matched.code;
                }
            });
        }

        // Before modal save / submit, ensure country code is prepended so Vue saves full phone
        function syncFullPhoneToVue() {
            const rawDigits = targetPhoneInput.value.trim();
            if (rawDigits && !rawDigits.startsWith('+')) {
                const code = countrySelect.value;
                const fullPhone = `${code} ${rawDigits.replace(/^[0\s+]+/, '')}`;
                targetPhoneInput.value = fullPhone;
                targetPhoneInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        const saveButtons = modal.querySelectorAll('.btn-primary, [type="submit"]');
        saveButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                syncFullPhoneToVue();
            }, true);
        });

        targetPhoneInput.addEventListener('blur', () => {
            const raw = targetPhoneInput.value.trim();
            if (raw && !raw.startsWith('+')) {
                const code = countrySelect.value;
                targetPhoneInput.value = `${code} ${raw.replace(/^[0\s+]+/, '')}`;
                targetPhoneInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        targetPhoneInput.addEventListener('focus', () => {
            const raw = targetPhoneInput.value.trim();
            if (raw.startsWith('+')) {
                const code = countrySelect.value;
                if (raw.startsWith(code)) {
                    targetPhoneInput.value = raw.slice(code.length).trim();
                }
            }
        });
    }

    function init() {
        injectModalStyles();
        enhanceDiscountModal();
        enhancePhoneInputInModal('add-customer');
        enhancePhoneInputInModal('edit-address');

        // Observe document for dynamic modal rendering
        const observer = new MutationObserver(() => {
            enhanceDiscountModal();
            enhancePhoneInputInModal('add-customer');
            enhancePhoneInputInModal('edit-address');
        });

        observer.observe(document.body, { childList: true, subtree: true });

        // Bootstrap modal shown event listeners
        ['add-discounts', 'add-customer', 'edit-address'].forEach(id => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.addEventListener('shown.bs.modal', () => {
                    const dialog = modal.querySelector('.modal-dialog');
                    if (dialog && (id === 'add-customer' || id === 'edit-address')) {
                        dialog.classList.add('modal-lg');
                        dialog.style.maxWidth = '760px';
                        dialog.style.width = '90%';
                    }
                    enhanceDiscountModal();
                    enhancePhoneInputInModal(id);
                });
            }
        });

        document.addEventListener('shown.bs.modal', function (e) {
            const modal = e.target;
            if (modal && (modal.id === 'add-customer' || modal.id === 'edit-address')) {
                const dialog = modal.querySelector('.modal-dialog');
                if (dialog) {
                    dialog.classList.add('modal-lg');
                    dialog.style.maxWidth = '760px';
                    dialog.style.width = '90%';
                }
                enhancePhoneInputInModal(modal.id);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
