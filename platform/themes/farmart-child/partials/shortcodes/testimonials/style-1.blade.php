<div class="widget-testimonials py-5 bg-white">
    <div class="container-xxxl">
        <div class="row align-items-center mb-2 widget-header position-relative">
            <div class="col-8">
                <h2 class="mb-1 py-1 fw-bold" style="font-size: 26px; color: #101828;">{!! BaseHelper::clean(data_get($shortcode, 'title') ?: __('Our Happy Customers')) !!}</h2>
                @if (data_get($shortcode, 'subtitle'))
                    <p class="mb-0 text-secondary" style="font-size: 14px; color: #475569;">{!! BaseHelper::clean(data_get($shortcode, 'subtitle')) !!}</p>
                @endif
            </div>
            <div class="col-4 text-end">
                <div class="arrows-wrapper position-relative"></div>
            </div>
        </div>
        <div class="testimonials__body arrows-top-right mt-4">
            <div
                class="testimonials-body slick-slides-carousel"
                data-slick="{{ json_encode([
                    'rtl' => BaseHelper::siteLanguageDirection() == 'rtl',
                    'appendArrows' => '.widget-testimonials .arrows-wrapper',
                    'arrows' => true,
                    'dots' => false,
                    'autoplay' => true,
                    'infinite' => true,
                    'autoplaySpeed' => 4000,
                    'speed' => 800,
                    'slidesToShow' => 4,
                    'slidesToScroll' => 1,
                    'swipeToSlide' => true,
                    'prevArrow' => '<button type="button" class="slick-prev" aria-label="Previous"></button>',
                    'nextArrow' => '<button type="button" class="slick-next" aria-label="Next"></button>',
                    'responsive' => [
                        [
                            'breakpoint' => 1400,
                            'settings' => [
                                'slidesToShow' => 4,
                            ],
                        ],
                        [
                            'breakpoint' => 1199,
                            'settings' => [
                                'slidesToShow' => 3,
                            ],
                        ],
                        [
                            'breakpoint' => 768,
                            'settings' => [
                                'slidesToShow' => 2,
                                'slidesToScroll' => 1,
                            ],
                        ],
                        [
                            'breakpoint' => 480,
                            'settings' => [
                                'slidesToShow' => 1,
                                'slidesToScroll' => 1,
                            ],
                        ],
                    ],
                ]) }}"
            >
                @php
                    $borderColors = ['#bee1e6', '#d1ecfd', '#ddd3fa', '#bee1e6'];
                @endphp
                @foreach ($testimonials as $index => $testimonial)
                    <div class="testimonial-slide-item">
                        <div class="card-grid-style-3" style="border-color: {{ $borderColors[$index % count($borderColors)] }};">
                            <div class="grid-3-img mb-3">
                                @if ($testimonial->image)
                                    <img
                                        src="{{ RvMedia::getImageUrl($testimonial->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                        alt="{{ $testimonial->name }}"
                                        class="rounded-circle"
                                        style="width: 55px; height: 55px; object-fit: cover;"
                                    >
                                @else
                                    <img
                                        src="{{ RvMedia::getDefaultImage() }}"
                                        alt="{{ $testimonial->name }}"
                                        style="width: 48px; height: 48px; object-fit: contain; opacity: 0.5;"
                                    >
                                @endif
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size: 16px; color: #101828; margin-top: 15px;">{{ $testimonial->name }}</h6>
                            <span class="text-secondary d-block mb-3" style="font-size: 13px; color: #64748b;">{{ $testimonial->company }}</span>
                            <div class="text-desc text-muted" style="font-size: 14px; color: #334155; line-height: 1.6; height: 115px; overflow-y: auto;">
                                {!! BaseHelper::clean($testimonial->content) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .widget-testimonials {
        position: relative;
        background-color: #fff;
    }
    .widget-testimonials .testimonial-slide-item {
        padding: 0 12px;
        box-sizing: border-box;
    }
    .widget-testimonials .card-grid-style-3 {
        background: #fff;
        border: 10px solid #bee1e6;
        border-radius: 0;
        padding: 40px 35px 25px;
        position: relative;
        width: 100%;
        min-height: 310px;
        box-sizing: border-box;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .widget-testimonials .card-grid-style-3:hover {
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        transform: translateY(-3px);
    }
    .widget-testimonials .arrows-wrapper {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
    }
    .widget-testimonials .arrows-wrapper button.slick-arrow {
        width: 48px;
        height: 48px;
        background-color: #f2f4f7;
        border: 1px solid #e4e7ec;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.25s ease;
        padding: 0;
        outline: none;
        background-repeat: no-repeat;
        background-position: center;
        background-size: 48px 48px;
    }
    .widget-testimonials .arrows-wrapper button.slick-prev {
        background-image: url("data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 64 64' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='32' cy='32.001' r='31.5' fill='%23F2F4F7' stroke='%23E4E7EC'/%3E%3Cpath d='M36.6314 22.4751L37.548 23.4377C37.8493 23.7712 38 24.1608 38 24.6056C38 25.0595 37.8493 25.4444 37.548 25.7611L31.6088 32L37.5478 38.2388C37.8491 38.5554 37.9998 38.9405 37.9998 39.3942C37.9998 39.8391 37.8491 40.2286 37.5478 40.5622L36.6312 41.5122C36.3218 41.8375 35.951 42 35.5192 42C35.0793 42 34.7127 41.8373 34.4195 41.5123L26.4643 33.1554C26.1549 32.8472 26 32.4623 26 32C26 31.5464 26.1548 31.157 26.4643 30.832L34.4195 22.4751C34.721 22.1584 35.0875 22 35.5192 22C35.9429 22 36.3135 22.1584 36.6314 22.4751Z' fill='%23101828'/%3E%3C/svg%3E");
    }
    .widget-testimonials .arrows-wrapper button.slick-prev:hover {
        background-color: #101828;
        border-color: #101828;
        background-image: url("data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 64 64' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='32' cy='32.001' r='31.5' fill='%23101828' stroke='%23101828'/%3E%3Cpath d='M36.6314 22.4751L37.548 23.4377C37.8493 23.7712 38 24.1608 38 24.6056C38 25.0595 37.8493 25.4444 37.548 25.7611L31.6088 32L37.5478 38.2388C37.8491 38.5554 37.9998 38.9405 37.9998 39.3942C37.9998 39.8391 37.8491 40.2286 37.5478 40.5622L36.6312 41.5122C36.3218 41.8375 35.951 42 35.5192 42C35.0793 42 34.7127 41.8373 34.4195 41.5123L26.4643 33.1554C26.1549 32.8472 26 32.4623 26 32C26 31.5464 26.1548 31.157 26.4643 30.832L34.4195 22.4751C34.721 22.1584 35.0875 22 35.5192 22C35.9429 22 36.3135 22.1584 36.6314 22.4751Z' fill='%23FFFFFF'/%3E%3C/svg%3E");
        transform: scale(1.05);
    }
    .widget-testimonials .arrows-wrapper button.slick-next {
        background-image: url("data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 64 64' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='32' cy='32.001' r='31.5' fill='%23F2F4F7' stroke='%23E4E7EC'/%3E%3Cpath d='M27.3686 41.5269L26.452 40.5643C26.1507 40.2307 26 39.8411 26 39.3964C26 38.9425 26.1507 38.5575 26.452 38.2409L32.3912 32.002L26.4522 25.7632C26.1509 25.4466 26.0002 25.0615 26.0002 24.6078C26.0002 24.1628 26.1509 23.7733 26.4522 23.4397L27.3688 22.4897C27.6782 22.1645 28.049 22.002 28.4808 22.002C28.9207 22.002 29.2873 22.1647 29.5805 22.4897L37.5357 30.8466C37.8451 31.1548 38 31.5397 38 32.002C38 32.4555 37.8452 32.845 37.5357 33.1699L29.5805 41.5268C29.279 41.8435 28.9125 42.002 28.4808 42.002C28.0571 42.002 27.6865 41.8436 27.3686 41.5269Z' fill='%23101828'/%3E%3C/svg%3E");
    }
    .widget-testimonials .arrows-wrapper button.slick-next:hover {
        background-color: #101828;
        border-color: #101828;
        background-image: url("data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 64 64' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='32' cy='32.001' r='31.5' fill='%23101828' stroke='%23101828'/%3E%3Cpath d='M27.3686 41.5269L26.452 40.5643C26.1507 40.2307 26 39.8411 26 39.3964C26 38.9425 26.1507 38.5575 26.452 38.2409L32.3912 32.002L26.4522 25.7632C26.1509 25.4466 26.0002 25.0615 26.0002 24.6078C26.0002 24.1628 26.1509 23.7733 26.4522 23.4397L27.3688 22.4897C27.6782 22.1645 28.049 22.002 28.4808 22.002C28.9207 22.002 29.2873 22.1647 29.5805 22.4897L37.5357 30.8466C37.8451 31.1548 38 31.5397 38 32.002C38 32.4555 37.8452 32.845 37.5357 33.1699L29.5805 41.5268C29.279 41.8435 28.9125 42.002 28.4808 42.002C28.0571 42.002 27.6865 41.8436 27.3686 41.5269Z' fill='%23FFFFFF'/%3E%3C/svg%3E");
        transform: scale(1.05);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initTestimonialSlick() {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.slick !== 'undefined') {
                var $slider = jQuery('.testimonials-body.slick-slides-carousel');
                if ($slider.length && !$slider.hasClass('slick-initialized')) {
                    $slider.slick();
                }
            } else {
                setTimeout(initTestimonialSlick, 100);
            }
        }
        initTestimonialSlick();
    });
</script>
