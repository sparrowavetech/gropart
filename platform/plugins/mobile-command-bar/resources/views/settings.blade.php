@php
    $t = 'plugins/mobile-command-bar::mobile-command-bar.';
    $s = $settings ?? mcb_settings();

    $toggle = function (string $key, string $label, string $desc = '') use ($s) {
        $checked = ! empty($s[$key]) ? 'checked' : '';
        $descHtml = $desc !== '' ? '<small>' . e($desc) . '</small>' : '';

        return <<<HTML
        <label class="mcb-toggle-row">
            <span class="mcb-toggle-copy"><strong>{$label}</strong>{$descHtml}</span>
            <span class="mcb-switch">
                <input type="checkbox" name="mcb[{$key}]" value="1" {$checked}>
                <span></span>
            </span>
        </label>
        HTML;
    };

    $text = function (string $key, string $label, string $type = 'text', string $help = '') use ($s) {
        $value = e($s[$key] ?? '');
        $helpHtml = $help !== '' ? '<small>' . e($help) . '</small>' : '';

        return <<<HTML
        <label class="mcb-field">
            <span>{$label}</span>
            <input type="{$type}" name="mcb[{$key}]" value="{$value}" data-preview="{$key}">
            {$helpHtml}
        </label>
        HTML;
    };

    $textarea = function (string $key, string $label, string $help = '', int $rows = 6, bool $code = false) use ($s) {
        $value = e($s[$key] ?? '');
        $helpHtml = $help !== '' ? '<small>' . e($help) . '</small>' : '';
        $class = $code ? 'mcb-code' : '';
        $dir = $code ? 'dir="ltr"' : '';

        return <<<HTML
        <label class="mcb-field">
            <span>{$label}</span>
            <textarea name="mcb[{$key}]" rows="{$rows}" class="{$class}" {$dir}>{$value}</textarea>
            {$helpHtml}
        </label>
        HTML;
    };

    $range = function (string $key, string $label, int $min, int $max) use ($s) {
        $value = (int) ($s[$key] ?? 0);

        return <<<HTML
        <label class="mcb-field mcb-range-field">
            <span>{$label}</span>
            <div>
                <input type="range" name="mcb[{$key}]" min="{$min}" max="{$max}" value="{$value}" data-range-output="{$key}" data-preview="{$key}">
                <output data-range-value="{$key}">{$value}px</output>
            </div>
        </label>
        HTML;
    };

    $icon = function (string $key, string $label) use ($s) {
        $value = e($s[$key] ?? '');
        $preview = e(\Botble\MobileCommandBar\Supports\MobileCommandBarHelper::iconUrl($s, $key));

        return <<<HTML
        <div class="mcb-icon-field">
            <span>{$label}</span>
            <div class="mcb-icon-picker">
                <span class="mcb-icon-preview"><img src="{$preview}" alt=""></span>
                <input type="url" id="mcb-icon-{$key}" name="mcb[{$key}]" value="{$value}" data-icon-input="{$key}" data-preview="{$key}">
                <button type="button" class="mcb-button secondary" data-media-picker="{$key}">...</button>
            </div>
        </div>
        HTML;
    };
@endphp

