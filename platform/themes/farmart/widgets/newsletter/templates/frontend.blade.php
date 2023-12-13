@if (is_plugin_active('newsletter'))
    <div class="col-xl-3">
        <div class="widget mb-5">
            <p class="h4 fw-bold widget-title mb-4">{{ $config['title'] }}</p>
            <div class="widget-description pb-3 mb-4">{{ $config['subtitle'] }}</div>
            <div class="form-widget">
                <form
                    class="subscribe-form"
                    method="POST"
                    action="{{ route('public.newsletter.subscribe') }}"
                >
                    @csrf
                    <div class="form-fields">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="svg-icon">
                                    <svg>
                                        <use
                                            href="#svg-icon-mail"
                                            xlink:href="#svg-icon-mail"
                                        ></use>
                                    </svg>
                                </span>
                            </div>
                            <input
                                class="form-control shadow-none"
                                name="email"
                                type="email"
                                placeholder="{{ __('Your email...') }}"
                            >
                            <button
                                class="btn btn-outline-secondary"
                                type="submit"
                            >{{ __('Subscribe') }}</button>
                        </div>
                        @if (is_plugin_active('captcha') && Captcha::reCaptchaEnabled())
                            <div class="mb-3">
                                {!! Captcha::display() !!}
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
