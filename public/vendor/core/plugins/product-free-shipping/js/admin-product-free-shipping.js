/**
 * Product Free Shipping - Admin UI Extension
 * Theme-agnostic and plugin-independent decorator for product search dropdown and product view.
 */
(function () {
    'use strict';

    function decorateSearchDropdown() {
        const searchContainer = document.querySelector('.box-search-advance.product');
        if (!searchContainer) return;

        const items = searchContainer.querySelectorAll('.list-group-item:not([data-pfs-decorated])');
        items.forEach(el => {
            el.setAttribute('data-pfs-decorated', '1');
            const pid = el.getAttribute('data-id') || el.dataset?.id;
            let productData = null;

            if (window.__adminProductSearchCache && window.__adminProductSearchCache[pid]) {
                productData = window.__adminProductSearchCache[pid];
            }

            const isFree = productData?.free_shipping ?? productData?.flags?.free_shipping;
            if (isFree) {
                const titleEl = el.querySelector('.item-name') || el.querySelector('strong') || el;
                let metaRow = el.querySelector('.item-search-badges-row');
                if (!metaRow) {
                    metaRow = document.createElement('div');
                    metaRow.className = 'item-search-badges-row d-flex align-items-center flex-wrap gap-1 mt-1';
                    titleEl.appendChild(metaRow);
                }

                if (!metaRow.querySelector('.pfs-free-shipping-badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-teal-lt text-teal pfs-free-shipping-badge';
                    badge.style.cssText = 'font-size: 10px; padding: 2px 5px;';
                    badge.innerHTML = '<i class="ti ti-gift me-1"></i>Free Delivery';
                    metaRow.appendChild(badge);
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
