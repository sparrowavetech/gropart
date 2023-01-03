<section class="section-box testimonialSection py-5">
    <div class="container mt-110">
        <div class="row">
            <div class="col-lg-9 col-sm-8 widget-header">
                <h2 class="text-heading-1 mb-10">{!! BaseHelper::clean($shortcode->title) !!}</h2>
                <p class="text-body-lead-large color-gray-600">{!! BaseHelper::clean($shortcode->subtitle) !!}</p>
            </div>
        </div>
    </div>
    <div class="container mt-80">
        <div class="box-swiper">
            <div class="swiper-container swiper-group-4">
                <div class="swiper-wrapper pb-70 pt-5">
                    @foreach ($testimonials as $testimonial)
                        <div class="swiper-slide @if ($loop->first) active @endif">
                            <div class="card-grid-style-3 hover-up bd-bg-{{ Arr::random([6, 9, 10]) }}">
                                <div class="grid-3-img">
                                    <img src="{{ RvMedia::getImageUrl($testimonial->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                        alt="{{ $testimonial->name }}">
                                </div>
                                <h6 class="text-heading-6 mb-2 mt-20">{{ $testimonial->name }}</h6>
                                <span class="text-body-small d-block">{{ $testimonial->company }}</span>
                                <div class="text-body-text text-desc color-gray-500 mt-20">{!! BaseHelper::clean($testimonial->content) !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination swiper-pagination3"></div>
            </div>
            <div class="swiper-button-next swiper-button-next-4"></div>
            <div class="swiper-button-prev swiper-button-prev-4"></div>
        </div>
    </div>
</section>
