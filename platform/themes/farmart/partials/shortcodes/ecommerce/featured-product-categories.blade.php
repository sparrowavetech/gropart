@php
$slick = [
    'rtl' => BaseHelper::isRtlEnabled(),
    'appendArrows' => '.arrows-wrapper',
    'arrows' => true,
    'dots' => false,
    'autoplay' => $shortcode->is_autoplay == 'yes',
    'infinite' => $shortcode->infinite == 'yes' || $shortcode->is_infinite == 'yes',
    'autoplaySpeed' => in_array($shortcode->autoplay_speed, theme_get_autoplay_speed_options()) ? $shortcode->autoplay_speed : 3000,
    'speed' => 800,
    'slidesToShow' => 6,
    'slidesToScroll' => 1,
    'swipeToSlide' => true,
    'responsive' => [
        [
            'breakpoint' => 1800,
            'settings' => [
                'slidesToShow' => 6,
            ],
        ],
        [
            'breakpoint' => 1601,
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
                'arrows' => true,
                'dots' => false,
                'slidesToShow' => 2,
                'slidesToScroll' => 2,
            ],
        ],
    ],
];
@endphp
@if ($categories->isNotEmpty())
    <div class="widget-product-categories py-5">
        <div class="container-xxxl">
            <div class="row">
                <div class="col-12">
                    <div class="row align-items-center mb-2 widget-header">
                        <h3 class="col-auto mb-0">{{ $shortcode->title }}</h3>
                        <div class="ps-4 col-auto d-md-block">
                            <a href="{{ $shortcode->labelurl ?:'' }}">
                                <span class="link-text">{{ $shortcode->labeltitle }}
                                    <span class="svg-icon">
                                        <svg>
                                            <use href="#svg-icon-chevron-right" xlink:href="#svg-icon-chevron-right"></use>
                                        </svg>
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="product-categories-body arrows-top-right">
                        <div
                            class="product-categories-box slick-slides-carousel"
                            data-slick="{{ json_encode($slick) }}"
                        >
                            @foreach ($categories as $item)
                                <div class="product-category-item p-3 pb-0">
                                    <div class="category-item-body p-3">
                                        <a
                                            class="d-block"
                                            href="{{ route('public.single', $item->url) }}"
                                        >
                                            <div class="category__thumb img-fluid-eq mb-3">
                                                <div class="img-fluid-eq__dummy"></div>
                                                <div class="img-fluid-eq__wrap">
                                                    <img
                                                        class="mx-auto"
                                                        src="{{ RvMedia::getImageUrl($item->image, 'small', false, RvMedia::getDefaultImage()) }}"
                                                        alt="icon {{ $item->name }}"
                                                    />
                                                </div>
                                            </div>
                                            <div class="category__text text-center py-2 text-truncate">
                                                <h6 class="category__name">{{ $item->name }}</h6>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="arrows-wrapper"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
