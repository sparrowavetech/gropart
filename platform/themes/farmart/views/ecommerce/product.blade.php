@php
    Theme::layout('full-width');
    Theme::set('bodyClass', 'single-product');
@endphp
{!! Theme::partial('page-header', ['size' => 'xxxl']) !!}

<div class="product-detail-container">
    <div class="bg-light py-md-5 px-lg-3 px-2">
        <div class="container-xxxl rounded-7 bg-white py-lg-5 py-md-4 py-3 px-3 px-md-4 px-lg-5">
            <div class="row">
                <div class="col-lg-5 col-md-12">
                    {!! Theme::partial('ecommerce.product-gallery', compact('product', 'productImages')) !!}
                </div>
                <div class="col-lg-4 col-md-12 ps-4 product-details-content">
                    <div class="product-details js-product-content">
                        <div class="entry-product-header">
                            <div class="product-header-left">
                                <h1 class="fs-5 fw-normal product_title entry-title">{{ $product->name }}</h1>
                                @if ($product->categories->isNotEmpty())
                                    <div class="meta-categories">
                                        <span class="meta-label d-inline-block">{{ __('Categories') }}: </span>
                                        @foreach ($product->categories as $category)
                                            <a href="{{ $category->url }}">{{ $category->name }}</a>@if (!$loop->last),@endif
                                        @endforeach
                                    </div>
                                @endif
                                <div class="product-entry-meta">
                                    @if (EcommerceHelper::isReviewEnabled())
                                        <a href="#product-reviews-tab" class="anchor-link">
                                            {!! Theme::partial('star-rating', ['avg' => $product->reviews_avg, 'count' => $product->reviews_count]) !!}
                                        </a>
                                    @endif
                                    @if ($product->brand_id)
                                        <p class="mb-0 me-2 pe-2 text-secondary">{{ __('Brand') }}: <a
                                                href="{{ $product->brand->url }}"
                                            >{{ $product->brand->name }}</a></p>
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
                                @if($product->store->is_verified)
                                    <img class="verified-store-main" src="{{ asset('/storage/stores/verified.png')}}"alt="Verified">
                                @endif
                                <small class="badge bg-warning text-dark">{{ $product->store->shop_category->label() }}</small>
                            </div>
                        @endif

                        <div class="meta-sku @if (!$product->sku) d-none @endif">
                            <span class="meta-label d-inline-block">{{ __('SKU') }}:</span>
                            <span class="meta-value">{{ $product->sku }}</span>
                        </div>

                        <div class="ps-list--dot">
                            {!! apply_filters('ecommerce_before_product_description', null, $product) !!}
                            {!! BaseHelper::clean($product->description) !!}
                            {!! apply_filters('ecommerce_after_product_description', null, $product) !!}
                        </div>

                        {!! Theme::partial('ecommerce.product-availability', compact('product', 'productVariation')) !!}
                        @if (Botble\Ecommerce\Facades\FlashSale::isEnabled() && ($flashSale = $product->latestFlashSales()->first()))
                            <div class="deal-expire-date p-4 bg-light mb-2 mt-4">
                                <div class="row">
                                    <div class="col-xxl-5 d-md-flex justify-content-center align-items-center">
                                        <div class="deal-expire-text mb-2">
                                            <div class="fw-bold text-uppercase">{{ __('Hurry up! Sale end in') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-7">
                                        <div class="countdown-wrapper d-none">
                                            <div class="expire-countdown col-auto" data-expire="{{ Carbon\Carbon::now()->diffInSeconds($flashSale->end_date) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center my-3">
                                    <div class="deal-sold row mt-2">
                                        @if (Botble\Ecommerce\Facades\FlashSale::isShowSaleCountLeft())
                                            <div class="deal-text col-auto">
                                                <span class="sold fw-bold">
                                                    <span class="text">{{ __('Sold') }}: </span>
                                                    <span class="value">{{ $flashSale->sale_count_left_label }}</span>
                                                </span>
                                            </div>
                                        @endif
                                        <div class="deal-progress col">
                                            <div class="progress">
                                                <div
                                                    class="progress-bar"
                                                    role="progressbar"
                                                    aria-valuenow="{{ $flashSale->sale_count_left_percent }}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                    style="width: {{ $flashSale->sale_count_left_percent }}%;"
                                                >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($product->tags->isNotEmpty())
                            <div class="meta-categories mt-4">
                                <span class="meta-label d-inline-block">{{ __('Tags') }}: </span>
                                @foreach ($product->tags as $tag)
                                    <a href="{{ $tag->url }}">{{ $tag->name }}</a>@if (!$loop->last),@endif
                                @endforeach
                            </div>
                        @endif

                        {!! Theme::partial(
                            'ecommerce.product-cart-form',
                            compact('product', 'selectedAttrs', 'productVariation') + [
                                'withButtons' => true,
                                'withVariations' => true,
                                'withProductOptions' => true,
                                'wishlistIds' => \Theme\Farmart\Supports\Wishlist::getWishlistIds([$product->id]),
                                'withBuyNow' => true,
                            ],
                        ) !!}
                        @if (theme_option('social_share_enabled', 'yes') == 'yes')
                            <div class="mt-0">
                                {!! Theme::partial('share-socials', compact('product')) !!}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3 d-block d-sm-block d-md-none d-lg-block d-xl-block">
                    {!! dynamic_sidebar('product_detail_sidebar') !!}
                </div>
            </div>
        </div>
    </div>
    <div class="container-xxxl">
        <div class="row product-detail-tabs mt-3 mb-4">
            <div class="col-md-12 col-lg-3">
                <div
                    class="nav flex-column nav-pills me-3"
                    id="product-detail-tabs"
                    role="tablist"
                    aria-orientation="vertical"
                >
                    <a
                        class="nav-link active"
                        id="product-description-tab"
                        data-bs-toggle="pill"
                        type="button"
                        href="#product-description"
                        role="tab"
                        aria-controls="product-description"
                        aria-selected="true"
                    >
                        {{ __('Description') }}
                    </a>
                    @if (EcommerceHelper::isReviewEnabled())
                        <a
                            class="nav-link"
                            id="product-reviews-tab"
                            data-bs-toggle="pill"
                            type="button"
                            href="#product-reviews"
                            role="tab"
                            aria-controls="product-reviews"
                            aria-selected="false"
                        >
                            {{ __('Reviews') }} ({{ $product->reviews_count }})
                        </a>
                    @endif
                    @if (is_plugin_active('marketplace') && $product->store_id)
                        <a
                            class="nav-link"
                            id="product-vendor-info-tab"
                            data-bs-toggle="pill"
                            type="button"
                            href="#product-vendor-info"
                            role="tab"
                            aria-controls="product-vendor-info"
                            aria-selected="false"
                        >
                            {{ __('Vendor Info') }}
                        </a>
                    @endif
                    @if (is_plugin_active('faq') && count($product->faq_items) > 0)
                        <a
                            class="nav-link"
                            id="product-faqs-tab"
                            data-bs-toggle="pill"
                            type="button"
                            href="#product-faqs"
                            role="tab"
                            aria-controls="product-faqs"
                            aria-selected="false"
                        >
                            {{ __('Questions & Answers') }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="col-md-12 col-lg-9">
                <div
                    class="tab-content"
                    id="product-detail-tabs-content"
                >
                    <div
                        class="tab-pane fade show active"
                        id="product-description"
                        role="tabpanel"
                        aria-labelledby="product-description-tab"
                    >
                        <div class="ck-content">
                            {!! BaseHelper::clean($product->content) !!}
                        </div>

                        {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, null, $product) !!}
                    </div>
                    @if (EcommerceHelper::isReviewEnabled())
                        <div
                            class="tab-pane fade"
                            id="product-reviews"
                            role="tabpanel"
                            aria-labelledby="product-reviews-tab"
                        >
                            @include('plugins/ecommerce::themes.includes.reviews')
                        </div>
                    @endif
                    @if (is_plugin_active('marketplace') && $product->store_id)
                        <div
                            class="tab-pane fade"
                            id="product-vendor-info"
                            role="tabpanel"
                            aria-labelledby="product-vendor-info-tab"
                        >
                            @include(Theme::getThemeNamespace() . '::views.marketplace.includes.info-box', [
                                'store' => $product->store,
                            ])
                        </div>
                    @endif
                    @if (is_plugin_active('faq') && count($product->faq_items) > 0)
                        <div
                            class="tab-pane fade"
                            id="product-faqs"
                            role="tabpanel"
                            aria-labelledby="product-faqs-tab"
                        >
                            @include('plugins/ecommerce::themes.includes.product-faqs', ['faqs' => $product->faq_items])
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="widget-products-with-category pt-4 pb-5 bg-light">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-3 py-2">{{ __('Related products') }}</h2>
                </div>
                <div class="product-deals-day__body arrows-top-right">
                    <div class="product-deals-day-body slick-slides-carousel" data-slick="{{ json_encode([
                            'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                            'appendArrows' => '.arrows-wrapper',
                            'arrows' => true,
                            'dots' => false,
                            'autoplay' => false,
                            'infinite' => false,
                            'autoplaySpeed' => 3000,
                            'speed' => 800,
                            'slidesToShow' => 6,
                            'slidesToScroll' => 1,
                            'swipeToSlide' => true,
                            'responsive' => [
                                [
                                    'breakpoint' => 1400,
                                    'settings' => [
                                        'slidesToShow' => 6,
                                    ],
                                ],
                                [
                                    'breakpoint' => 1199,
                                    'settings' => [
                                        'slidesToShow' => 6,
                                    ],
                                ],
                                [
                                    'breakpoint' => 1024,
                                    'settings' => [
                                        'slidesToShow' => 4,
                                    ],
                                ],
                                [
                                    'breakpoint' => 767,
                                    'settings' => [
                                        'arrows' => true,
                                        'dots' => false,
                                        'slidesToShow' => 2,
                                        'slidesToScroll' => 2,
                                    ],
                                ],
                            ],
                        ]) }}">
                        @foreach (get_related_products($product, 6) as $relatedProduct)
                            <div class="product-inner">
                                {!! Theme::partial('ecommerce.product-item', ['product' => $relatedProduct]) !!}
                            </div>
                        @endforeach
                    </div>
                    <div class="arrows-wrapper"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="sticky-add-to-cart">
    <header class="header--product js-product-content">
        <nav class="navigation">
            <div class="container">
                <article class="ps-product--header-sticky">
                    <div class="ps-product__thumbnail">
                        <img
                            src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}"
                            alt="{{ $product->name }}"
                        >
                    </div>
                    <div class="ps-product__wrapper">
                        <div class="ps-product__content">
                            <span class="ps-product__title">{!! BaseHelper::clean($product->name) !!}</span>
                            <ul>
                                <li class="active"><a href="#product-description-tab">{{ __('Description') }}</a>
                                </li>
                                @if (EcommerceHelper::isReviewEnabled())
                                    <li><a href="#product-reviews-tab">{{ __('Reviews') }}
                                            ({{ $product->reviews_count }})</a></li>
                                @endif
                            </ul>
                        </div>
                        <div class="ps-product__shopping">
                            {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                            @if (EcommerceHelper::isCartEnabled())
                                <button
                                    class="btn btn-primary ms-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                                    name="add_to_cart"
                                    type="button"
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
                                    <span class="add-to-cart-text ms-1">{{ __('Add to cart') }}</span>
                                </button>
                                @if (EcommerceHelper::isQuickBuyButtonEnabled())
                                    <button
                                        class="btn btn-primary btn-black ms-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                                        name="checkout"
                                        type="button"
                                        value="1"
                                        title="{{ __('Buy Now') }}"
                                        @if ($product->isOutOfStock()) disabled @endif
                                    >
                                        <span class="add-to-cart-text">{{ __('Buy Now') }}</span>
                                    </button>
                                @endif

                                <div class="header">
                                    <div class="header-middle" style="border:none">
                                        <div class="header__right" style="width: auto; padding: 0; border: none;">
                                            <div class="header__extra cart--mini" tabindex="0" role="button">
                                                <div class="header__extra">
                                                    <a class="btn-shopping-cart" href="{{ route('public.cart') }}">
                                                        <span class="svg-icon">
                                                            <svg>
                                                                <use href="#svg-icon-cart" xlink:href="#svg-icon-cart"></use>
                                                            </svg>
                                                        </span>
                                                        <span class="header-item-counter">{{ Cart::instance('cart')->count() }}</span>
                                                    </a>
                                                    <span class="cart-text">
                                                        <span class="cart-title">{{ __('Your Cart') }}</span>
                                                        <span class="cart-price-total">
                                                            <span class="cart-amount">
                                                                <bdi>
                                                                    <span>{{ format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax()) }}</span>
                                                                </bdi>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="cart__content" id="cart-mobile">
                                                    <div class="backdrop"></div>
                                                    <div class="mini-cart-content">
                                                        <div class="widget-shopping-cart-content">
                                                            {!! Theme::partial('cart-mini.list') !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        </nav>
    </header>

    <div class="sticky-atc-wrap sticky-atc-shown">
        <div class="container">
            <div class="row">
                <div class="sticky-atc-btn product-button">
                    @if (EcommerceHelper::isCartEnabled())
                        <button
                            class="btn btn-primary mb-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                            name="add_to_cart"
                            type="button"
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
                            <span class="add-to-cart-text ms-1">{{ __('Add to cart') }}</span>
                        </button>

                        @if (EcommerceHelper::isQuickBuyButtonEnabled())
                            <button
                                class="btn btn-primary btn-black mb-2 ms-2 add-to-cart-button @if ($product->isOutOfStock()) disabled @endif"
                                name="checkout"
                                type="button"
                                value="1"
                                title="{{ __('Buy Now') }}"
                                @if ($product->isOutOfStock()) disabled @endif
                            >
                                <span class="add-to-cart-text ms-2">{{ __('Buy Now') }}</span>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
