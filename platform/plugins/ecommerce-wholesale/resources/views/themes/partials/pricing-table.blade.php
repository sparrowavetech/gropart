@if($pricingTiers->isNotEmpty())
@php
    $wsHelper = \Botble\EcommerceWholesale\Facades\WholesaleHelper::getFacadeRoot();
    $style = $wsHelper->getStyle();
    $primaryColor = $wsHelper->getPrimaryColor();
    $headerColor = $wsHelper->getHeaderColor();
    $priceColor = $wsHelper->getPriceColor();
    $badgeColor = $wsHelper->getBadgeColor();
    $savingsColor = $wsHelper->getSavingsColor();
    $borderColor = $wsHelper->getBorderColor();
    $displayMode = $wsHelper->getDisplayMode();
    $showIcon = $wsHelper->showIcon();
    $icon = $wsHelper->getIcon();
    $showOriginalPrice = $wsHelper->showOriginalPrice();
    $showSavings = $wsHelper->showSavings();

    [$r, $g, $b] = sscanf($primaryColor, '#%02x%02x%02x');
@endphp
<style>
    .wholesale-pricing-table--{{ $style }} {
        --ws-primary: {{ $primaryColor }};
        --ws-primary-rgb: {{ $r }}, {{ $g }}, {{ $b }};
        --ws-header-color: {{ $headerColor }};
        --ws-price-color: {{ $priceColor }};
        --ws-badge-color: {{ $badgeColor }};
        --ws-savings-color: {{ $savingsColor }};
        --ws-border-color: {{ $borderColor }};
    }
    .wholesale-pricing-table--modern {
        border: 2px solid var(--ws-primary);
        border-radius: 12px;
        overflow: hidden;
    }
    .wholesale-pricing-table--modern .ws-header {
        background: var(--ws-primary);
        color: #fff;
        padding: 12px 16px;
    }
    .wholesale-pricing-table--modern .ws-header h5 { color: #fff; }
    .wholesale-pricing-table--modern .table thead {
        background: rgba(var(--ws-primary-rgb), 0.08);
    }
    .wholesale-pricing-table--modern .ws-tier-active {
        background: rgba(var(--ws-primary-rgb), 0.12) !important;
    }

    .wholesale-pricing-table--minimal {
        border: 1px solid var(--ws-border-color);
        border-radius: 8px;
        overflow: hidden;
    }
    .wholesale-pricing-table--minimal .ws-header {
        padding: 10px 16px;
        border-bottom: 1px solid var(--ws-border-color);
    }
    .wholesale-pricing-table--minimal .ws-tier-active {
        background: rgba(var(--ws-primary-rgb), 0.08) !important;
        border-left: 3px solid var(--ws-primary);
    }

    .wholesale-pricing-table--classic {
        border: 1px solid var(--ws-border-color);
        border-radius: 4px;
        overflow: hidden;
    }
    .wholesale-pricing-table--classic .ws-header {
        background: #f9fafb;
        padding: 12px 16px;
        border-bottom: 2px solid var(--ws-primary);
    }
    .wholesale-pricing-table--classic .table thead {
        background: #f3f4f6;
    }
    .wholesale-pricing-table--classic .ws-tier-active {
        background: rgba(var(--ws-primary-rgb), 0.08) !important;
        font-weight: bold;
    }

    .wholesale-pricing-table--elegant {
        border: 1px solid rgba(var(--ws-primary-rgb), 0.2);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(var(--ws-primary-rgb), 0.08);
    }
    .wholesale-pricing-table--elegant .ws-header {
        background: linear-gradient(135deg, var(--ws-primary), rgba(var(--ws-primary-rgb), 0.8));
        color: #fff;
        padding: 14px 20px;
    }
    .wholesale-pricing-table--elegant .ws-header h5 { color: #fff; }
    .wholesale-pricing-table--elegant .table thead {
        background: rgba(var(--ws-primary-rgb), 0.05);
    }
    .wholesale-pricing-table--elegant .ws-tier-active {
        background: rgba(var(--ws-primary-rgb), 0.1) !important;
        border-left: 3px solid var(--ws-primary);
    }

    .wholesale-pricing-table .ws-header h5 {
        color: var(--ws-header-color);
    }
    .wholesale-pricing-table .ws-price-discounted {
        color: var(--ws-price-color);
        font-weight: bold;
    }
    .wholesale-pricing-table .ws-badge {
        background-color: var(--ws-badge-color) !important;
        color: #fff;
    }
    .wholesale-pricing-table .ws-savings {
        color: var(--ws-savings-color);
    }
    .wholesale-pricing-table .table {
        margin-bottom: 0;
    }

    /* Force wholesale table to remain a proper table on mobile (prevent theme stacking) */
    .wholesale-pricing-table .table-responsive table,
    .wholesale-pricing-table .table-responsive thead,
    .wholesale-pricing-table .table-responsive tbody,
    .wholesale-pricing-table .table-responsive tr,
    .wholesale-pricing-table .table-responsive th,
    .wholesale-pricing-table .table-responsive td {
        display: revert !important;
    }

    @media (max-width: 575.98px) {
        .wholesale-pricing-table .table th,
        .wholesale-pricing-table .table td {
            padding: 6px 8px;
            font-size: 13px;
        }
        .wholesale-pricing-table .table th {
            font-size: 12px;
            white-space: nowrap;
        }
        .wholesale-pricing-table .ws-price-discounted {
            font-size: 13px;
        }
        .wholesale-pricing-table .ws-price-discounted + del {
            display: block;
            font-size: 11px;
            margin-left: 0 !important;
        }
        .wholesale-pricing-table .badge {
            font-size: 10px;
            padding: 3px 5px;
        }
        .wholesale-pricing-table .ws-savings {
            font-size: 12px;
        }
    }
</style>
<div class="wholesale-pricing-table wholesale-pricing-table--{{ $style }} my-4" data-wholesale-box="1">
    <div class="ws-header d-flex align-items-center">
        @if($showIcon)
            <x-core::icon :name="$icon" class="me-2" />
        @endif
        <h5 class="mb-0">{{ trans('plugins/ecommerce-wholesale::wholesale.pricing_table.title') }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>{{ trans('plugins/ecommerce-wholesale::wholesale.pricing_table.quantity') }}</th>
                    <th>{{ trans('plugins/ecommerce-wholesale::wholesale.pricing_table.price_per_unit') }}</th>
                    <th>{{ trans('plugins/ecommerce-wholesale::wholesale.pricing_table.discount') }}</th>
                    @if($showSavings && $displayMode !== 'compact')
                        <th>{{ trans('plugins/ecommerce-wholesale::wholesale.pricing_table.savings') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($pricingTiers as $tier)
                <tr data-min="{{ $tier['min_qty'] }}"
                    data-max="{{ $tier['max_qty'] ?? 999999 }}"
                    data-price="{{ $tier['price'] }}"
                    data-price-formatted="{{ format_price($tier['price']) }}">
                    <td>
                        <strong>{{ $tier['quantity_range'] }}</strong>
                    </td>
                    <td>
                        <span class="ws-price-discounted">{{ format_price($tier['price']) }}</span>
                        @if($showOriginalPrice && $tier['price'] < $basePrice)
                            <del class="text-muted ms-2 small">{{ format_price($basePrice) }}</del>
                        @endif
                    </td>
                    <td>
                        @if($tier['type'] === 'percentage')
                            <span class="badge ws-badge">{{ $tier['discount'] }}% {{ trans('plugins/ecommerce-wholesale::wholesale.pricing_table.off') }}</span>
                        @elseif($tier['type'] === 'fixed')
                            <span class="badge ws-badge">-{{ format_price($tier['discount']) }}</span>
                        @else
                            <span class="badge ws-badge">{{ trans('plugins/ecommerce-wholesale::wholesale.pricing_table.fixed_price') }}</span>
                        @endif
                    </td>
                    @if($showSavings && $displayMode !== 'compact')
                        <td>
                            @if($tier['savings'] > 0)
                                <span class="ws-savings">
                                    {{ trans('plugins/ecommerce-wholesale::wholesale.pricing_table.save') }} {{ format_price($tier['savings']) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="small text-muted mt-2 mb-0 px-3 pb-3">
        <x-core::icon name="ti ti-info-circle" class="me-1" />
        {{ trans('plugins/ecommerce-wholesale::wholesale.pricing_table.note') }}
    </p>
</div>
<script>
(function() {
    var table = document.querySelector('.wholesale-pricing-table table');
    if (!table) return;

    var showOriginalPrice = @json($showOriginalPrice);
    var showSavings = @json($showSavings && $displayMode !== 'compact');
    var saveLabel = @json(trans('plugins/ecommerce-wholesale::wholesale.pricing_table.save'));
    var tiersEndpoint = @json(route('public.wholesale.tiers', ['productId' => '__ID__']));

    var tiers = [];
    table.querySelectorAll('tbody tr').forEach(function(row) {
        tiers.push({
            min: parseInt(row.dataset.min),
            max: parseInt(row.dataset.max),
            formattedPrice: row.dataset.priceFormatted,
            row: row
        });
    });
    if (!tiers.length) return;

    var priceSelectors = '[data-bb-value="product-price"], .bb-product-price-text, .tp-product-details-price.new-price, .product-price-sale, .price-current';

    function findTier(qty) {
        for (var i = 0; i < tiers.length; i++) {
            if (qty >= tiers[i].min && qty <= tiers[i].max) {
                return tiers[i];
            }
        }
        return null;
    }

    function updateTableFromServer(serverTiers) {
        for (var i = 0; i < tiers.length && i < serverTiers.length; i++) {
            var t = tiers[i];
            var s = serverTiers[i];

            t.formattedPrice = s.formattedPrice;
            t.row.dataset.price = s.price;
            t.row.dataset.priceFormatted = s.formattedPrice;

            var priceCell = t.row.querySelector('.ws-price-discounted');
            if (priceCell) priceCell.textContent = s.formattedPrice;

            var origPriceDel = t.row.querySelector('del');
            if (showOriginalPrice) {
                if (origPriceDel) {
                    origPriceDel.textContent = s.formattedBasePrice;
                    origPriceDel.style.display = s.savings > 0 ? '' : 'none';
                }
            }

            if (showSavings) {
                var savingsCell = t.row.cells[t.row.cells.length - 1];
                if (savingsCell) {
                    if (s.savings > 0) {
                        savingsCell.innerHTML = '<span class="ws-savings">' + saveLabel + ' ' + s.formattedSavings + '</span>';
                    } else {
                        savingsCell.innerHTML = '<span class="text-muted">-</span>';
                    }
                }
            }
        }
    }

    function updatePrice(qty) {
        var tier = findTier(qty);

        tiers.forEach(function(t) {
            t.row.classList.remove('ws-tier-active');
        });
        if (tier) {
            tier.row.classList.add('ws-tier-active');
        }

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

    function onVariantChanged(response) {
        if (!response || !response.data || response.data.error_message) return;

        var variantId = response.data.id;
        if (!variantId) return;

        var url = tiersEndpoint.replace('__ID__', variantId);

        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.tiers || !data.tiers.length) return;

                updateTableFromServer(data.tiers);

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

    var qtyInputSelectors = 'input[name="qty"], .tp-cart-input, .qty-input, .product-quantity input[type="number"]';
    var qtyBtnSelectors = '[data-bb-toggle="decrease-qty"], [data-bb-toggle="increase-qty"], .tp-cart-plus, .tp-cart-minus, .qty-btn, .quantity-btn';

    function getQtyValue() {
        var input = document.querySelector(qtyInputSelectors);
        return input ? (parseInt(input.value) || 1) : 1;
    }

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
@endif
