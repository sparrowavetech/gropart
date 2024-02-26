<div class="row cart-item">
    <div class="col-sm-2 col-3">
        <div class="checkout-product-img-wrapper">
            <img
                class="item-thumb img-thumbnail img-rounded"
                src="{{ RvMedia::getImageUrl(Arr::get($cartItem->options, 'image'), default: RvMedia::getDefaultImage()) }}"
                alt="{{ $product->original_product->name }}"
            >
            <span class="checkout-quantity">{{ $cartItem->qty }}</span>
        </div>
    </div>
    <div class="col-sm-7 col-6">
        <h6 class="mb-0"><a class="fw-bold text-black" href="{{ $product->original_product->url }}" target="_BLANK" title="{{ $product->original_product->name }}">{{ $product->original_product->name }}</a> @if ($product->isOutOfStock()) <span class="stock-status-label">({!! $product->stock_status_html !!})</span> @endif</h6>
        <p class="mb-0">
            <small><em>{{ $product->variation_attributes }}</em></small>
        </p>
        @include('plugins/ecommerce::themes.includes.cart-item-options-extras', [
            'options' => $cartItem->options,
        ])
        @if (!empty($cartItem->options['options']))
            {!! render_product_options_html($cartItem->options['options'], $product->original_price) !!}
        @endif
    </div>
    <div class="col-sm-3 col-3 text-end">
        <p class="price-text">{{ format_price($cartItem->price) }}</p>
    </div>
</div>
