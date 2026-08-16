@php
    use Botble\MobileCommandBar\Supports\MobileCommandBarHelper;

    $columns = [];
    if (! empty($s['enable_home'])) { $columns[] = '48px'; }
    if (! empty($s['enable_menu'])) { $columns[] = '48px'; }
    $columns[] = 'minmax(0,1fr)';
    if (! empty($s['enable_profile'])) { $columns[] = '48px'; }
    if (! empty($s['enable_cart'])) { $columns[] = '48px'; }

    $shadow = ! empty($s['enable_shadow']) ? $s['shadow_value'] : 'none';
    $supportSelectors = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $s['support_selectors']))));
@endphp

<link rel="stylesheet" href="{{ mcb_asset('frontend/css/frontend.css') }}">
<style id="mcb-inline-style">
:root{
    --mcb-z:{{ (int) $s['z_index'] }};
    --mcb-side:{{ (int) $s['side_offset'] }}px;
    --mcb-bottom:{{ (int) $s['bottom_offset'] }}px;
    --mcb-height:{{ (int) $s['bar_height'] }}px;
    --mcb-radius:{{ (int) $s['bar_radius'] }}px;
    --mcb-item-radius:{{ (int) $s['item_radius'] }}px;
    --mcb-icon:{{ (int) $s['icon_size'] }}px;
    --mcb-blur:{{ (int) $s['blur'] }}px;
    --mcb-body-padding:{{ (int) $s['body_padding'] }}px;
    --mcb-accent:{{ $s['accent'] }};
    --mcb-accent-dark:{{ $s['accent_dark'] }};
    --mcb-bg:{{ $s['light_background'] }};
    --mcb-bg-dark:{{ $s['dark_background'] }};
    --mcb-text:{{ $s['light_text'] }};
    --mcb-text-dark:{{ $s['dark_text'] }};
    --mcb-muted:{{ $s['light_muted'] }};
    --mcb-muted-dark:{{ $s['dark_muted'] }};
    --mcb-border:{{ $s['light_border'] }};
    --mcb-border-dark:{{ $s['dark_border'] }};
    --mcb-item-bg:{{ $s['item_background_light'] }};
    --mcb-item-bg-dark:{{ $s['item_background_dark'] }};
    --mcb-shadow:{{ $shadow }};
    --mcb-support-open-z:{{ (int) $s['support_open_z_index'] }};
}
@media (max-width:{{ (int) $s['breakpoint'] }}px){body{padding-bottom:calc(var(--mcb-body-padding) + env(safe-area-inset-bottom));}}
@media (min-width:{{ (int) $s['breakpoint'] + 1 }}px){.mcb-wrapper,.mcb-overlay,.mcb-toast{display:none!important;}}
@if(empty($s['show_labels']))
.mcb-nav-label{display:none!important;}
@endif
@if(! empty($s['custom_css']))
{!! $s['custom_css'] !!}
@endif
</style>

<div id="mcb-wrapper" class="mcb-wrapper" dir="{{ in_array(app()->getLocale(), ['fa', 'ar', 'he', 'ur']) ? 'rtl' : 'ltr' }}" style="--mcb-grid:{{ implode(' ', $columns) }};">
    <nav class="mcb-command-bar" aria-label="{{ trans('plugins/mobile-command-bar::mobile-command-bar.name') }}">

        @if(! empty($s['enable_home']))
            <a href="{{ $s['home_url'] }}" class="mcb-nav-item">
                <span class="mcb-nav-icon"><img src="{{ MobileCommandBarHelper::iconUrl($s, 'home_icon') }}" alt="{{ $s['home_label'] }}" loading="eager" decoding="async"></span>
                <span class="mcb-nav-label">{{ $s['home_label'] }}</span>
            </a>
        @endif

        @if(! empty($s['enable_menu']))
            <button type="button" class="mcb-nav-item mcb-open-menu">
                <span class="mcb-nav-icon"><img src="{{ MobileCommandBarHelper::iconUrl($s, 'menu_icon') }}" alt="{{ $s['menu_label'] }}" loading="eager" decoding="async"></span>
                <span class="mcb-nav-label">{{ $s['menu_label'] }}</span>
            </button>
        @endif

        @if($action['type'] === 'link')
            <a href="{{ $action['url'] }}" class="mcb-main-action" data-action="link">
        @else
            <button type="button" class="mcb-main-action" data-action="{{ $action['type'] }}">
        @endif

            <span class="mcb-main-icon">{!! MobileCommandBarHelper::svg($action['icon']) !!}</span>
            <span class="mcb-main-copy">
                <strong>{{ $action['label'] }}</strong>
                <small>{{ $action['note'] }}</small>
            </span>
            <span class="mcb-main-arrow">{!! MobileCommandBarHelper::svg('arrow') !!}</span>

        @if($action['type'] === 'link')
            </a>
        @else
            </button>
        @endif

        @if(! empty($s['enable_profile']))
            <button type="button" class="mcb-nav-item mcb-open-profile">
                <span class="mcb-nav-icon"><img src="{{ MobileCommandBarHelper::iconUrl($s, 'profile_icon') }}" alt="{{ $s['profile_label'] }}" loading="eager" decoding="async"></span>
                <span class="mcb-nav-label">{{ $s['profile_label'] }}</span>
            </button>
        @endif

        @if(! empty($s['enable_cart']))
            <a href="{{ $cartUrl }}" class="mcb-nav-item mcb-cart-item">
                <span class="mcb-nav-icon">
                    <img src="{{ MobileCommandBarHelper::iconUrl($s, 'cart_icon') }}" alt="{{ $s['cart_label'] }}" loading="eager" decoding="async">
                    @if(! empty($s['show_cart_badge']))
                        <span class="mcb-cart-badge {{ 0 === $cartCount ? 'is-empty' : '' }}">{{ $cartCount }}</span>
                    @endif
                </span>
                <span class="mcb-nav-label">{{ $s['cart_label'] }}</span>
            </a>
        @endif
    </nav>