@extends(\Botble\Base\Facades\BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <link rel="stylesheet" href="{{ mcb_asset('admin/css/admin.css') }}">

    @if (session('success'))
        <div class="mcb-notice success">{{ trans($t . 'saved') }}</div>
    @endif

    <div class="mcb-app" dir="{{ in_array(app()->getLocale(), ['fa', 'ar', 'he', 'ur']) ? 'rtl' : 'ltr' }}">
        <aside class="mcb-sidebar">
            <div class="mcb-brand">
                <div class="mcb-brand-mark">M</div>
                <div><strong>{{ trans($t . 'name') }}</strong><span>v1.0.0</span></div>
            </div>

            <nav class="mcb-tabs">
                <button type="button" class="is-active" data-tab="general"><span class="mcb-tab-icon">&#9881;</span>{{ trans($t . 'tabs.general') }}</button>
                <button type="button" data-tab="items"><span class="mcb-tab-icon">&#9737;</span>{{ trans($t . 'tabs.items') }}</button>
                <button type="button" data-tab="menu"><span class="mcb-tab-icon">&#9776;</span>{{ trans($t . 'tabs.menu') }}</button>
                <button type="button" data-tab="profile"><span class="mcb-tab-icon">&#128100;</span>{{ trans($t . 'tabs.profile') }}</button>
                <button type="button" data-tab="appearance"><span class="mcb-tab-icon">&#127912;</span>{{ trans($t . 'tabs.appearance') }}</button>
                <button type="button" data-tab="visibility"><span class="mcb-tab-icon">&#128065;</span>{{ trans($t . 'tabs.visibility') }}</button>
                <button type="button" data-tab="advanced"><span class="mcb-tab-icon">&#128295;</span>{{ trans($t . 'tabs.advanced') }}</button>
            </nav>

            <div class="mcb-sidebar-footer">
                <span>Mobile Command Bar</span>
                <a href="https://aryaportal.ir/" target="_blank" rel="noopener">AryaPortal.ir</a>
            </div>
        </aside>

        <main class="mcb-main">
            <header class="mcb-topbar">
                <div>
                    <span class="mcb-eyebrow">{{ trans($t . 'settings_title') }}</span>
                    <h1>{{ trans($t . 'name') }}</h1>
                </div>

                <div class="mcb-top-actions">
                    <button type="button" class="mcb-button secondary" id="mcb-preview-toggle">{{ trans($t . 'preview.title') }}</button>
                    <button type="submit" form="mcb-settings-form" class="mcb-button primary">{{ trans($t . 'save') }}</button>
                </div>
            </header>

            <form id="mcb-settings-form" method="POST" action="{{ route('mobile-command-bar.settings.update') }}">
                @csrf
                @method('PUT')

                <section class="mcb-panel is-active" data-panel="general">
                    <div class="mcb-section-heading">
                        <span>01</span>
                        <div><h2>{{ trans($t . 'tabs.general') }}</h2></div>
                    </div>

                    <div class="mcb-grid two">
                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.status') }}</h3>
                            {!! $toggle('enabled', trans($t . 'sections.enabled'), trans($t . 'sections.enabled_desc')) !!}
                            {!! $toggle('show_labels', trans($t . 'sections.show_labels')) !!}
                            {!! $toggle('show_cart_badge', trans($t . 'sections.show_cart_badge')) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.dimensions') }}</h3>
                            {!! $range('breakpoint', trans($t . 'sections.breakpoint'), 480, 1200) !!}
                            {!! $range('side_offset', trans($t . 'sections.side_offset'), 0, 50) !!}
                            {!! $range('bottom_offset', trans($t . 'sections.bottom_offset'), 0, 80) !!}
                            {!! $range('body_padding', trans($t . 'sections.body_padding'), 0, 180) !!}
                        </div>
                    </div>
                </section>

                <section class="mcb-panel" data-panel="items">
                    <div class="mcb-section-heading">
                        <span>02</span>
                        <div><h2>{{ trans($t . 'tabs.items') }}</h2></div>
                    </div>

                    <div class="mcb-grid two">
                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.home_item') }}</h3>
                            {!! $toggle('enable_home', trans($t . 'sections.show_item')) !!}
                            {!! $text('home_label', trans($t . 'sections.label')) !!}
                            {!! $text('home_url', trans($t . 'sections.url'), 'text') !!}
                            {!! $icon('home_icon', trans($t . 'sections.icon')) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.menu_item') }}</h3>
                            {!! $toggle('enable_menu', trans($t . 'sections.show_item')) !!}
                            {!! $text('menu_label', trans($t . 'sections.label')) !!}
                            {!! $icon('menu_icon', trans($t . 'sections.icon')) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.profile_item') }}</h3>
                            {!! $toggle('enable_profile', trans($t . 'sections.show_item')) !!}
                            {!! $text('profile_label', trans($t . 'sections.label')) !!}
                            {!! $icon('profile_icon', trans($t . 'sections.icon')) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.cart_item') }}</h3>
                            {!! $toggle('enable_cart', trans($t . 'sections.show_item')) !!}
                            {!! $text('cart_label', trans($t . 'sections.label')) !!}
                            {!! $icon('cart_icon', trans($t . 'sections.icon')) !!}
                        </div>
                    </div>
                </section>

                <section class="mcb-panel" data-panel="menu">
                    <div class="mcb-section-heading">
                        <span>03</span>
                        <div><h2>{{ trans($t . 'tabs.menu') }}</h2></div>
                    </div>

                    <div class="mcb-grid two">
                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.smart_search') }}</h3>
                            {!! $toggle('enable_search', trans($t . 'sections.enable_search')) !!}
                            {!! $text('search_label', trans($t . 'sections.search_button_label')) !!}
                            {!! $text('search_note', trans($t . 'sections.search_note')) !!}
                            {!! $text('search_placeholder', trans($t . 'sections.search_placeholder')) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.site_menu') }}</h3>
                            @php($availableMenus = \Botble\MobileCommandBar\Supports\MobileCommandBarHelper::availableMenus())
                            <label class="mcb-field">
                                <span>{{ trans($t . 'sections.menu_used') }}</span>
                                <select name="mcb[menu_id]">
                                    <option value="0">{{ trans($t . 'sections.menu_auto') }}</option>
                                    @foreach ($availableMenus as $menu)
                                        <option value="{{ $menu['id'] }}" @selected((int) $s['menu_id'] === $menu['id'])>{{ $menu['name'] }}</option>
                                    @endforeach
                                </select>
                                @if (empty($availableMenus))
                                    <small>{{ trans($t . 'sections.menu_none_found') }}</small>
                                @endif
                            </label>
                            {!! $text('shop_url', trans($t . 'sections.shop_url')) !!}
                            {!! $text('blog_url', trans($t . 'sections.blog_url')) !!}
                        </div>
                    </div>
                </section>

                <section class="mcb-panel" data-panel="profile">
                    <div class="mcb-section-heading">
                        <span>04</span>
                        <div><h2>{{ trans($t . 'tabs.profile') }}</h2></div>
                    </div>

                    <div class="mcb-grid two">
                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.account_display') }}</h3>
                            {!! $toggle('profile_show_avatar', trans($t . 'sections.show_avatar')) !!}
                            {!! $toggle('profile_show_email', trans($t . 'sections.show_email')) !!}
                            {!! $text('profile_title', trans($t . 'sections.popup_title')) !!}
                            {!! $text('profile_subtitle_member', trans($t . 'sections.member_subtitle')) !!}
                            {!! $text('profile_subtitle_guest', trans($t . 'sections.guest_subtitle')) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.panel_links') }}</h3>
                            {!! $text('login_url', trans($t . 'sections.login_url')) !!}
                            {!! $text('register_url', trans($t . 'sections.register_url')) !!}
                            {!! $text('account_url', trans($t . 'sections.account_url')) !!}
                            {!! $text('downloads_url', trans($t . 'sections.downloads_url')) !!}
                            {!! $text('orders_url', trans($t . 'sections.orders_url')) !!}
                        </div>

                        <div class="mcb-card mcb-card-wide">
                            <h3>{{ trans($t . 'sections.guest_mode') }}</h3>
                            {!! $textarea('guest_message', trans($t . 'sections.guest_message'), '', 4) !!}
                            <div class="mcb-grid two compact">
                                {!! $text('login_button_label', trans($t . 'sections.login_button_label')) !!}
                                {!! $text('logout_label', trans($t . 'sections.logout_label')) !!}
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mcb-panel" data-panel="appearance">
                    <div class="mcb-section-heading">
                        <span>05</span>
                        <div><h2>{{ trans($t . 'tabs.appearance') }}</h2></div>
                    </div>

                    <div class="mcb-grid two">
                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.main_colors') }}</h3>
                            {!! $text('accent', trans($t . 'sections.accent')) !!}
                            {!! $text('accent_dark', trans($t . 'sections.accent_dark')) !!}
                            {!! $text('light_background', trans($t . 'sections.light_background')) !!}
                            {!! $text('dark_background', trans($t . 'sections.dark_background')) !!}
                            {!! $text('light_text', trans($t . 'sections.light_text')) !!}
                            {!! $text('dark_text', trans($t . 'sections.dark_text')) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.dimensions_effects') }}</h3>
                            {!! $range('bar_height', trans($t . 'sections.bar_height'), 54, 110) !!}
                            {!! $range('bar_radius', trans($t . 'sections.bar_radius'), 0, 45) !!}
                            {!! $range('item_radius', trans($t . 'sections.item_radius'), 0, 35) !!}
                            {!! $range('icon_size', trans($t . 'sections.icon_size'), 16, 50) !!}
                            {!! $range('blur', trans($t . 'sections.blur'), 0, 40) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.light_dark_details') }}</h3>
                            {!! $text('light_muted', trans($t . 'sections.light_muted')) !!}
                            {!! $text('dark_muted', trans($t . 'sections.dark_muted')) !!}
                            {!! $text('light_border', trans($t . 'sections.light_border')) !!}
                            {!! $text('dark_border', trans($t . 'sections.dark_border')) !!}
                            {!! $text('item_background_light', trans($t . 'sections.item_background_light')) !!}
                            {!! $text('item_background_dark', trans($t . 'sections.item_background_dark')) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.shadow') }}</h3>
                            {!! $toggle('enable_shadow', trans($t . 'sections.enable_shadow')) !!}
                            {!! $text('shadow_value', trans($t . 'sections.shadow_value')) !!}
                        </div>
                    </div>
                </section>

                <section class="mcb-panel" data-panel="visibility">
                    <div class="mcb-section-heading">
                        <span>06</span>
                        <div><h2>{{ trans($t . 'tabs.visibility') }}</h2></div>
                    </div>

                    <div class="mcb-grid two">
                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.visibility_rules') }}</h3>
                            {!! $toggle('hide_on_product', trans($t . 'sections.hide_on_product')) !!}
                            {!! $toggle('hide_on_shop', trans($t . 'sections.hide_on_shop')) !!}
                            {!! $toggle('hide_on_product_cat', trans($t . 'sections.hide_on_product_cat')) !!}
                            {!! $toggle('hide_on_product_tag', trans($t . 'sections.hide_on_product_tag')) !!}
                            {!! $toggle('show_logged_in', trans($t . 'sections.show_logged_in')) !!}
                            {!! $toggle('show_logged_out', trans($t . 'sections.show_logged_out')) !!}
                        </div>

                        <div class="mcb-card mcb-card-wide">
                            <h3>{{ trans($t . 'sections.excluded_paths') }}</h3>
                            {!! $textarea('excluded_paths', trans($t . 'sections.excluded_paths'), trans($t . 'sections.excluded_paths_help'), 7) !!}
                        </div>

                        <div class="mcb-card mcb-card-wide">
                            <h3>{{ trans($t . 'sections.support_integration') }}</h3>
                            {!! $toggle('support_integration', trans($t . 'sections.support_integration'), trans($t . 'sections.support_integration_desc')) !!}

                            <div class="mcb-grid two compact">
                                {!! $text('support_open_z_index', trans($t . 'sections.support_open_z_index'), 'number', trans($t . 'sections.support_open_z_index_help')) !!}
                            </div>

                            {!! $textarea('support_selectors', trans($t . 'sections.support_selectors'), trans($t . 'sections.support_selectors_help'), 10, true) !!}
                        </div>
                    </div>
                </section>

                <section class="mcb-panel" data-panel="advanced">
                    <div class="mcb-section-heading">
                        <span>07</span>
                        <div><h2>{{ trans($t . 'tabs.advanced') }}</h2></div>
                    </div>

                    <div class="mcb-grid two">
                        <div class="mcb-card mcb-card-wide">
                            <h3>{{ trans($t . 'sections.custom_css') }}</h3>
                            {!! $textarea('custom_css', '', '', 12, true) !!}
                        </div>

                        <div class="mcb-card">
                            <h3>{{ trans($t . 'sections.tools') }}</h3>
                            <div class="mcb-tool-actions">
                                <button type="button" class="mcb-button secondary" id="mcb-export-settings">{{ trans($t . 'sections.export') }}</button>
                                <label class="mcb-button secondary">{{ trans($t . 'sections.import') }}<input type="file" id="mcb-import-settings" accept="application/json" hidden></label>
                            </div>
                        </div>

                        <div class="mcb-card danger">
                            <h3>{{ trans($t . 'sections.danger_zone') }}</h3>
                            <p>{{ trans($t . 'sections.danger_zone_desc') }}</p>
                            <button type="button" class="mcb-button danger" id="mcb-reset-trigger">{{ trans($t . 'reset') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </main>

        <aside class="mcb-preview-pane">
            <div class="mcb-preview-head">
                <div><strong>{{ trans($t . 'preview.title') }}</strong><span>{{ trans($t . 'preview.subtitle') }}</span></div>
                <button type="button" id="mcb-preview-mode">{{ trans($t . 'preview.dark') }}</button>
            </div>

            <div class="mcb-phone">
                <div class="mcb-phone-notch"></div>
                <div class="mcb-phone-screen">
                    <div class="mcb-demo-content"><span></span><span></span><span></span><span></span></div>

                    <div class="mcb-preview-bar">
                        <div class="mcb-preview-item" data-preview-item="home"><img src="{{ \Botble\MobileCommandBar\Supports\MobileCommandBarHelper::iconUrl($s, 'home_icon') }}" alt=""><span>{{ $s['home_label'] }}</span></div>
                        <div class="mcb-preview-item" data-preview-item="menu"><img src="{{ \Botble\MobileCommandBar\Supports\MobileCommandBarHelper::iconUrl($s, 'menu_icon') }}" alt=""><span>{{ $s['menu_label'] }}</span></div>

                        <div class="mcb-preview-action">
                            <span class="mcb-tab-icon">&#128269;</span>
                            <div><strong>{{ $s['search_label'] }}</strong><small>{{ $s['search_note'] }}</small></div>
                        </div>

                        <div class="mcb-preview-item" data-preview-item="profile"><img src="{{ \Botble\MobileCommandBar\Supports\MobileCommandBarHelper::iconUrl($s, 'profile_icon') }}" alt=""><span>{{ $s['profile_label'] }}</span></div>
                        <div class="mcb-preview-item" data-preview-item="cart"><img src="{{ \Botble\MobileCommandBar\Supports\MobileCommandBarHelper::iconUrl($s, 'cart_icon') }}" alt=""><span>{{ $s['cart_label'] }}</span></div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <form id="mcb-reset-form" method="POST" action="{{ route('mobile-command-bar.settings.reset') }}" hidden>
        @csrf
        @method('POST')
    </form>

    <script>
        window.MCB_ADMIN = {
            confirmReset: @json(trans($t . 'reset_confirm')),
            importError: @json(trans($t . 'sections.import')),
            manualIconPrompt: @json(trans($t . 'sections.icon')),
            lightLabel: @json(trans($t . 'preview.light')),
            darkLabel: @json(trans($t . 'preview.dark')),
            savingLabel: @json(trans($t . 'save'))
        };
    </script>
    <script src="{{ mcb_asset('admin/js/admin.js') }}"></script>
@endsection
