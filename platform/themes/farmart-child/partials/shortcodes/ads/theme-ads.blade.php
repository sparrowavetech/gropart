@if ($countAds = count($ads))
    <div class="widget-featured-banners pt-3">
        <div class="container-xxxl">
            <div class="row row-cols-md-{{ $countAds }} row-cols-md-{{ $countAds - 1 > 1 ?: 1 }} row-cols-1 justify-content-center">
                @for ($i = 0; $i < $countAds; $i++)
                    <div class="col">
                        <div class="featured-banner-item img-fluid-eq">
                            <div class="img-fluid-eq__dummy"></div>
                            <div class="img-fluid-eq__wrap">
                                {!! $ads[$i] !!}
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
@endif