</div>

@if(! empty($s['enable_search']))
    <div id="mcb-search-overlay" class="mcb-overlay" aria-hidden="true">
        <button type="button" class="mcb-backdrop" data-close-panel></button>
        <section class="mcb-sheet mcb-search-sheet">
            <span class="mcb-handle"></span>
            <header class="mcb-sheet-head">
                <div><strong>{{ $s['search_label'] }}</strong><span>{{ $s['search_note'] }}</span></div>
                <button type="button" class="mcb-close" data-close-panel>{!! MobileCommandBarHelper::svg('close') !!}</button>
            </header>

            <form action="{{ url('/search') }}" method="get" class="mcb-search-form">
                <span>{!! MobileCommandBarHelper::svg('search') !!}</span>
                <input id="mcb-search-input" type="search" name="q" placeholder="{{ $s['search_placeholder'] }}" autocomplete="off">
                <button type="submit">{{ trans('plugins/mobile-command-bar::mobile-command-bar.sections.smart_search') }}</button>
            </form>

            <div class="mcb-chip-row">
                <a href="{{ $s['shop_url'] }}">{{ $s['search_label'] }}</a>
                <a href="{{ $cartUrl }}">{{ $s['cart_label'] }}</a>
                <a href="{{ $s['account_url'] }}">{{ $s['profile_label'] }}</a>
            </div>
        </section>
    </div>
@endif

@if(! empty($s['enable_menu']))
    <div id="mcb-menu-overlay" class="mcb-overlay" aria-hidden="true">
        <button type="button" class="mcb-backdrop" data-close-panel></button>
        <section class="mcb-sheet mcb-menu-sheet">
            <span class="mcb-handle"></span>
            <header class="mcb-sheet-head">
                <div><strong>{{ $s['menu_label'] }}</strong></div>
                <button type="button" class="mcb-close" data-close-panel>{!! MobileCommandBarHelper::svg('close') !!}</button>
            </header>

            <div class="mcb-menu-grid">
                <a href="{{ $s['home_url'] }}" class="mcb-menu-card">
                    <span><img src="{{ MobileCommandBarHelper::iconUrl($s, 'home_icon') }}" alt="{{ $s['home_label'] }}"></span><strong>{{ $s['home_label'] }}</strong>
                </a>
                <a href="{{ $s['shop_url'] }}" class="mcb-menu-card">
                    <span><img src="{{ MobileCommandBarHelper::iconUrl($s, 'cart_icon') }}" alt=""></span><strong>{{ trans('plugins/mobile-command-bar::mobile-command-bar.sections.products') }}</strong>
                </a>
                <button type="button" class="mcb-menu-card mcb-open-profile">
                    <span><img src="{{ MobileCommandBarHelper::iconUrl($s, 'profile_icon') }}" alt=""></span><strong>{{ trans('plugins/mobile-command-bar::mobile-command-bar.sections.account') }}</strong>
                </button>
                <a href="{{ $cartUrl }}" class="mcb-menu-card">
                    <span><img src="{{ MobileCommandBarHelper::iconUrl($s, 'cart_icon') }}" alt=""></span><strong>{{ $s['cart_label'] }}</strong>
                </a>
                <a href="{{ $s['blog_url'] }}" class="mcb-menu-card">
                    <span>{!! MobileCommandBarHelper::svg('blog') !!}</span><strong>{{ trans('plugins/mobile-command-bar::mobile-command-bar.sections.blog') }}</strong>
                </a>
                @if(! empty($s['enable_search']))
                    <button type="button" class="mcb-menu-card mcb-open-search">
                        <span>{!! MobileCommandBarHelper::svg('search') !!}</span><strong>{{ $s['search_label'] }}</strong>
                    </button>
                @endif
            </div>

            @if(! empty($menuItems))
                <div class="mcb-list-title"></div>
                <div class="mcb-menu-list">
                    @foreach($menuItems as $item)
                        <a href="{{ $item['url'] }}">
                            <span>{{ $item['title'] }}</span>
                            <span>{!! MobileCommandBarHelper::svg('arrow') !!}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endif

