@php
    $slick = [
        'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
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
    $brands = get_featured_brands();
@endphp
<div class="widget-featured-brands pt-5 pb-0">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h3 class="col-auto mb-0">{{ $shortcode->title }}</h3>
                    <div class="ps-4 col-auto d-md-block">
                        <a href="{{ $shortcode->labelurl ?:'' }}">
                            <span class="link-text">{{ $shortcode->labeltitle }} <span class="svg-icon"><svg><use href="#svg-icon-chevron-right" xlink:href="#svg-icon-chevron-right"></use></svg></span></span>
                        </a>
                    </div>
                </div>
                <div class="featured-brands__body arrows-top-right">
                    <div
                        class="featured-brands-body slick-slides-carousel"
                        data-slick="{{ json_encode($slick) }}"
                    >
                        @foreach ($brands as $brand)
                            <div class="featured-brand-item">
                                <div class="brand-item-body mx-2 px-2">
                                    <a
                                        class="py-3"
                                        href="{{ $brand->url }}"
                                    >
                                        <div class="brand__thumb img-fluid-eq">
                                            <div class="img-fluid-eq__dummy"></div>
                                            <div class="img-fluid-eq__wrap">
                                                <img
                                                    class="mx-auto"
                                                    src="{{ RvMedia::getImageUrl($brand->logo, null, false, RvMedia::getDefaultImage()) }}"
                                                    alt="{{ $brand->name }}"
                                                />
                                            </div>
                                        </div>
                                        <div class="brand__text">
                                            <h4 class="h6 fw-bold text-secondary text-uppercase brand__name">
                                                {{ $brand->name }}
                                            </h4>
                                            <div class="h5 fw-bold brand__desc">
                                                {!! BaseHelper::clean(Str::limit($brand->description, 150)) !!}
                                            </div>
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
