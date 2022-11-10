<header class="header--mobile">
    <div class="header__left">
        <button class="ps-drawer-toggle"><i class="icon-menu"></i></button>
    </div>
    <div class="header__center">
        <a class="ps-logo" href="{{ route('marketplace.vendor.dashboard') }}">
            @php $logo = theme_option('logo_vendor_dashboard', theme_option('logo')); @endphp
            @if ($logo)
                <img src="{{ RvMedia::getImageUrl($logo) }}" alt="{{ theme_option('site_title') }}">
            @endif
        </a>
    </div>
    <div class="header__right"><a class="header__site-link" href="{{ route('customer.logout') }}"><i class="icon-exit-right"></i></a></div>
</header>
<aside class="ps-drawer--mobile">
    <div class="ps-drawer__header">
        <h4>Menu</h4>
        <button class="ps-drawer__close"><i class="icon-cross"></i></button>
    </div>
    <div class="ps-drawer__content">
        @include(MarketplaceHelper::viewPath('dashboard.layouts.menu'))
    </div>
</aside>
<div class="ps-site-overlay"></div>
<main class="ps-main">
    <div class="ps-main__sidebar">
        <div class="ps-sidebar">
            <div class="ps-sidebar__top">
                <div class="ps-block--user-wellcome">
                    <div class="ps-block__left">
                        <img src="{{ auth('customer')->user()->store->logo_url }}" alt="{{ auth('customer')->user()->store->name }}" width="80" />
                    </div>
                    <div class="ps-block__right">
                        <p>{{ __('Hello') }}, {{ auth('customer')->user()->name }} <img class="verified-store-info" style="max-width: 15px;" src="{{ asset('/storage/stores/verified.png')}}"alt="Verified" /></p>
                        <small>{{ __('Joined on :date', ['date' => auth('customer')->user()->created_at->translatedFormat('M d, Y')]) }}</small>
                        <br>
                        @if(auth('customer')->user()->store->is_verified)
                            <span style="position: absolute;"><small class="badge bg-success">{{ __('You are verified now') }}</small></span>
                        @endif
                    </div>
                    <div class="ps-block__action"><a href="{{ route('customer.logout') }}"><i class="icon-exit"></i></a></div>
                </div>
                <div class="ps-block--earning-count"><small>{{ __('Earnings') }}</small>
                    <h3>{{ format_price(auth('customer')->user()->balance) }}</h3>
                </div>
            </div>
            <div class="ps-sidebar__content">
                <div class="ps-sidebar__center">
                    @include(MarketplaceHelper::viewPath('dashboard.layouts.menu'))
                </div>
                <div class="ps-sidebar__footer">
                    <div class="ps-copyright">
                        @php $logo = theme_option('logo_vendor_dashboard', theme_option('logo')); @endphp
                        @if ($logo)
                            <img src="{{ RvMedia::getImageUrl($logo)}}" alt="{{ theme_option('site_title') }}" height="40">
                        @endif
                        <p>{{ theme_option('copyright') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="ps-main__wrapper" id="vendor-dashboard">
        <header class="header--dashboard">
            <div class="header__left">
                <h3>{{ page_title()->getTitle(false) }}</h3>
                @if(auth('customer')->user()->store->is_verified)
                    <div class="alert alert-warning d-flex align-items-center mt-4" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                        <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </symbol>
                        <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                        </symbol>
                        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </symbol>
                        </svg>
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Warning:"><use xlink:href="#exclamation-triangle-fill"/></svg>
                        <p class="m-0">{{ __('You are not verified vendor') }} <a style="font-weight: bold" href="https://www.google.com">{{ __('Click here') }}</a> {{ __('to get verified!') }}</p>
                    </div>
                @endif
            </div>
            @if (auth('customer')->user()->store && auth('customer')->user()->store->id)
                <div class="header__right">
                    @if (is_plugin_active('language'))
                        <div class="me-4">
                            @include(MarketplaceHelper::viewPath('dashboard.partials.language-switcher'))
                        </div>
                    @endif
                    <a class="header__site-link ms-2" href="{{ auth('customer')->user()->store->url }}" target="_blank"><span>{{ __('View your store') }}</span><i class="icon-exit-right"></i></a>
                </div>
            @endif
        </header>

        <div id="main">
            @yield('content')
        </div>
    </div>
</main>
