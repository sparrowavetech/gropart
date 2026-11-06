/**
 * Vendor Verified Badge - Admin UI Extension
 * Theme-agnostic and plugin-independent decorator for product search dropdown and product view.
 */
(function () {
    'use strict';

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function decorateSearchDropdown() {
        const searchContainer = document.querySelector('.box-search-advance.product');
        if (!searchContainer) return;

        const items = searchContainer.querySelectorAll('.list-group-item:not([data-vendor-badge-decorated])');
        items.forEach(el => {
            el.setAttribute('data-vendor-badge-decorated', '1');
            const pid = el.getAttribute('data-id') || el.dataset?.id;
            let productData = null;

            if (window.__adminProductSearchCache && window.__adminProductSearchCache[pid]) {
                productData = window.__adminProductSearchCache[pid];
            }

            const storeName = productData?.store_name ?? productData?.flags?.store_name ?? productData?.store?.name;
            const storeType = productData?.store_type ?? productData?.flags?.store_type;
            const isVerified = productData?.is_verified ?? productData?.flags?.is_verified;

            if (storeName || storeType || isVerified) {
                const titleEl = el.querySelector('.item-name') || el.querySelector('strong') || el;
                let metaRow = el.querySelector('.item-search-badges-row');
                if (!metaRow) {
                    metaRow = document.createElement('div');
                    metaRow.className = 'item-search-badges-row d-flex align-items-center flex-wrap gap-1 mt-1';
                    titleEl.appendChild(metaRow);
                }

                if (storeName && !metaRow.querySelector('.vendor-store-name')) {
                    const span = document.createElement('span');
                    span.className = 'text-muted me-1 vendor-store-name';
                    span.innerHTML = `Store: <strong class="text-dark">${escapeHtml(storeName)}</strong>`;
                    metaRow.prepend(span);
                }

                if (storeType && !metaRow.querySelector('.vendor-store-type')) {
                    const span = document.createElement('span');
                    span.className = 'badge bg-secondary-lt text-secondary vendor-store-type';
                    span.style.cssText = 'font-size: 10px; padding: 2px 5px;';
                    span.textContent = storeType;
                    metaRow.appendChild(span);
                }

                if (isVerified && !metaRow.querySelector('.vendor-is-verified')) {
                    const span = document.createElement('span');
                    span.className = 'badge bg-blue-lt text-blue vendor-is-verified';
                    span.style.cssText = 'font-size: 10px; padding: 2px 5px;';
                    span.innerHTML = '<i class="ti ti-check me-1"></i>Verified';
                    metaRow.appendChild(span);
                }
            }
        });
    }

    function init() {
        const searchContainer = document.querySelector('.box-search-advance.product');
        if (searchContainer) {
            const observer = new MutationObserver(decorateSearchDropdown);
            observer.observe(searchContainer, { childList: true, subtree: true });
            decorateSearchDropdown();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

