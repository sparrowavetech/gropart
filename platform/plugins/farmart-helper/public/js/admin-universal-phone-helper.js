/**
 * Farmart Helper - Universal Phone Formatting & Modal Auto-Initializer
 * 1. Seeds local country code cache with 'in' (India, +91) to prevent Botble defaulting to US (+1).
 * 2. Auto-initializes and enhances phone inputs inside Bootstrap modals, popups, and AJAX forms.
 * 3. Prevents layout distortion, wrapping, clipping, and z-index dropdown cutoff.
 */
(function () {
    'use strict';

    // 1. Seed India (+91) as default country for intl-tel-input geo lookup
    try {
        const cached = localStorage.getItem('ipinfo_country_code');
        if (!cached || cached === 'us') {
            localStorage.setItem('ipinfo_country_code', 'in');
            localStorage.setItem('ipinfo_country_expiry', (new Date().getTime() + 30 * 24 * 60 * 60 * 1000).toString());
        }
    } catch (e) {
        // Storage disabled or private browsing
    }

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

    /**
     * Enhance an individual raw phone input with a clean country code select
     */
    function enhancePhoneInput(input) {
        if (!input || input.hasAttribute('data-farmart-phone-enhanced')) {
            return;
        }

        // Do not wrap if it's already managed by Botble's intl-tel-input (.iti)
        if (input.closest('.iti') || input.classList.contains('iti__input')) {
            return;
        }

        // Check if input is a phone input
        const name = (input.getAttribute('name') || '').toLowerCase();
        const type = (input.getAttribute('type') || '').toLowerCase();
        const id = (input.getAttribute('id') || '').toLowerCase();
        const isPhone = type === 'tel'
            || name.includes('phone')
            || name.includes('mobile')
            || id.includes('phone')
            || id.includes('mobile')
            || input.classList.contains('js-phone-number-mask');

        if (!isPhone) {
            return;
        }

        // If it's a hidden input, ignore
        if (type === 'hidden') {
            return;
        }

        input.setAttribute('data-farmart-phone-enhanced', '1');

        // Create container
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

        // Insert inputGroup in place of input
        const parent = input.parentNode;
        parent.insertBefore(inputGroup, input);
        inputGroup.appendChild(countrySelect);
        inputGroup.appendChild(input);

        // Adjust input styling
        input.style.flex = '1 1 auto';
        input.style.minWidth = '0';
        input.style.borderTopLeftRadius = '0';
        input.style.borderBottomLeftRadius = '0';

        if (!input.placeholder || input.placeholder === '+1') {
            input.setAttribute('placeholder', 'Enter phone number');
        }

        // Check if value already has country code
        function parseInitialValue() {
            let val = (input.value || '').trim();
            if (val.startsWith('+')) {
                for (const c of COUNTRY_CODES) {
                    if (val.startsWith(c.code)) {
                        countrySelect.value = c.code;
                        input.value = val.slice(c.code.length).trim();
                        break;
                    }
                }
            } else if (val.startsWith('91') && val.length === 12) {
                countrySelect.value = '+91';
                input.value = val.slice(2).trim();
            }
        }

        parseInitialValue();

        // Synchronization on submit
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                const dial = countrySelect.value;
                let num = input.value.trim().replace(/^0+/, '');
                if (num && !num.startsWith('+')) {
                    input.value = dial + num;
                }
            }, { capture: true });
        }
    }

    /**
     * Scan container (modal, page, form) for phone inputs and initialize them
     */
    function scanAndInitPhones(scope) {
        const root = scope || document;

        // 1. If Botble intl-tel-input exists and not initialized, trigger init event
        const uninitIti = root.querySelectorAll('.js-phone-number-mask[data-country-code-selection="true"]:not([data-iti-initialized="true"])');
        if (uninitIti.length > 0) {
            try {
                document.dispatchEvent(new CustomEvent('phone-number-field:init'));
            } catch (e) {}
        }

        // 2. Enhance raw phone inputs in modals / dialogs
        const phoneInputs = root.querySelectorAll('input[type="tel"], input[name*="phone"], input[name*="mobile"], input.customer-address-phone');
        phoneInputs.forEach(input => {
            enhancePhoneInput(input);
        });
    }

    /**
     * Handle modal opening
     */
    function handleModalShown(modal) {
        if (!modal) return;

        const dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
            // Ensure modal has adequate width so inputs don't distort
            const curWidth = parseInt(window.getComputedStyle(dialog).maxWidth, 10);
            if (!curWidth || curWidth < 600) {
                dialog.style.maxWidth = '760px';
                dialog.style.width = '90%';
            }
        }

        scanAndInitPhones(modal);
    }

    // Attach to jQuery / Bootstrap modal events if present
    if (window.jQuery) {
        window.jQuery(document).on('shown.bs.modal', function (e) {
            handleModalShown(e.target);
        });

        window.jQuery(document).ajaxComplete(function () {
            setTimeout(function () {
                scanAndInitPhones(document);
            }, 100);
        });
    }

    // Native DOM listeners
    document.addEventListener('shown.bs.modal', function (e) {
        handleModalShown(e.target);
    });

    document.addEventListener('DOMContentLoaded', function () {
        scanAndInitPhones(document);
    });

    // Also observe DOM additions (for Vue / AJAX injected modals)
    try {
        const observer = new MutationObserver(function (mutations) {
            for (const mutation of mutations) {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) { // ELEMENT_NODE
                            if (node.matches && (node.matches('.modal') || node.querySelector('.modal'))) {
                                const modal = node.matches('.modal') ? node : node.querySelector('.modal');
                                handleModalShown(modal);
                            } else if (node.matches && (node.matches('input[type="tel"]') || node.querySelector('input[type="tel"], input[name*="phone"]'))) {
                                scanAndInitPhones(node);
                            }
                        }
                    });
                }
            }
        });

        observer.observe(document.body || document.documentElement, {
            childList: true,
            subtree: true
        });
    } catch (e) {}

    // Initial pass
    if (document.readyState !== 'loading') {
        scanAndInitPhones(document);
    }
})();

