/**
 * ShipMozo - Admin UI Extension
 * Theme-agnostic and plugin-independent decorator for product search dropdown and product view.
 */
(function () {
    'use strict';

    function decorateSearchDropdown() {
        const searchContainer = document.querySelector('.box-search-advance.product');
        if (!searchContainer) return;

        const items = searchContainer.querySelectorAll('.list-group-item:not([data-shipmozo-decorated])');
        items.forEach(el => {
            el.setAttribute('data-shipmozo-decorated', '1');
            const pid = el.getAttribute('data-id') || el.dataset?.id;
            let productData = null;

            if (window.__adminProductSearchCache && window.__adminProductSearchCache[pid]) {
                productData = window.__adminProductSearchCache[pid];
            }

            const isShipmozo = productData?.shipmozo ?? productData?.flags?.shipmozo;
            if (isShipmozo) {
                const titleEl = el.querySelector('.item-name') || el.querySelector('strong') || el;
                let metaRow = el.querySelector('.item-search-badges-row');
                if (!metaRow) {
                    metaRow = document.createElement('div');
                    metaRow.className = 'item-search-badges-row d-flex align-items-center flex-wrap gap-1 mt-1';
                    titleEl.appendChild(metaRow);
                }

                if (!metaRow.querySelector('.shipmozo-search-badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-azure-lt text-azure shipmozo-search-badge';
                    badge.style.cssText = 'font-size: 10px; padding: 2px 5px;';
                    badge.innerHTML = '<i class="ti ti-box me-1"></i>ShipMozo';
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
