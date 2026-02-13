@php
    /**
     * Lightweight price display for bundles.
     * We intentionally avoid deep coupling with eCommerce price helpers because versions vary.
     */
    $bundle = $bundle ?? null;
    $label = $bundle?->type === 'mix'
        ? trans('plugins/product-bundles::bundles.front.price_from')
        : trans('plugins/product-bundles::bundles.front.bundle_price');

    $priceText = null;

    if ($bundle) {
        try {
            $priceText = pb_format_price(pb_bundle_total_price($bundle));
        } catch (Throwable $e) {
            $priceText = null;
        }
    }
@endphp

<div class="product-price">
    <span class="text-muted">{{ $label }}</span>
    <span class="fw-bold">
        {{ $priceText ?: trans('plugins/product-bundles::bundles.front.view_details_for_price') }}
    </span>
</div>
