/**
 * Razorpay Easy EMI & Affordability Suite Script
 * Handles dynamic mounting, live price variation recalculations, and checkout intent tracking.
 * SparroWave Technologies
 */

(function () {
    'use strict';

    function dockWidgetBelowPrice(wrapper) {
        if (!wrapper || wrapper.getAttribute('data-docked') === 'true') {
            return;
        }

        // Search for primary product price element in common Botble themes (Farmart, Nest, Shofy, Martfury)
        var priceElement = document.querySelector('.entry-product-header .product-price')
            || document.querySelector('.product-details-content .product-price')
            || document.querySelector('.product-info .product-price')
            || document.querySelector('.ps-product__price')
            || document.querySelector('.product-price');

        if (priceElement && priceElement.parentNode && priceElement.nextSibling !== wrapper) {
            priceElement.parentNode.insertBefore(wrapper, priceElement.nextSibling);
            wrapper.setAttribute('data-docked', 'true');
        }
    }

    function calculateMonthlyEmi(principal, months, annualRate) {
        if (!annualRate || annualRate <= 0) {
            return Math.round(principal / months);
        }
        var r = (annualRate / 12) / 100;
        var emi = (principal * r * Math.pow(1 + r, months)) / (Math.pow(1 + r, months) - 1);
        return Math.round(emi);
    }

    function updateWidgetForPrice(wrapper, newPrice) {
        if (!wrapper || isNaN(newPrice) || newPrice <= 0) return;

        var scheme = wrapper.getAttribute('data-scheme') || 'standard';
        var startingEmi = (scheme === 'no_cost') ? Math.round(newPrice / 6) : calculateMonthlyEmi(newPrice, 24, 15.0);

        var startingEmiEl = wrapper.querySelector('#rzp-starting-emi-val');
        if (startingEmiEl) {
            startingEmiEl.textContent = '₹' + startingEmi.toLocaleString('en-IN');
        }

        var modalPriceEls = document.querySelectorAll('.rzp-modal-price');
        modalPriceEls.forEach(function (el) {
            el.textContent = newPrice.toLocaleString('en-IN');
        });

        // Recalculate tables in EMI plans modal
        var modal = document.getElementById('razorpay-emi-plans-modal');
        if (modal) {
            var tables = modal.querySelectorAll('.rzp-tenure-table');
            tables.forEach(function (table) {
                var rows = table.querySelectorAll('tbody tr');
                rows.forEach(function (row) {
                    var tenureText = row.querySelector('td:first-child');
                    if (!tenureText) return;
                    var monthsMatch = tenureText.textContent.match(/\d+/);
                    if (!monthsMatch) return;
                    var months = parseInt(monthsMatch[0], 10);
                    var isNoCost = !!row.querySelector('.bg-success-subtle');

                    var stdMonthly = calculateMonthlyEmi(newPrice, months, 15.0);
                    var stdTotal = Math.round(stdMonthly * months);
                    var stdInterest = Math.max(0, stdTotal - newPrice);

                    var monthlyEmi = isNoCost ? Math.round(newPrice / months) : stdMonthly;
                    var total = isNoCost ? newPrice : stdTotal;
                    var savings = isNoCost ? stdInterest : 0;

                    var monthlyCell = row.querySelector('td:nth-child(2)');
                    if (monthlyCell) {
                        monthlyCell.textContent = '₹' + monthlyEmi.toLocaleString('en-IN') + '/mo';
                    }

                    var totalCell = row.querySelector('td:nth-child(4)');
                    if (totalCell) {
                        totalCell.textContent = '₹' + total.toLocaleString('en-IN');
                    }

                    var savingsCell = row.querySelector('td:nth-child(5)');
                    if (savingsCell) {
                        if (savings > 0) {
                            savingsCell.innerHTML = '<span class="badge bg-success text-white">Save ₹' + savings.toLocaleString('en-IN') + '</span>';
                        } else {
                            savingsCell.innerHTML = '<span class="text-muted">-</span>';
                        }
                    }
                });
            });
        }
    }

    function parsePriceFromText(text) {
        if (!text) return 0;
        var cleaned = text.replace(/[^0-9.]/g, '');
        var parsed = parseFloat(cleaned);
        return isNaN(parsed) ? 0 : parsed;
    }

    function initVariationWatcher(wrapper) {
        var priceTarget = document.querySelector('.bb-product-price-text')
            || document.querySelector('.product-price .amount')
            || document.querySelector('.product-price ins');

        if (!priceTarget) return;

        var lastPrice = parseFloat(wrapper.getAttribute('data-price')) || 0;

        var observer = new MutationObserver(function () {
            var newPrice = parsePriceFromText(priceTarget.textContent || priceTarget.innerText);
            if (newPrice > 0 && Math.abs(newPrice - lastPrice) > 1) {
                lastPrice = newPrice;
                wrapper.setAttribute('data-price', newPrice);
                wrapper.setAttribute('data-amount', Math.round(newPrice * 100));
                updateWidgetForPrice(wrapper, newPrice);
            }
        });

        observer.observe(priceTarget, { characterData: true, childList: true, subtree: true });

        // Listen for jQuery variation change event if theme triggers it
        if (window.jQuery) {
            window.jQuery(document).on('ecommerce.product.variation-changed', function (e, data) {
                if (data && data.price) {
                    var price = parseFloat(data.price);
                    if (price > 0) {
                        wrapper.setAttribute('data-price', price);
                        wrapper.setAttribute('data-amount', Math.round(price * 100));
                        updateWidgetForPrice(wrapper, price);
                    }
                }
            });
        }
    }

    function dockCartBanner() {
        var tpl = document.getElementById('razorpay-cart-emi-dock-template');
        if (!tpl) return;

        // If banner was already rendered server-side via theme filter, do not duplicate
        if (document.querySelector('.razorpay-cart-emi-alert[data-docked="true"]')) {
            return;
        }

        var banner = tpl.querySelector('.razorpay-cart-emi-alert');
        if (!banner) return;

        // Find checkout button or cart subtotal container across any theme (Farmart, Nest, Shofy, Martfury)
        var target = document.querySelector('.checkout-button')
            || document.querySelector('.proceed-to-checkout')
            || document.querySelector('.cart-actions')
            || document.querySelector('a[href*="checkout"]')
            || document.querySelector('.cart-totals')
            || document.querySelector('.cart__summary');

        if (target && target.parentNode) {
            var container = target.closest('.proceed-to-checkout') || target;
            banner.setAttribute('data-docked', 'true');
            container.parentNode.insertBefore(banner, container);
        }
    }

    function init() {
        var wrapper = document.querySelector('.razorpay-easy-emi-container');
        if (wrapper) {
            dockWidgetBelowPrice(wrapper);
            initVariationWatcher(wrapper);

            // Track user interaction with plans/offers
            var viewPlansBtn = wrapper.querySelector('.rzp-btn-view-plans');
            var viewOffersBtn = wrapper.querySelector('.rzp-btn-view-offers');

            function recordIntent() {
                try {
                    sessionStorage.setItem('razorpay_selected_emi', 'true');
                } catch (e) {}
            }

            if (viewPlansBtn) viewPlansBtn.addEventListener('click', recordIntent);
            if (viewOffersBtn) viewOffersBtn.addEventListener('click', recordIntent);
        }

        // Auto-dock cart banner if on cart page across any Botble theme
        dockCartBanner();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
