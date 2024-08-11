<form
    class="cart-form"
    action="{{ route('public.cart.add-to-cart') }}"
    method="POST"
>
    @csrf
    @if (!empty($withVariations) && $product->variations()->count())
        <div class="pr_switch_wrap">
            {!! render_product_swatches($product, [
                'selected' => $selectedAttrs,
            ]) !!}
        </div>
        <div
            class="number-items-available"
            style="display: none; margin-bottom: 10px;"
        ></div>
    @endif

    @if (isset($withProductOptions) && $withProductOptions)
        {!! render_product_options($product) !!}
    @endif

    <input
        class="hidden-product-id"
        name="id"
        type="hidden"
        value="{{ $product->is_variation || !$product->defaultVariation->product_id ? $product->id : $product->defaultVariation->product_id }}"
    />

    @if (EcommerceHelper::isCartEnabled() || !empty($withButtons))
        {!! apply_filters(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, null, $product) !!}
        <div class="product-button  @if ($product->is_enquiry == 1) is_enquiry @endif">
        @if($product->is_enquiry == 1)
                <a href="{{ route('public.enquiry.get',$product->id) }}" class="btn btn-primary btn-black mb-2 " title="{{ __('Enquiry Now') }}">
                    <span class="add-to-cart-text ms-2">{{ __('Enquiry Now') }}</span>
                </a>
            @else
                @if (EcommerceHelper::isCartEnabled())
                    {!! Theme::partial('ecommerce.product-quantity', compact('product')) !!}
                    <button
                        class="btn btn-primary mb-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                        name="add_to_cart"
                        type="submit"
                        value="1"
                        title="{{ __('Add to cart') }}"
                        @if ($product->isOutOfStock()) disabled @endif
                    >
                        <span class="svg-icon">
                            <svg>
                                <use
                                    href="#svg-icon-cart"
                                    xlink:href="#svg-icon-cart"
                                ></use>
                            </svg>
                        </span>
                        <span class="add-to-cart-text ms-2">{{ __('Add to cart') }}</span>
                    </button>

                    @if (EcommerceHelper::isQuickBuyButtonEnabled() && isset($withBuyNow) && $withBuyNow)
                        <button
                            class="btn btn-primary btn-black mb-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                            name="checkout"
                            type="submit"
                            value="1"
                            title="{{ __('Buy Now') }}"
                            @if ($product->isOutOfStock()) disabled @endif
                        >
                            <span class="add-to-cart-text ms-2">{{ __('Buy Now') }}</span>
                        </button>
                    @endif
                    @if($product->minimum_order_quantity > 0)
                    <p>Minimun Order Qty {{ $product->minimum_order_quantity}}</p>
                    @endif
                    @if($product->maximum_order_quantity > 0)
                        <p>Maxmum Order Qty {{ $product->minimum_order_quantity}}</p>
                    @endif
                @endif
                @if (!empty($withButtons))
                    {!! Theme::partial('ecommerce.product-loop-buttons', compact('product', 'wishlistIds')) !!}
                @endif
            @endif
            
           
        </div>
    @endif
</form>

