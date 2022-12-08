@if (count($ads))
    <div class="widget-featured-banners py-5 theme-short-ad">
        <div class="container-xxxl">
            @if (count($ads)<=2)
                <div class="row row-cols-md-2 row-cols-1 justify-content-center">
            @elseif (count($ads)==3)
                <div class="row row-cols-md-3 row-cols-1 justify-content-center">
            @else
                <div class="row row-cols-md-4 row-cols-1 justify-content-center">
            @endif
                @for($i = 0; $i < count($ads); $i++)
                    <div class="col">
                        <div class="featured-banner-item img-fluid-eq my-2">
                            <div class="img-fluid-eq__dummy"></div>
                            <div class="img-fluid-eq__wrap">
                                {!! BaseHelper::clean($ads[$i]) !!}
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
@endif
