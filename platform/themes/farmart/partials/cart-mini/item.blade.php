<li class="mini-cart-item row g-0">
    <div class="col-3">
        <div class="product-image">
            <a class="img-fluid-eq" href="{{ $product->original_product->url }}">
                <div class="img-fluid-eq__dummy"></div>
                <div class="img-fluid-eq__wrap">
                    <img class="lazyload" data-src="{{ Arr::get($cartItem->options, 'image', $product->original_product->image) }}" alt="{{ $product->original_product->name }}">
                </div>
            </a>
        </div>
    </div>
    <div class="col-7">
        <div class="product-content">
            <div class="product-name">
                <a href="{{ $product->original_product->url }}">{{ $product->original_product->name }}</a>
            </div>
            @if (is_plugin_active('marketplace') && $product->original_product->store->id)
                <div class="product-vendor">
                    <a class="text-primary ms-1" href="{{ $product->original_product->store->url }}">
                        {{ $product->original_product->store->name }}
                    </a>
                </div>
            @endif
            <span class="quantity">
                <span class="price-amount amount">
                    <bdi>{{ format_price($cartItem->price) }} @if ($product->front_sale_price != $product->price)
                            <small><del>{{ format_price($product->price) }}</del></small>
                        @endif</bdi>
                </span>
                (x{{ $cartItem->qty }})
            </span>
            <p class="mb-0">
                <small>
                    <small>{{ $cartItem->options['attributes'] ?? '' }}</small>
                </small>
            </p>
            @if (!empty($cartItem->options['options']))
                {!! render_product_options_info($cartItem->options['options'], $product, true) !!}
            @endif

            @if (!empty($cartItem->options['extras']) && is_array($cartItem->options['extras']))
                @foreach($cartItem->options['extras'] as $option)
                    @if (!empty($option['key']) && !empty($option['value']))
                        <p class="mb-0"><small>{{ $option['key'] }}: <strong> {{ $option['value'] }}</strong></small></p>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
    <div class="col-2">
        <a class="btn remove-cart-item" href="#"
            data-url="{{ route('public.cart.remove', $cartItem->rowId) }}"
            aria-label="{{ __('Remove this item') }}">
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-trash" xlink:href="#svg-icon-trash"></use>
                </svg>
            </span>
        </a>
    </div>
</li>
