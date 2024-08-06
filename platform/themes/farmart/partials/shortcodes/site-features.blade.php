@php
    $checkHomePage = false;
    $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName();
    if ($currentRoute === 'public.index') {
        $checkHomePage = true;
    }
    $quantity = $shortcode->quantity;
@endphp
<div class="widget-site-features @if($checkHomePage) py-5 @else pt-5 mt-5 @endif border-top">
    <div class="container-xxxl">
        @if(!empty($shortcode->title))
        <div class="row align-items-center mb-2 widget-header">
            <h2 class="col-auto mb-0 py-2">{!! BaseHelper::clean($shortcode->title) !!}</h2>
        </div>
        @endif
        <div class="row row-cols-lg-4 row-cols-sm-2 row-cols-2 justify-content-center g-1">
            @foreach(range(1, $shortcode->quantity) as $i)
                <div class="col py-2">
                    <div class="site-info__item d-flex align-items-center">
                        @if($icon = $shortcode->{"icon_$i"})
                            <div class="site-info__image me-3">
                                <img class="lazyload" data-src="{{ RvMedia::getImageUrl($icon) }}" alt="{{ $shortcode->{"title_$i"} }}">
                            </div>
                        @endif
                        <div class="site-info__content">
                            <div class="site-info__title h4 fw-bold">{{ $shortcode->{"title_$i"} }}</div>
                            <div class="site-info__desc">{!! BaseHelper::clean(nl2br($shortcode->{"subtitle_$i"})) !!}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
