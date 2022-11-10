@php
$brands = get_all_brands();
@endphp
<div class="widget-featured-brands all-brands py-5">
    <div class="container-xxxl">
        <div class="row">
            <div class="col-12">
                <div class="row align-items-center mb-2 widget-header">
                    <h2 class="col-auto mb-0 py-2">{!! BaseHelper::clean($shortcode->title) !!}</h2>
                </div>
                <div class="featured-brands__body arrows-top-right row row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-1 g-0">
                @foreach ($brands as $brand)
                    <div class="featured-brand-item col">
                        <div class="brand-item-body mx-2 py-2">
                            <a class="py-3" href="{{ $brand->url }}">
                                <div class="brand__thumb mb-2 img-fluid-eq">
                                    <div class="img-fluid-eq__dummy"></div>
                                    <div class="img-fluid-eq__wrap">
                                        <img
                                            class="lazyload mx-auto"
                                            src="{{ image_placeholder($brand->logo) }}"
                                            data-src="{{ RvMedia::getImageUrl($brand->logo, null, false, RvMedia::getDefaultImage()) }}"
                                            alt="{{ $brand->name }}"
                                        />
                                    </div>
                                </div>
                                <div class="brand__text py-1">
                                    <h4 class="h6 fw-bold text-secondary text-uppercase brand__name">
                                        {{ $brand->name }}
                                    </h4>
                                    <div class="h5 fw-bold brand__desc">
                                        <div>
                                            {{ BaseHelper::clean(Str::limit($brand->description, 150)) }}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
