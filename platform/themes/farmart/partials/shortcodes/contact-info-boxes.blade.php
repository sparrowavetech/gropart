<div class="container-xxxl">
    <div class="row py-5 mt-5">
        <div class="col-md-4">
            <div class="contact-page-info mx-3">
                <h2>{!! BaseHelper::clean($shortcode->title) !!}</h2>
                <div class="fs-5 mt-5 mb-3">{!! BaseHelper::clean($shortcode->subtitle) !!}</div>
                @for ($i = 1; $i <= 3; $i++)
                    @if ($shortcode->{'name_' . $i} && $shortcode->{'address_' . $i})
                        <div class="contact-page-info-item">
                            <small class="fw-bold text-uppercase">{{ $shortcode->{'name_' . $i} }}</small>
                            <div class="fs-5">
                                <p>{{ $shortcode->{'address_' . $i} }}</p>
                                @if ($phone = $shortcode->{'phone_' . $i})
                                    <p><a href="tel:{{ $phone }}">{{ $phone }}</a></p>
                                @endif
                                @if ($email = $shortcode->{'email_' . $i})
                                    <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                                @endif
                            </div>
                        </div>
                    @endif
                @endfor
                @if (theme_option('social_links'))
                <h4 class="mb-3">{{ __('Stay connected:') }}</h4>
                <div class="footer-social-menu">
                    @if (theme_option('social_links'))
                        @foreach(json_decode(theme_option('social_links'), true) as $socialLink)
                            @if (count($socialLink) == 3)
                                @if ((Arr::get($socialLink[0], 'value')) == "Whatsapp" || (Arr::get($socialLink[0], 'value')) == "whatsapp" || (Arr::get($socialLink[0], 'value')) == "WA" || (Arr::get($socialLink[0], 'value')) == "wa" )
                                    <a target="_blank" href="{{ Arr::get($socialLink[2], 'value') }}" title="{{ Arr::get($socialLink[0], 'value') }}" class="wa-link">
                                        <img class="lazyload" data-src="{{ route('public.index') }}/storage/social-icons/wa.png" alt="{{ Arr::get($socialLink[0], 'value') }}" />
                                    </a>
                                @elseif ((Arr::get($socialLink[0], 'value')) == "Facebook" || (Arr::get($socialLink[0], 'value')) == "facebook" || (Arr::get($socialLink[0], 'value')) == "FB" || (Arr::get($socialLink[0], 'value')) == "fb" )
                                    <a target="_blank" href="{{ Arr::get($socialLink[2], 'value') }}" title="{{ Arr::get($socialLink[0], 'value') }}" class="fb-link">
                                        <img class="lazyload" data-src="{{ route('public.index') }}/storage/social-icons/fb.png" alt="{{ Arr::get($socialLink[0], 'value') }}" />
                                    </a>
                                @elseif ((Arr::get($socialLink[0], 'value')) == "Instagram" || (Arr::get($socialLink[0], 'value')) == "instagram" || (Arr::get($socialLink[0], 'value')) == "IG" || (Arr::get($socialLink[0], 'value')) == "ig" )
                                    <a target="_blank" href="{{ Arr::get($socialLink[2], 'value') }}" title="{{ Arr::get($socialLink[0], 'value') }}" class="insta-link">
                                        <img class="lazyload" data-src="{{ route('public.index') }}/storage/social-icons/insta.png" alt="{{ Arr::get($socialLink[0], 'value') }}" />
                                    </a>
                                @elseif ((Arr::get($socialLink[0], 'value')) == "Linkedin" || (Arr::get($socialLink[0], 'value')) == "linkedin" || (Arr::get($socialLink[0], 'value')) == "LI" || (Arr::get($socialLink[0], 'value')) == "li" )
                                    <a target="_blank" href="{{ Arr::get($socialLink[2], 'value') }}" title="{{ Arr::get($socialLink[0], 'value') }}" class="lidin-link">
                                        <img class="lazyload" data-src="{{ route('public.index') }}/storage/social-icons/lidin.png" alt="{{ Arr::get($socialLink[0], 'value') }}" />
                                    </a>
                                @elseif ((Arr::get($socialLink[0], 'value')) == "Twitter" || (Arr::get($socialLink[0], 'value')) == "twitter" || (Arr::get($socialLink[0], 'value')) == "TW" || (Arr::get($socialLink[0], 'value')) == "tw" )
                                    <a target="_blank" href="{{ Arr::get($socialLink[2], 'value') }}" title="{{ Arr::get($socialLink[0], 'value') }}" class="tw-link">
                                        <img class="lazyload" data-src="{{ route('public.index') }}/storage/social-icons/tw.png" alt="{{ Arr::get($socialLink[0], 'value') }}" />
                                    </a>
                                @elseif ((Arr::get($socialLink[0], 'value')) == "Youtube" || (Arr::get($socialLink[0], 'value')) == "youtube" || (Arr::get($socialLink[0], 'value')) == "YT" || (Arr::get($socialLink[0], 'value')) == "yt" )
                                    <a target="_blank" href="{{ Arr::get($socialLink[2], 'value') }}" title="{{ Arr::get($socialLink[0], 'value') }}" class="yt-link">
                                        <img class="lazyload" data-src="{{ route('public.index') }}/storage/social-icons/yt.png" alt="{{ Arr::get($socialLink[0], 'value') }}" />
                                    </a>
                                @else
                                    <a target="_blank" href="{{ Arr::get($socialLink[2], 'value') }}" title="{{ Arr::get($socialLink[0], 'value') }}">
                                        <img class="lazyload" data-src="{{ RvMedia::getImageUrl(Arr::get($socialLink[1], 'value')) }}" alt="{{ Arr::get($socialLink[0], 'value') }}" />
                                    </a>
                                @endif
                            @endif
                        @endforeach
                    @endif
                </div>
                @endif
            </div>
        </div>
        @if ($shortcode->show_contact_form && is_plugin_active('contact'))
            <div class="col-md-8 border-start">
                {!! '[contact-form][/contact-form]' !!}
            </div>
        @endif
    </div>
</div>
