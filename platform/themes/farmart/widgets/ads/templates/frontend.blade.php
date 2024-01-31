@php
    $slick = [
        'rtl' => BaseHelper::isRtlEnabled(),
        'appendArrows' => '.arrows-wrapper',
        'arrows' => false,
        'dots' => true,
        'autoplay' => 'yes',
        'infinite' => 'no',
        'autoplaySpeed' => 3000,
        'speed' => 800,
        'slidesToShow' => 1,
        'slidesToScroll' => 1,
    ];

    // Manually convert the array into a JSON string
    $slickOptions = json_encode($slick);
@endphp

@if (is_plugin_active('ads'))
    @if (is_array($config['ads_key']) && count($config['ads_key']) > 0)
        <div id="ad-widget-sidbar" class="mb-5 ad-widget slick-slides-carousel" data-slick='{{ $slickOptions }}'>
            @foreach ($config['ads_key'] as $adKey)
                @php
                    $image = display_ads_advanced($adKey, ['class' => 'd-flex justify-content-center']);
                    if (!$image) continue;
                @endphp
                <div class="slick-slide">
                    <div class="lazyload" @if ($config['background']) data-bg="{{ RvMedia::getImageUrl($config['background']) }}" @endif>
                        @php
                            $size = 'xxxl';
                            switch ($config['size']) {
                                case 'large':
                                    $size = 'xxl';
                                    break;
                                case 'medium':
                                    $size = 'lg';
                                    break;
                            }
                        @endphp
                        <div class="container-{{ $size }}">
                            <div class="row">
                                <div class="my-4">
                                    {!! $image !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endif