@if(! empty($s['enable_profile']))
    <div id="mcb-profile-overlay" class="mcb-overlay" aria-hidden="true">
        <button type="button" class="mcb-backdrop" data-close-panel></button>
        <section class="mcb-sheet mcb-profile-sheet">
            <span class="mcb-handle"></span>
            <header class="mcb-sheet-head">
                <div>
                    <strong>{{ $s['profile_title'] }}</strong>
                    <span>{{ $isLoggedIn ? $s['profile_subtitle_member'] : $s['profile_subtitle_guest'] }}</span>
                </div>
                <button type="button" class="mcb-close" data-close-panel>{!! MobileCommandBarHelper::svg('close') !!}</button>
            </header>

            <div class="mcb-profile-hero">
                <div class="mcb-profile-avatar {{ $profileAvatar ? 'has-real-avatar' : 'is-fallback-icon' }}">
                    @if($profileAvatar)
                        <img src="{{ $profileAvatar }}" alt="{{ $profileName }}">
                    @else
                        <img src="{{ MobileCommandBarHelper::iconUrl($s, 'profile_icon') }}" alt="{{ $profileName }}">
                    @endif
                    <span></span>
                </div>

                <div class="mcb-profile-copy">
                    <strong>{{ $profileName }}</strong>
                    <span>{{ $profileDetail }}</span>
                </div>

                @if($isLoggedIn)<b></b>@endif
            </div>

            @if($isLoggedIn)
                <div class="mcb-profile-actions">
                    <a href="{{ $s['account_url'] }}" class="is-primary">
                        <span><img src="{{ MobileCommandBarHelper::iconUrl($s, 'menu_icon') }}" alt=""></span>
                        <div><strong>{{ trans('plugins/mobile-command-bar::mobile-command-bar.sections.account_url') }}</strong></div>
                        <i>{!! MobileCommandBarHelper::svg('arrow') !!}</i>
                    </a>
                    <a href="{{ $s['downloads_url'] }}">
                        <span><img src="{{ MobileCommandBarHelper::iconUrl($s, 'cart_icon') }}" alt=""></span>
                        <div><strong>{{ trans('plugins/mobile-command-bar::mobile-command-bar.sections.downloads_url') }}</strong></div>
                        <i>{!! MobileCommandBarHelper::svg('arrow') !!}</i>
                    </a>
                    <a href="{{ $s['orders_url'] }}">
                        <span>{!! MobileCommandBarHelper::svg('blog') !!}</span>
                        <div><strong>{{ trans('plugins/mobile-command-bar::mobile-command-bar.sections.orders_url') }}</strong></div>
                        <i>{!! MobileCommandBarHelper::svg('arrow') !!}</i>
                    </a>
                </div>

                <footer class="mcb-profile-footer">
                    <a href="{{ $cartUrl }}">{{ $s['cart_label'] }}@if($cartCount > 0)<b>{{ $cartCount }}</b>@endif</a>
                    <a href="{{ $logoutUrl }}" class="is-danger">{{ $s['logout_label'] }}</a>
                </footer>
            @else
                <div class="mcb-guest-box">
                    <p>{{ $s['guest_message'] }}</p>
                    <a href="{{ $s['login_url'] }}" class="mcb-login-button">
                        <span>{{ $s['login_button_label'] }}</span>
                        <span>{!! MobileCommandBarHelper::svg('arrow') !!}</span>
                    </a>
                    <div>
                        <a href="{{ $s['register_url'] }}">{{ trans('plugins/mobile-command-bar::mobile-command-bar.sections.register_url') }}</a>
                        <a href="{{ $s['shop_url'] }}">{{ $s['search_label'] }}</a>
                        <a href="{{ $cartUrl }}">{{ $s['cart_label'] }}</a>
                    </div>
                </div>
            @endif
        </section>
    </div>
@endif

<div id="mcb-toast" class="mcb-toast"><span>&#10003;</span><strong></strong></div>

<script data-cfasync="false" data-pagespeed-no-defer="1" data-no-optimize="1" data-no-defer="1" data-no-minify="1">
    window.MCB_DATA = {
        addedToCart: @json(trans('plugins/mobile-command-bar::mobile-command-bar.name')),
        copied: @json('Link copied'),
        shareError: @json('Could not share this link'),
        supportIntegration: {{ ! empty($s['support_integration']) ? 'true' : 'false' }},
        supportOpenZIndex: {{ (int) $s['support_open_z_index'] }},
        supportSelectors: @json($supportSelectors)
    };
</script>
<script src="{{ mcb_asset('frontend/js/frontend.js') }}" data-cfasync="false" data-pagespeed-no-defer="1" data-no-optimize="1" data-no-defer="1" data-no-minify="1"></script>
