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

    function enhanceVendorUI() {
        initTooltips();

        // 1. Automatically inject "Are You A? (Shop Type)" into Become Vendor registration forms on any theme
        var $urlWrapper = $('.shop-url-wrapper, #shop-url-register, input[name="shop_url"]');
        if ($urlWrapper.length && !$urlWrapper.closest('form').find('[name="shop_category"]').length) {
            var shopTypeHtml = `
                <div class="mb-3 shop-category-wrapper" id="shop-category-wrapper">
                    <label class="form-label required" for="shop-category-register">Are You A ?</label>
                    <select class="form-select form-control" id="shop-category-register" name="shop_category" required>
                        <option value="">Select Shop Type</option>
                        <option value="manufacture">Manufacturer</option>
                        <option value="wholesaler">Wholesaler</option>
                        <option value="retailer">Retailer</option>
                    </select>
                </div>
            `;
            $urlWrapper.closest('.shop-url-wrapper, .mb-3, .form-group').after(shopTypeHtml);
        }

        // 2. Automatically enhance Vendor Dashboard Sidebar Greeting on Farmart and other themes
        var $welcomeRight = $('.ps-block--user-wellcome .ps-block__right');
        if ($welcomeRight.length && !$welcomeRight.find('.vendor-shop-type-badge, .verified-store-badge').length) {
            var storeData = window.vendorBadgeData || null;
            if (storeData) {
                if (storeData.isVerified) {
                    $welcomeRight.find('p:first').append(' ' + storeData.verifiedHtml);
                }
                if (storeData.shopTypeHtml) {
                    $welcomeRight.append(storeData.shopTypeHtml);
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

    $(document).ready(function () {
        enhanceVendorUI();
    });

    $(document).ajaxComplete(function () {
        setTimeout(enhanceVendorUI, 100);
    });

    document.addEventListener('DOMContentLoaded', enhanceVendorUI);
})(jQuery);
