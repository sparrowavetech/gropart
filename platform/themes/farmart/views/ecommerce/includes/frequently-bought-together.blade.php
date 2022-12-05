@php
$slick = [
'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
'appendArrows' => '.arrows-wrapper',
'arrows' => true,
'dots' => false,
'autoplay' => 'yes',
'infinite' => 'yes',
'autoplaySpeed' => 3000,
'speed' => 800,
'slidesToShow' => 4,
'slidesToScroll' => 1,
'responsive' => [
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
<div class="widget-featured-brands py-5">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-9">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-0 py-2">{{ __('Frequently Bought Together')}}</h2>
                </div>
                <div class="featured-brands__body arrows-top-right">
                    <div class="featured-brands-body slick-slides-carousel" data-slick="{{ json_encode($slick) }}">
                        @php
                        $front_sale_price = 0;
                        $price_with_taxes = 0;
                        $front_sale_price_with_taxes = 0;
                        $front_sale_price_with_taxes = 0;
                        @endphp
                        <div class="featured-brand-item">
                            <div class="brand-item-body mx-2 py-4 px-2">
                                <a class="py-3" href="{{ $product->url }}">
                                    <div class="brand__thumb mb-3 img-fluid-eq">
                                        <div class="img-fluid-eq__dummy"></div>
                                        <div class="img-fluid-eq__wrap">
                                            <img class="lazyload mx-auto" src="{{ image_placeholder($product->image) }}" data-src="{{ RvMedia::getImageUrl($product->image, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}" />
                                        </div>
                                    </div>
                                    <div class="brand__text py-3">
                                        <h4 class="h6 fw-bold text-secondary text-uppercase brand__name">
                                            {{ $product->name }}
                                        </h4>
                                        <div class="h5 fw-bold brand__desc">
                                            <input type="checkbox" checked class="fqtcheckbox" data-price="{{$product->front_sale_price_with_taxes}}" value="{{ $product->id }}" id="product_{{ $product->id }}">
                                        </div>
                                        {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                                        @if (EcommerceHelper::isReviewEnabled())
                                        {!! Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]) !!}
                                        @endif
                                        {!! Theme::partial('ecommerce.product-cart-form',
                            compact('product', 'selectedAttrs') + ['withButtons' => false, 'withVariations' => false, 'wishlistIds' => [], 'withBuyNow' => false,'RemoveAddToCart' => true,'FormId'=>$product->id]) !!}
                                    </div>
                                </a>
                            </div>
                        </div>
                        @foreach ($products as $product)
                        <div class="featured-brand-item">
                            <div class="brand-item-body mx-2 py-4 px-2">
                                <a class="py-3" href="{{ $product->url }}">
                                    <div class="brand__thumb mb-3 img-fluid-eq">
                                        <div class="img-fluid-eq__dummy"></div>
                                        <div class="img-fluid-eq__wrap">
                                            <img class="lazyload mx-auto" src="{{ image_placeholder($product->image) }}" data-src="{{ RvMedia::getImageUrl($product->image, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}" />
                                        </div>
                                    </div>
                                    <div class="brand__text py-3">
                                        <h4 class="h6 fw-bold text-secondary text-uppercase brand__name">
                                            {{ $product->name }}
                                        </h4>
                                        <div class="h5 fw-bold brand__desc">
                                            <input type="checkbox" checked class="fqtcheckbox" data-price="{{$product->front_sale_price_with_taxes}}" value="{{ $product->id }}" id="product_{{ $product->id }}">
                                        </div>
                                        {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                                        @if (EcommerceHelper::isReviewEnabled())
                                        {!! Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]) !!}
                                        @endif
                                        {!! Theme::partial('ecommerce.product-cart-form',
                            compact('product', 'selectedAttrs') + ['withButtons' => false, 'withVariations' => false, 'wishlistIds' => [], 'withBuyNow' => false,'RemoveAddToCart' => true,'FormId'=>$product->id]) !!}
                                    </div>
                                </a>
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
            <div class="col-3">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-0 py-2">{{ __('Combo Price')}}</h2>
                </div>
                <div class="featured-brands__body arrows-top-right">
                    <span class="product-price">
                        <span class="product-price-original ">
                            <span class="price-amount">
                                <bdi>
                                    <span class="amount comboamount">{{ format_price($front_sale_price_with_taxes) }}</span>
                                </bdi>
                            </span>
                        </span>
                    </span>
                    <button type="button" name="add_to_cart" value="1" class="btn btn-primary mb-2 add-to-cart-button " title="{{ __('Add to cart') }}">
                        <span class="svg-icon">
                            <svg>
                                <use href="#svg-icon-cart" xlink:href="#svg-icon-cart"></use>
                            </svg>
                        </span>
                        <span class="add-to-cart-text ms-2" onclick="addToCartAll()">{{ __('Add to cart') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
