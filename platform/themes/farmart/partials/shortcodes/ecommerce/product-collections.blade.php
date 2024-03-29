<div class="widget-products-with-category py-5 pt-3 bg-light">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-0 py-2">{{ $shortcode->title }}</h2>
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
                        @foreach ($products as $product)
                            <div class="product-inner">
                                {!! Theme::partial('ecommerce.product-item', compact('product', 'wishlistIds')) !!}
                            </div>
                        @endforeach
                    </div>
                    <div class="arrows-wrapper"></div>
                </div>
            </div>
        </div>
    </div>
</div>
