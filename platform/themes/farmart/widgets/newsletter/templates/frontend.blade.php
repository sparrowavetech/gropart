<div class="col-xl-3 col-sm-6">
    @if (is_plugin_active('newsletter'))
        <div class="widget mb-3">
            <h4 class="fw-bold widget-title mb-2">{!! BaseHelper::clean($config['title']) !!}</h4>
            <div class="widget-description mb-2">{!! BaseHelper::clean($config['subtitle']) !!}</div>
            <div class="form-widget">
                <form class="subscribe-form" method="POST" action="{{ route('public.newsletter.subscribe') }}">
                    @csrf
                    <div class="form-fields">
                        <div class="input-group">
                            <div class="input-group-text">
                            <span class="svg-icon">
                                <svg>
                                    <use href="#svg-icon-mail" xlink:href="#svg-icon-mail"></use>
                                </svg>
                            </span>
                            </div>
                            <input class="form-control shadow-none" name="email" type="email" placeholder="{{ __('Your email...') }}">
                            <button class="btn btn-outline-secondary" type="submit">{{ __('Subscribe') }}</button>
                        </div>
                        <!--@if (setting('enable_captcha') && is_plugin_active('captcha'))
                            <div class="form-group">
                                {!! Captcha::display() !!}
                            </div>
                        @endif-->
                    </div>
                </form>
            </div>
        </div>
        @if (theme_option('social_links'))
        <h4 class="mb-2">{{ __('Stay connected:') }}</h4>
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
    @endif
</div>
