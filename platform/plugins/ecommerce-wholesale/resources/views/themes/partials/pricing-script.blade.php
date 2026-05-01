{{-- Wholesale price updater (no visible table) --}}
@php
    $tiersJson = $pricingTiers->map(fn ($tier) => [
        'min' => $tier['min_qty'],
        'max' => $tier['max_qty'] ?? 999999,
        'price' => $tier['price'],
        'formattedPrice' => format_price($tier['price']),
    ])->values();
@endphp
<script>
(function() {
    var tiers = @json($tiersJson);
    if (!tiers.length) return;

    var tiersEndpoint = @json(route('public.wholesale.tiers', ['productId' => '__ID__']));
    var priceSelectors = '[data-bb-value="product-price"], .bb-product-price-text, .tp-product-details-price.new-price, .product-price-sale, .price-current';

    function findTier(qty) {
        for (var i = 0; i < tiers.length; i++) {
            if (qty >= tiers[i].min && qty <= tiers[i].max) {
                return tiers[i];
            }
        }
        return null;
    }

    function updatePrice(qty) {
        var tier = findTier(qty);

        document.querySelectorAll(priceSelectors).forEach(function(el) {
            if (!el.dataset.originalPrice) {
                el.dataset.originalPrice = el.textContent;
            }
            if (tier) {
                el.textContent = tier.formattedPrice;
            } else if (el.dataset.originalPrice) {
                el.textContent = el.dataset.originalPrice;
            }
        });
    }

    var qtyInputSelectors = 'input[name="qty"], .tp-cart-input, .qty-input, .product-quantity input[type="number"]';
    var qtyBtnSelectors = '[data-bb-toggle="decrease-qty"], [data-bb-toggle="increase-qty"], .tp-cart-plus, .tp-cart-minus, .qty-btn, .quantity-btn';

    function getQtyValue() {
        var input = document.querySelector(qtyInputSelectors);
        return input ? (parseInt(input.value) || 1) : 1;
    }

    function onVariantChanged(response) {
        if (!response || !response.data || response.data.error_message) return;

        var variantId = response.data.id;
        if (!variantId) return;

        var url = tiersEndpoint.replace('__ID__', variantId);

        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.tiers || !data.tiers.length) return;

                tiers = data.tiers;

                document.querySelectorAll(priceSelectors).forEach(function(el) {
                    el.dataset.originalPrice = response.data.display_sale_price;
                });

                updatePrice(getQtyValue());
            });
    }

    var hookAttempts = 0;
    function hookSwatchCallback() {
        if (hookAttempts++ > 50) return;
        var orig = window.onChangeSwatchesSuccess;
        if (typeof orig === 'function') {
            window.onChangeSwatchesSuccess = function(response, element) {
                orig(response, element);
                onVariantChanged(response);
            };
            return;
        }
        setTimeout(hookSwatchCallback, 100);
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.product-attribute-swatches')) {
            hookSwatchCallback();
        }
    });

    document.body.addEventListener('change', function(e) {
        if (e.target.matches(qtyInputSelectors)) {
            updatePrice(parseInt(e.target.value) || 1);
        }
    });

    document.body.addEventListener('input', function(e) {
        if (e.target.matches(qtyInputSelectors)) {
            updatePrice(parseInt(e.target.value) || 1);
        }
    });

    document.body.addEventListener('click', function(e) {
        if (e.target.closest(qtyBtnSelectors)) {
            setTimeout(function() {
                updatePrice(getQtyValue());
            }, 50);
        }
    });
})();
</script>
