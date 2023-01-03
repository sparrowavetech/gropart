@php
    $testimonials->loadMissing('metadata');
@endphp
<section class="section-box py-5 testimonialSection">
    <div class="container mt-120">
        <div class="bdrd-58 box-gray-100">
            <div class="row">
                <div class="col-lg-2 col-sm-1 col-12"></div>
                <div class="col-lg-8 col-sm-10 col-12 widget-header">
                    <h2 class="text-heading-1 color-gray-900">{!! BaseHelper::clean($shortcode->title) !!}</h2>
                    <p class="text-body-lead-large color-gray-600 mt-30">{!! BaseHelper::clean($shortcode->subtitle) !!}</p>
                </div>
                <div class="col-lg-2 col-sm-1 col-12"></div>
            </div>
            <div class="container mt-70">
                <div class="row">
                    @foreach ($testimonials as $testimonial)
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div class="card-grid-style-2 hover-up mb-20">
                                <h5 class="text-heading-5 color-gray-900">{{ $testimonial->getMetadata('title', true) }}</h5>
                                <p class="text-body-text color-gray-600 mt-20 text-comment">{!! BaseHelper::clean($testimonial->content) !!}</p>
                                <div class="box-img-user">
                                    <div class="img-user">
                                        <img src="{{ RvMedia::getImageUrl($testimonial->image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $testimonial->name }}">
                                    </div>
                                    <h4 class="text-body-lead color-gray-900 mb-5">{{ $testimonial->name }}</h4>
                                    <p class="text-body-text-md">{{ $testimonial->company }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
