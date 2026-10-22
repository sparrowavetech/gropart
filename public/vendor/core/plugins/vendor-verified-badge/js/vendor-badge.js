(function ($) {
    'use strict';

    function initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            var tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipElements.forEach(function (el) {
                if (!bootstrap.Tooltip.getInstance(el)) {
                    new bootstrap.Tooltip(el, {
                        container: 'body',
                        trigger: 'hover'
                    });
                }
            });
        } else if (typeof $ !== 'undefined' && $.fn && $.fn.tooltip) {
            $('[data-bs-toggle="tooltip"]').tooltip({
                container: 'body',
                trigger: 'hover'
            });
        }
    }

    function getTranslations() {
        var t = window.vendorBadgeTranslations || {};
        var types = t.types || {};

        return {
            areYouA: t.are_you_a || 'Are You A ?',
            selectShopType: t.select_shop_type || 'Select Shop Type',
            manufacture: types.manufacture || 'Manufacturer',
            wholesaler: types.wholesaler || 'Wholesaler',
            retailer: types.retailer || 'Retailer'
        };
    }

    function enhanceVendorUI() {
        initTooltips();

        // 1. Automatically inject "Are You A? (Shop Type)" into Become Vendor registration forms on any theme
        var $urlWrapper = $('.shop-url-wrapper, #shop-url-register, input[name="shop_url"]');
        if ($urlWrapper.length && !$urlWrapper.closest('form').find('[name="shop_category"]').length) {
            var trans = getTranslations();
            var shopTypeHtml = `
                <div class="mb-3 shop-category-wrapper" id="shop-category-wrapper">
                    <label class="form-label required" for="shop-category-register">` + escapeHtml(trans.areYouA) + `</label>
                    <select class="form-select form-control" id="shop-category-register" name="shop_category" required>
                        <option value="">` + escapeHtml(trans.selectShopType) + `</option>
                        <option value="manufacture">` + escapeHtml(trans.manufacture) + `</option>
                        <option value="wholesaler">` + escapeHtml(trans.wholesaler) + `</option>
                        <option value="retailer">` + escapeHtml(trans.retailer) + `</option>
                    </select>
                </div>
            `;
            $urlWrapper.closest('.shop-url-wrapper, .mb-3, .form-group').after(shopTypeHtml);
        }

        // 2. Automatically enhance Vendor Dashboard Sidebar Greeting across themes (Farmart, Nest, Shofy, Martfury)
        var $welcomeContainer = $([
            '.ps-block--user-wellcome .ps-block__right', // Farmart / Martfury
            '.ps-block--user-wellcome',                  // Farmart fallback
            '.vendor-header .vendor-name',               // Nest
            '.dashboard-user-name',                      // Shofy / Nest
            '.user-card-info',                           // Generic
            '.vendor-profile-info',                      // Generic
            '.vendor-dashboard-header .vendor-title'     // Generic
        ].join(', ')).first();

        if ($welcomeContainer.length && !$welcomeContainer.find('.vendor-shop-type-badge, .verified-store-badge').length) {
            var storeData = window.vendorBadgeData || null;
            if (storeData) {
                if (storeData.isVerified && storeData.verifiedHtml) {
                    var $firstP = $welcomeContainer.find('p:first, h3:first, h4:first, span:first');
                    if ($firstP.length) {
                        $firstP.append(' ' + storeData.verifiedHtml);
                    } else {
                        $welcomeContainer.append(' ' + storeData.verifiedHtml);
                    }
                }
                if (storeData.shopTypeHtml) {
                    $welcomeContainer.append(' ' + storeData.shopTypeHtml);
                }
            }
        }

        // 3. Automatically enhance Admin Store View page (/admin/marketplaces/stores/view/{id})
        if (window.adminStoreBadgesHtml) {
            var $adminStoreLink = $('.card .text-center.p-3 a[target="_blank"]:first, .card .text-center.p-3 a[href*="/stores/"]:first');
            if ($adminStoreLink.length && !$adminStoreLink.parent().find('.verified-store-badge, .vendor-shop-type-badge').length) {
                $adminStoreLink.after('<div class="mt-1">' + window.adminStoreBadgesHtml + '</div>');
            }
        }
    }

    function escapeHtml(string) {
        if (!string) return '';
        var entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };
        return String(string).replace(/[&<>"']/g, function (s) {
            return entityMap[s];
        });
    }

    $(document).ready(function () {
        enhanceVendorUI();
    });

    $(document).ajaxComplete(function () {
        setTimeout(enhanceVendorUI, 100);
    });

    document.addEventListener('DOMContentLoaded', enhanceVendorUI);
})(jQuery);
