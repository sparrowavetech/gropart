@php
$slick =
    [
        'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
        'appendArrows' => '.arrows-wrapper',
        'arrows' => true,
        'dots' => false,
        'autoplay' => 'yes',
        'infinite' => 'no',
        'autoplaySpeed' => 3000,
        'speed' => 800,
        'slidesToShow' => 4,
        'slidesToScroll' => 1,
        'responsive' => [
            [
                'breakpoint' => 1400,
                'settings' => [
                    'slidesToShow' => 4,
                ],
            ],
            [
                'breakpoint' => 1199,
                'settings' => [
                    'slidesToShow' => 4,
                ],
            ],
            [
                'breakpoint' => 1024,
                'settings' => [
                    'slidesToShow' => 2,
                ],
            ],
            [
                'breakpoint' => 767,
                'settings' => [
                    'arrows' => false,
                    'dots' => true,
                    'slidesToShow' => 2,
                    'slidesToScroll' => 1,
                ],
            ],
        ],
    ];
@endphp
<div class="widget-frequently-bought-products pt-2 pb-4 bg-light">
    <div class="container-xxxl rounded-7 bg-white py-4 px-5">
        <div class="row">
            <div class="col-md-8 col-lg-9">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-3">{{ __('Frequently Bought Together')}}</h2>
                </div>
                <div class="frequently-bought-products__body arrows-top-right">
                    <div class="frequently-bought-products-body slick-slides-carousel" data-slick="{{ json_encode($slick) }}">
                        @php
                            $front_sale_price = 0;
                            $price_with_taxes = 0;
                            $front_sale_price_with_taxes = $product->front_sale_price_with_taxes;
                        @endphp
                        <div class="product-inner">
                            <div class="product-thumbnail mb-0">
                                <a class="product-loop__link img-fluid-eq" href="{{ $product->url }}">
                                    <div class="img-fluid-eq__dummy"></div>
                                    <div class="img-fluid-eq__wrap">
                                        <img class="lazyload product-thumbnail__img" src="{{ image_placeholder($product->image) }}" data-src="{{ RvMedia::getImageUrl($product->image, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}" />
                                    </div>
                                </a>
                                <div class="product-details">
                                    <div class="product-content-box">
                                        @if (is_plugin_active('marketplace') && $product->store->id)
                                            <div class="sold-by-meta">
                                                <a href="{{ $product->store->url }}" tabindex="0">{{ $product->store->name }}</a>
                                                @if($product->store->is_verified)
                                                    <img class="verified-store" src="{{ asset('/storage/stores/verified.png')}}"alt="Verified">
                                                @endif
                                                <small class="badge bg-warning text-dark">{{ $product->store->shop_category->label() }}</small>
                                            </div>
                                        @endif
                                        <h3 class="product__title">
                                            <a href="{{ $product->url }}" tabindex="0">{{ $product->name }}</a>
                                        </h3>
                                        <div class="position-absolute fbt-checkbox">
                                            <label class="check-box-container">
                                                <input type="checkbox" checked class="fqtcheckbox" data-price="{{$product->front_sale_price_with_taxes}}" value="{{ $product->id }}" id="product_{{ $product->id }}" />
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>

                                        @if (EcommerceHelper::isReviewEnabled())
                                            <a href="#product-reviews-tab" class="anchor-link">
                                                {!! Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]) !!}
                                            </a>
                                        @endif
                                        {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                                        {!! Theme::partial('ecommerce.product-cart-form',
                            compact('product', 'selectedAttrs') + ['withButtons' => false, 'withVariations' => false, 'wishlistIds' => [], 'withBuyNow' => false,'RemoveAddToCart' => true,'FormId'=>$product->id]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @foreach ($products as $product)
                            <div class="product-inner">
                                <div class="product-thumbnail mb-0">
                                    <a class="product-loop__link img-fluid-eq" href="{{ $product->url }}">
                                        <div class="img-fluid-eq__dummy"></div>
                                        <div class="img-fluid-eq__wrap">
                                            <img class="lazyload mx-auto" src="{{ image_placeholder($product->image) }}" data-src="{{ RvMedia::getImageUrl($product->image, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}" />
                                        </div>
                                    </a>
                                    <div class="product-details">
                                        <div class="product-content-box">
                                            @if (is_plugin_active('marketplace') && $product->store->id)
                                                <div class="sold-by-meta">
                                                    <a href="{{ $product->store->url }}" tabindex="0">{{ $product->store->name }}</a>
                                                    @if($product->store->is_verified)
                                                        <img class="verified-store" src="{{ asset('/storage/stores/verified.png')}}"alt="Verified">
                                                    @endif
                                                    <small class="badge bg-warning text-dark">{{ $product->store->shop_category->label() }}</small>
                                                </div>
                                            @endif
                                            <h3 class="product__title">
                                                <a href="{{ $product->url }}" tabindex="0">{{ $product->name }}</a>
                                            </h3>
                                            <div class="position-absolute fbt-checkbox">
                                                <label class="check-box-container">
                                                    <input type="checkbox" checked class="fqtcheckbox" data-price="{{$product->front_sale_price_with_taxes}}" value="{{ $product->id }}" id="product_{{ $product->id }}" />
                                                    <span class="checkmark"></span>
                                                </label>
                                            </div>
                                            @if (EcommerceHelper::isReviewEnabled())
                                                <a href="#product-reviews-tab" class="anchor-link">
                                                    {!! Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]) !!}
                                                </a>
                                            @endif

                                            {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                                            {!! Theme::partial('ecommerce.product-cart-form',
                                compact('product', 'selectedAttrs') + ['withButtons' => false, 'withVariations' => false, 'wishlistIds' => [], 'withBuyNow' => false,'RemoveAddToCart' => true,'FormId'=>$product->id]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $front_sale_price_with_taxes += $product->front_sale_price_with_taxes;
                            @endphp
                        @endforeach
                    </div>
                    <div class="arrows-wrapper"></div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3">
                <div class="frequently-bought-products__price-box text-center">
                    <h2>{{ __('Combo Price')}}</h2>
                    <p class="product-price">
                        <span class="product-price-original ">
                            <span class="price-amount">
                                <bdi>
                                    <span class="amount comboamount">{{ format_price($front_sale_price_with_taxes) }}</span>
                                </bdi>
                            </span>
                        </span>
                    </p>
                    <button type="button" name="add_to_cart" value="1" class="btn btn-lg btn-primary mb-2 add-to-cart-button" title="{{ __('Add all to cart') }}">
                        <span class="svg-icon">
                            <svg>
                                <use href="#svg-icon-cart" xlink:href="#svg-icon-cart"></use>
                            </svg>
                        </span>
                        <span class="add-to-cart-text ms-2" onclick="addToCartAll()">{{ __('Add all to cart') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
