<section class="section-box py-5 testimonialSection">
    <div class="container-xxxl">
        <div class="row align-items-center mb-2 widget-header">
            <h2 class="col-sm-12 mb-0 py-2">{!! BaseHelper::clean($shortcode->title) !!}</h2>
            <p class="text-body-lead-large color-gray-600 mt-30">{!! BaseHelper::clean($shortcode->subtitle) !!}</p>
        </div>
    </div>
    <div class="container mt-70">
        <div class="row">
            @foreach ($testimonials as $testimonial)
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="card-grid-style-3 hover-up wow animate__animated animate__fadeIn" data-wow-delay=".{{ $loop->iteration * 2 - 1 }}s">
                        <div class="grid-3-img">
                            <img src="{{ RvMedia::getImageUrl($testimonial->image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $testimonial->name }}">
                        </div>
                        <h6 class="text-heading-6 mb-2 mt-20">{{ $testimonial->name }}</h6>
                        <span class="text-body-small d-block">{{ $testimonial->company }}</span>
                        <div class="text-body-text text-desc color-gray-500 mt-20">{!! BaseHelper::clean($testimonial->content) !!}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
