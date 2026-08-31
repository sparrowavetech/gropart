<div class="container product-detail-container bb-product-detail">
    <div class="row">
        <div class="col-md-6">
            <div class="px-1 py-1 px-lg-5 py-lg-5 py-md-1 my-lg-5 my-md-1 my-2 mb-4">
                <div class="bb-quick-view-gallery-images">
                    @foreach ($productImages as $image)
                        <a href="{{ RvMedia::getImageUrl($image) }}">
                            {{ RvMedia::image($image, $product->name, 'medium') }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="product-modal-entry product-details js-product-content">
                <div class="entry-product-header">
                    <div class="product-header-left">
                        <h2 class="h3 product_title entry-title"><a href="{{ $product->url }}">{{ $product->name }}</a>
                        </h2>
                        <div class="product-entry-meta">
                            @if ($product->brand_id)
                                <p class="mb-0 me-2 pe-2 text-secondary">{{ __('Brand') }}: <a
                                        href="{{ $product->brand->url }}"
                                    >{{ $product->brand->name }}</a></p>
                            @endif

                            @if (EcommerceHelper::isReviewEnabled())
                                <div class="col-auto">
                                    {!! Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]) !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                {!! Theme::partial('ecommerce.product-price', compact('product')) !!}

                @if (is_plugin_active('marketplace') && $product->store_id)
                    <div class="product-meta-sold-by my-2">
                        <span class="d-inline-block">{{ __('Sold By') }}: </span>
                        <a href="{{ $product->store->url }}">
                            {{ $product->store->name }}
                        </a>
                        @vendorBadges($product->store)
                    </div>
                @endif

                {!! Theme::partial('ecommerce.product-availability', compact('product', 'productVariation')) !!}

                <div class="product-details__short-description">
                    {!! apply_filters('ecommerce_before_product_description', null, $product) !!}
                    {!! BaseHelper::clean($product->description) !!}
                    {!! apply_filters('ecommerce_after_product_description', null, $product) !!}
                </div>
                @php
                    $wishlistIds = \Theme\Farmart\Supports\Wishlist::getWishlistIds([$product->id]);
                @endphp
                {!! Theme::partial(
                    'ecommerce.product-cart-form',
                    compact('product', 'wishlistIds', 'selectedAttrs') + [
                        'withButtons' => true,
                        'withQuantity' => true,
                    ]
                ) !!}
                <div class="meta-sku @if (! $product->sku) d-none @endif">
                    <span class="meta-label">{{ __('SKU') }}:</span>
                    <span class="meta-value">{{ $product->sku }}</span>
                </div>
                @if ($product->categories->isNotEmpty())
                    <div class="meta-categories">
                        <span class="meta-label">{{ __('Categories') }}:</span>
                        @foreach ($product->categories as $category)
                            <a href="{{ $category->url }}">{{ $category->name }}</a>@if (! $loop->last),@endif
                        @endforeach
                    </div>
                @endif
                @if ($product->tags->isNotEmpty())
                    <div class="meta-categories">
                        <span class="meta-label">{{ __('Tags') }}:</span>
                        @foreach ($product->tags as $tag)
                            <a href="{{ $tag->url }}">{{ $tag->name }}</a>@if (! $loop->last),@endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
