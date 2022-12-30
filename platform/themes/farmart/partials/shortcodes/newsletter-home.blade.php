@if (is_plugin_active('newsletter'))
<div class="widget-site-newsletter newsletter mb-15 wow animate__ animate__fadeIn animated">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="position-relative newsletter-inner" style="background-image:url('{{ RvMedia::getImageUrl($shortcode->{'bgimg'}), null, false, RvMedia::getDefaultImage() }}')">
                    <div class="newsletter-content">
                        <h2 class="mb-20">{!! BaseHelper::clean($shortcode->title) !!}</h2>
                        <p class="mb-45">{!! BaseHelper::clean($shortcode->subtitle) !!}</p>
                        <form class="subscribe-form" method="POST" action="{{ route('public.newsletter.subscribe') }}">
                            <div class="form-subscribe d-flex">
                                <input type="email" name="email" placeholder="{{ __('Your email...') }}">
                                <button class="btn" type="submit">{{ __('Subscribe') }}</button>
                            </div>
                            <div class="col-auto">
                                @if (setting('enable_captcha') && is_plugin_active('captcha'))
                                    <div class="form-group">
                                        {!! Captcha::display() !!}
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                    <img class="lazyload" data-src="{{ RvMedia::getImageUrl($shortcode->{'nlimg'}), null, false, RvMedia::getDefaultImage() }}" alt="{{ $shortcode->{'title'} }}" />
                </div>
            </div>
        </div>
    </div>
</div>
@endif
