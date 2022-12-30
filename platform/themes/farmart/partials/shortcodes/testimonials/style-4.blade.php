<section class="section-box py-5 testimonialSection"
    @if ($shortcode->bg_color) style="background-color: {{ $shortcode->bg_color }}" @endif>
    <div class="container">
        <div class="row">
            <div class="col-lg-5 mb-30">
                @if ($shortcode->highlight)
                    <span class="tag-1 @if (!$shortcode->bg_color) bg-6 @endif color-gray-900">{{ $shortcode->highlight }}</span>
                @endif
                <div class="widget-header">
                    <h2>{!! BaseHelper::clean($shortcode->title) !!}</h2>
                    <p class="text-body-lead-large color-gray-600 mt-30">{!! BaseHelper::clean($shortcode->subtitle) !!}</p>
                </div>
                <div class="mt-40">
                    @if ($shortcode->link)
                        <a class="btn btn-default btn-white icon-arrow-right"
                            href="{{ $shortcode->link }}">{{ $shortcode->text_link ?: __('Learn More') }}</a>
                    @endif
                </div>
            </div>
            <div class="col-lg-7">
                <div class="row" data-masonry="{&quot;percentPosition&quot;: true }">
                    @foreach ($testimonials as $testimonial)
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div class="card-grid-style-2 card-square hover-up mb-20">
                                <p class="text-body-text color-gray-600 text-comment">{!! BaseHelper::clean($testimonial->content) !!}</p>
                                <div class="box-img-user">
                                    <div class="img-user img-user-round">
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
