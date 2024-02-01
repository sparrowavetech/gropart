@php AdminBar::setIsDisplay(false); @endphp
<div class="container-fluid p-0 coming-soon-page">
    <div class="row g-0 h-100">
        <div class="col-md-6">
            <div class="d-flex justify-content-center h-100 align-items-center">
                <div class="px-3 px-xl-5 pb-5 mb-5">
                    <h1 class="mb-4">{{ $shortcode->title }}</h1>
                    <p class="mb-4">{{ $shortcode->subtitle }}</p>
                    @if ($shortcode->time)
                        <div class="countdown-wrapper mt-3">
                            <div
                                class="expire-countdown"
                                data-expire="{{ now()->diffInSeconds($shortcode->time) }}"
                            >

                            </div>
                        </div>
                    @endif
                    @if (theme_option('social_links'))
                        <div class="footer-socials mt-5">
                            <h5 class="me-3 mb-0">{!! BaseHelper::clean($shortcode->social_title) !!}:</h5>
                            <div class="footer-socials-container mt-3">
                                <ul class="ps-0 mb-0">
                                    @foreach (json_decode(theme_option('social_links'), true) as $socialLink)
                                        @if (count($socialLink) == 3)
                                            <li
                                                class="d-inline-block @if (!$loop->first) ps-1 @endif pe-2">
                                                <a
                                                    href="{{ Arr::get($socialLink[2], 'value') }}"
                                                    title="{{ Arr::get($socialLink[0], 'value') }}"
                                                    target="_blank"
                                                >
                                                    <img
                                                        class="lazyload"
                                                        data-src="{{ RvMedia::getImageUrl(Arr::get($socialLink[1], 'value')) }}"
                                                        alt="{{ Arr::get($socialLink[0], 'value') }}"
                                                    />
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    <a class="btn btn-success btn-lg text-white mt-5 fs-6" href="#" id="goBackButton" style="padding: 10px 30px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16" style=" margin-top: -2px; margin-right: 5px;"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"></path></svg> Go Back</a>
                </div>
            </div>
        </div>
        @if ($shortcode->image)
            <div class="col-md-6 d-none d-md-block"><img
                    class="lazyload img-contain h-100 w-100"
                    data-src="{{ RvMedia::getImageUrl($shortcode->image) }}"
                    alt="coming-soon"
                ></div>
        @endif
    </div>
</div>
