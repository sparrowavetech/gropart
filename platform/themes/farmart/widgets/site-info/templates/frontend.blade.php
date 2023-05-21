@if ($sidebar == 'footer_sidebar')
    <div class="col-xl-3 col-sm-6">
        <div class="widget mb-2 mb-md-0">
            <h5 class="fw-bold widget-title mb-2">{!! BaseHelper::clean($config['name']) !!}</h5>
            <div class="widget-description">{!! BaseHelper::clean($config['about']) !!}</div>
            <ul class="ps-0 mt-3 mb-0">
                @if ($config['phone'])
                    <li class="d-flex">
                        <span class="svg-icon me-2">
                            <svg>
                                <use href="#svg-icon-phone" xlink:href="#svg-icon-phone"></use>
                            </svg>
                        </span>
                        <span>{{ __('Hotline 24/7:') }}
                            <h4 class="m-0"><a href="tel:{{ $config['phone'] }}">{{ $config['phone'] }}</a></h4>
                        </span>
                    </li>
                @endif
                @if ($config['address'])
                    <li class="py-1 d-flex">
                        <span class="svg-icon me-2">
                            <svg>
                                <use href="#svg-icon-home" xlink:href="#svg-icon-home"></use>
                            </svg>
                        </span>
                        <span>{{ $config['address'] }}</span>
                    </li>
                @endif
                @if ($config['email'])
                    <li class="py-1 d-flex">
                        <span class="svg-icon me-2">
                            <svg>
                                <use href="#svg-icon-mail" xlink:href="#svg-icon-mail"></use>
                            </svg>
                        </span>
                        <span><a href="mailto:{{ $config['email'] }}">{{ $config['email'] }}</a></span>
                    </li>
                @endif

                @if ($config['working_time'])
                    <li class="py-1 d-flex">
                        <span class="me-2">
                            <i class="icon-clock3"></i>
                        </span>
                        <span>{{ $config['working_time'] }}</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
@else
    <div class="row bg-light mb-4 g-0">
        <div class="col-12">
            <div class="px-3 py-4">
                <h6 class="fw-bold">{{ __('Hotline Order') }}:</h6>
                @if ($config['working_time'])
                    <p class="text">{{ $config['working_time'] }}</p>
                @endif
                @if ($config['phone'])
                    <h4 class="fw-bold">{{ $config['phone'] }}</h4>
                @endif
            </div>
        </div>
    </div>
@endif
