<li class="mini-cart-item row g-0">
    <div class="col-3">
        <div class="product-image">
            <a
                class="img-fluid-eq"
                href="{{ $product->original_product->url }}"
            >
                <div class="img-fluid-eq__dummy"></div>
                <div class="img-fluid-eq__wrap">
                    <img
                        class="lazyload"
                        data-src="{{ RvMedia::getImageUrl(Arr::get($cartItem->options, 'image', $product->original_product->image), 'thumb', false, RvMedia::getDefaultImage()) }}"
                        alt="{{ $product->original_product->name }}"
                    >
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
                @php $store = $product->original_product->store; @endphp
                <div class="product-vendor">
                    <a class="text-primary ms-1 d-inline" href="{{ $store->url }}">
                        {{ $store->name }}
                    </a>
                    @if($store->is_verified)
                        <img class="verified-store" src="{{ asset('/storage/stores/verified.png')}}" alt="Verified" style="max-height: 14px; vertical-align: middle;">
                    @endif
                    @if($store->shop_category)
                        @php
                            $shopCategory = $store->shop_category;
                            $categoryLabel = ($shopCategory instanceof \Botble\Marketplace\Enums\ShopTypeEnum) 
                                ? $shopCategory->label() 
                                : (\Botble\Marketplace\Enums\ShopTypeEnum::getLabel($shopCategory) ?: $shopCategory);
                        @endphp
                        <small class="badge bg-warning text-dark" style="font-size: 9px; padding: 1px 3px;">{{ $categoryLabel }}</small>
                    @endif
                </div>
            @endif
            <span class="quantity">
                <span class="price-amount amount">
                    <bdi>{{ format_price($cartItem->price) }} @if ($product->front_sale_price != $product->price)
                            <small><del>{{ format_price($product->price) }}</del></small>
                        @endif
                    </bdi>
                </span>
                ({{ __('x:quantity', ['quantity' => $cartItem->qty]) }})
            </span>
            <p class="mb-0">
                <small>{{ Arr::get($cartItem->options, 'attributes', '') }}</small>
            </p>
            @if (EcommerceHelper::isEnabledProductOptions() && !empty($cartItem->options['options']))
                {!! render_product_options_html($cartItem->options['options'], $product->front_sale_price_with_taxes) !!}
            @endif

            @include(
                EcommerceHelper::viewPath('includes.cart-item-options-extras'),
                ['options' => $cartItem->options]
            )

            {!! apply_filters('ecommerce_cart_after_item_content', null, $cartItem) !!}
        </div>
    </div>
    <div class="col-2">
        <a
            class="btn remove-cart-item"
            data-url="{{ route('public.cart.remove', $cartItem->rowId) }}"
            href="#"
            aria-label="{{ __('Remove this item') }}"
        >
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-trash"
                        xlink:href="#svg-icon-trash"
                    ></use>
                </svg>
            </span>
        </a>
    </div>
</li>
