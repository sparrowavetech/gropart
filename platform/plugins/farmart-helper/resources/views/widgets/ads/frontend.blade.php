@if (is_plugin_active('ads'))
    @php
        $ads = [];
        foreach ((array) ($config['ads_key'] ?? []) as $key) {
            if ($key && ($ad = AdsManager::displayAds($key, ['class' => 'd-flex justify-content-center']))) {
                $ads[] = $ad;
            }
        }
        $size = match ($config['size'] ?? '') {
            'large' => 'xxl',
            'medium' => 'lg',
            default => 'xxxl',
        };
        $slider = count($ads) > 1;
        $options = [
            'rtl' => BaseHelper::isRtlEnabled(),
            'arrows' => false,
            'dots' => true,
            'autoplay' => true,
            'infinite' => false,
            'autoplaySpeed' => 3000,
            'speed' => 800,
            'slidesToShow' => 1,
            'slidesToScroll' => 1,
        ];
    @endphp
    @if ($ads)
        <div class="lazyload" @if (!empty($config['background'])) data-bg="{{ RvMedia::getImageUrl($config['background']) }}" @endif>
            <div class="ad-widget-sidebar mb-4 {{ $slider ? 'slick-slides-carousel' : '' }}" @if ($slider) data-slick='@json($options)' @endif>
                @foreach ($ads as $ad)
                    <div class="container-{{ $size }}">
                        <div class="row"><div class="my-4">{!! $ad !!}</div></div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
