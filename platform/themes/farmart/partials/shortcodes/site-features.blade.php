<div class="widget-site-features py-5">
    <div class="container-xxxl">
        <!--<div class="row align-items-center mb-2 widget-header">
            <h2 class="col-auto mb-0 py-2">{!! BaseHelper::clean($shortcode->title) !!}</h2>
        </div>-->
        <div class="row">
            @for ($i = 1; $i <= 4; $i++)
                @if ($shortcode->{'name_' . $i} && $shortcode->{'subtitle_' . $i} && $shortcode->{'icon_' . $i})
                    <div class="col-sm-3 py-2">
                        <div class="site-info__item d-flex align-items-center">
                            <div class="site-info__image me-3">
                                <img class="lazyload" data-src="{{ RvMedia::getImageUrl($shortcode->{'icon_' . $i}), null, false, RvMedia::getDefaultImage() }}" alt="{{ $shortcode->{'name_' . $i} }}">
                            </div>
                            <div class="site-info__content">
                                <div class="site-info__title h4 fw-bold">{{ $shortcode->{'name_' . $i} }}</div>
                                <div class="site-info__desc">{{ $shortcode->{'subtitle_' . $i} }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            @endfor
        </div>
    </div>
</div>
