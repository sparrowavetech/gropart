/**
 * ShipMozo - Admin UI Extension
 * Theme-agnostic and plugin-independent decorator for product search dropdown, product view, and order create table.
 */
(function () {
    'use strict';

    window.__adminProductSearchCache = window.__adminProductSearchCache || {};

    function cacheSearchResults(items) {
        if (!Array.isArray(items)) return;
        items.forEach(item => {
            if (item && item.id) {
                window.__adminProductSearchCache[item.id] = Object.assign(window.__adminProductSearchCache[item.id] || {}, item);
            }
            if (item && Array.isArray(item.variations)) {
                item.variations.forEach(v => {
                    if (v && v.id) {
                        window.__adminProductSearchCache[v.id] = Object.assign(window.__adminProductSearchCache[v.id] || {}, v, {
                            shipmozo: item.shipmozo ?? item.flags?.shipmozo,
                        });
                    }
                });
            }
        });
    }

    if (window.axios && window.axios.interceptors) {
        window.axios.interceptors.response.use(function (response) {
            try {
                const url = response.config?.url || '';
                if (url.includes('products/get-all-products-and-variations') || url.includes('check-data-before-create-order')) {
                    const items = response.data?.data?.data || response.data?.data?.products || response.data?.data || [];
                    cacheSearchResults(items);
                    setTimeout(() => {
                        decorateSearchDropdown();
                        decorateAddedProductsTable();
                    }, 50);
                }
            } catch (e) {}
            return response;
        });
    }

    let debounceTimer = null;

    function findProductData(name, id) {
        if (!window.__adminProductSearchCache) return null;
        if (id && window.__adminProductSearchCache[id]) {
            return window.__adminProductSearchCache[id];
        }
        if (!name) return null;
        const cleanName = name.toLowerCase().replace(/\s*\([^)]*\)\s*$/, '').trim();
        for (const key of Object.keys(window.__adminProductSearchCache)) {
            const p = window.__adminProductSearchCache[key];
            if (p && p.name) {
                const pName = p.name.toLowerCase().replace(/\s*\([^)]*\)\s*$/, '').trim();
                if (cleanName === pName || cleanName.includes(pName) || pName.includes(cleanName)) {
                    return p;
                }
            }
        }
        return null;
    }

    function decorateSearchDropdown() {
        const searchContainer = document.querySelector('.box-search-advance.product');
        if (!searchContainer) return;

        const items = searchContainer.querySelectorAll('.list-group-item:not([data-shipmozo-decorated])');
        items.forEach(el => {
            if (el.hasAttribute('v-ec-modal.add-product-item') || el.querySelector('img[src*="next-create-custom-line-item"]')) {
                return;
            }

            const titleEl = el.querySelector('.col.text-truncate') || el.querySelector('.item-name') || el;
            if (!titleEl) return;

            const nameSpan = titleEl.querySelector('span:not(.text-info):not(.text-danger):not(.text-warning)');
            const productName = nameSpan ? nameSpan.textContent.trim() : titleEl.textContent.trim();

            const productData = findProductData(productName);

            if (productData) {
                el.setAttribute('data-shipmozo-decorated', '1');
                const isShipmozo = productData.shipmozo ?? productData.flags?.shipmozo;
                if (isShipmozo) {
                    let metaRow = el.querySelector('.item-search-badges-row');
                    if (!metaRow) {
                        metaRow = document.createElement('div');
                        metaRow.className = 'item-search-badges-row d-flex align-items-center flex-wrap gap-1 mt-1';
                        const firstActionRow = titleEl.querySelector('.row.align-items-center');
                        if (firstActionRow && firstActionRow.parentNode === titleEl) {
                            firstActionRow.after(metaRow);
                        } else {
                            titleEl.appendChild(metaRow);
                        }
                    }

                    if (!metaRow.querySelector('.shipmozo-search-badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-azure-lt text-azure shipmozo-search-badge';
                        badge.style.cssText = 'font-size: 10px; padding: 2px 5px;';
                        badge.innerHTML = '<i class="ti ti-package me-1"></i>ShipMozo';
                        metaRow.appendChild(badge);
                    }
                }
            }
        });
    }

    function decorateAddedProductsTable() {
        const table = document.querySelector('table.card-table') || document.querySelector('.table-responsive table');
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr:not([data-shipmozo-table-decorated])');
        rows.forEach(tr => {
            const link = tr.querySelector('td:nth-child(2) a');
            if (!link) return;

            const name = link.textContent.trim();
            const href = link.getAttribute('href') || '';
            const idMatch = href.match(/\/(?:edit|products)\/(\d+)/);
            const prodId = idMatch ? parseInt(idMatch[1], 10) : null;

            const productData = findProductData(name, prodId);
            if (productData) {
                tr.setAttribute('data-shipmozo-table-decorated', '1');
                const isShipmozo = productData.shipmozo ?? productData.flags?.shipmozo;
                if (isShipmozo) {
                    const col = tr.querySelector('td:nth-child(2)');
                    if (!col) return;

                    let metaRow = col.querySelector('.item-table-badges-row');
                    if (!metaRow) {
                        metaRow = document.createElement('div');
                        metaRow.className = 'item-table-badges-row d-flex align-items-center flex-wrap gap-1 mt-1';
                        col.appendChild(metaRow);
                    }

                    if (!metaRow.querySelector('.shipmozo-table-badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-azure-lt text-azure shipmozo-table-badge';
                        badge.style.cssText = 'font-size: 10px; padding: 2px 5px;';
                        badge.innerHTML = '<i class="ti ti-package me-1"></i>ShipMozo';
                        metaRow.appendChild(badge);
                    }
                }
            }
        });
    }

    function scheduleDecorate() {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            decorateSearchDropdown();
            decorateAddedProductsTable();
        }, 100);
    }

    function init() {
        const searchContainer = document.querySelector('.box-search-advance.product');
        if (searchContainer) {
            const observer = new MutationObserver((mutations) => {
                const isOnlyBadges = mutations.every(m => m.target && (m.target.closest?.('.item-search-badges-row, .item-table-badges-row') || m.target.classList?.contains('item-search-badges-row') || m.target.classList?.contains('item-table-badges-row')));
                if (isOnlyBadges) return;
                scheduleDecorate();
            });
            observer.observe(searchContainer, { childList: true, subtree: true });
        }

        const orderContainer = document.querySelector('.card-table') || document.querySelector('.table-responsive') || document.querySelector('.main-card');
        if (orderContainer) {
            const tableObserver = new MutationObserver((mutations) => {
                const isOnlyBadges = mutations.every(m => m.target && (m.target.closest?.('.item-search-badges-row, .item-table-badges-row') || m.target.classList?.contains('item-search-badges-row') || m.target.classList?.contains('item-table-badges-row')));
                if (isOnlyBadges) return;
                scheduleDecorate();
            });
            tableObserver.observe(orderContainer, { childList: true, subtree: true });
        }

        setTimeout(scheduleDecorate, 300);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
