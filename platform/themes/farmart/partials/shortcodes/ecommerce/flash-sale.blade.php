<div class="widget-product-deals-day py-5">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-0 py-2">{!! $shortcode->title ? BaseHelper::clean($shortcode->title) : BaseHelper::clean($flashSale->name) !!}</h2>
                    <div class="ps-4 col-auto py-2 d-none d-md-block">
                        <a href="{{ route('public.products') }}">
                            <span class="link-text">{{ __('All Offers') }}
                                <span class="svg-icon">
                                    <svg>
                                        <use
                                            href="#svg-icon-chevron-right"
                                            xlink:href="#svg-icon-chevron-right"
                                        ></use>
                                    </svg>
                                </span>
                            </span>
                        </a>
                    </div>
                    <div class="countdown-wrapper col-auto ps-md-5 py-2">
                        <div class="header-countdown row align-items-center justify-content-center gx-2">
                            <div class="ends-text col-auto">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="svg-icon">
                                        <i class="icon icon-speed-fast"></i>
                                    </span>{{ __('Expires in') }}:
                                </div>
                            </div>
                            <div
                                class="expire-countdown col-auto"
                                data-expire="{{ Carbon\Carbon::now()->diffInSeconds($flashSale->end_date) }}"
                            >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-deals-day__body arrows-top-right">
                    <div
                        class="product-deals-day-body slick-slides-carousel"
                        data-slick="{{ json_encode([
                            'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                            'appendArrows' => '.arrows-wrapper',
                            'arrows' => true,
                            'dots' => false,
                            'autoplay' => $shortcode->is_autoplay == 'yes',
                            'infinite' => $shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes',
                            'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options())
                                ? $shortcode->autoplay_speed
                                : 3000,
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
                                    'breakpoint' => 1201,
                                    'settings' => [
                                        'slidesToShow' => 5,
                                    ],
                                ],
                                [
                                    'breakpoint' => 1025,
                                    'settings' => [
                                        'arrows' => true,
                                        'dots' => false,
                                        'slidesToShow' => 4,
                                        'slidesToScroll' => 4,
                                    ],
                                ],
                                [
                                    'breakpoint' => 769,
                                    'settings' => [
                                        'arrows' => true,
                                        'dots' => false,
                                        'slidesToShow' => 3,
                                        'slidesToScroll' => 3,
                                    ],
                                ],
                                [
                                    'breakpoint' => 440,
                                    'settings' => [
                                        'arrows' => false,
                                        'dots' => true,
                                        'slidesToShow' => 1,
                                        'slidesToScroll' => 1,
                                    ],
                                ],
                            ],
                        ]) }}"
                    >
                        @foreach ($flashSale->products as $product)
                            @continue(!EcommerceHelper::showOutOfStockProducts() && $product->isOutOfStock())
                            <div class="product-inner">
                                {!! Theme::partial('ecommerce.product-item', compact('product', 'flashSale', 'isFlashSale', 'wishlistIds')) !!}
                            </div>
                        @endforeach
                    </div>
                    <div class="arrows-wrapper"></div>
                </div>
            </div>
        </div>
    </div>
</div>
