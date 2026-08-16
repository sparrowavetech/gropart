<?php

namespace Botble\MobileCommandBar\Supports;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MobileCommandBarHelper
{
    public const SETTING_PREFIX = 'mcb_';

    /**
     * Single source of truth for every option the plugin exposes.
     */
    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'breakpoint' => 782,
            'z_index' => 999999,
            'side_offset' => 10,
            'bottom_offset' => 10,
            'bar_height' => 70,
            'bar_radius' => 24,
            'item_radius' => 18,
            'icon_size' => 29,
            'blur' => 20,
            'body_padding' => 102,
            'show_labels' => true,
            'show_cart_badge' => true,

            'enable_home' => true,
            'enable_menu' => true,
            'enable_search' => true,
            'enable_profile' => true,
            'enable_cart' => true,

            'home_label' => 'Home',
            'menu_label' => 'Menu',
            'profile_label' => 'Account',
            'cart_label' => 'Cart',
            'search_label' => 'Search products',
            'search_note' => 'Smart action',
            'search_placeholder' => 'e.g. wireless headphones...',

            'home_url' => '/',
            'shop_url' => '/products',
            'login_url' => '/login',
            'register_url' => '/register',
            'account_url' => '/customer/overview',
            'downloads_url' => '/customer/downloads',
            'orders_url' => '/customer/orders',
            'blog_url' => '/blog',

            'home_icon' => '',
            'menu_icon' => '',
            'profile_icon' => '',
            'cart_icon' => '',

            'accent' => '#ff6a00',
            'accent_dark' => '#e95700',
            'light_background' => 'rgba(255,255,255,0.94)',
            'dark_background' => 'rgba(24,24,27,0.95)',
            'light_text' => '#171717',
            'dark_text' => '#ffffff',
            'light_muted' => '#777777',
            'dark_muted' => '#a9a9a9',
            'light_border' => 'rgba(20,20,20,0.08)',
            'dark_border' => 'rgba(255,255,255,0.09)',
            'item_background_light' => 'rgba(18,18,18,0.045)',
            'item_background_dark' => 'rgba(255,255,255,0.065)',
            'enable_shadow' => false,
            'shadow_value' => '0 18px 55px rgba(15,18,25,0.16)',

            'hide_on_product' => true,
            'hide_on_shop' => false,
            'hide_on_product_cat' => false,
            'hide_on_product_tag' => false,
            'show_logged_in' => true,
            'show_logged_out' => true,
            'excluded_paths' => "/customer/*\n/admin",
            'menu_id' => 0,

            'support_integration' => true,
            'support_open_z_index' => 5,
            'support_selectors' => implode("\n", [
                '#cc-live-support-popup',
                '.cc-live-support-popup',
                '.cc-support-popup',
                '.cc-support-panel',
                '.cc-support-chat',
                '.cc-chat-popup',
                '.live-support-popup',
                '.support-chat-popup',
                '.support-widget-panel',
                '.chat-widget-panel',
                '[role="dialog"][class*="support"]',
                '[role="dialog"][class*="chat"]',
                '[id*="support"][aria-hidden="false"]',
                '[id*="chat"][aria-hidden="false"]',
                '[class*="support"][aria-hidden="false"]',
                '[class*="chat"][aria-hidden="false"]',
                'iframe[src*="chat"]',
                'iframe[src*="support"]',
            ]),

            'profile_show_email' => true,
            'profile_show_avatar' => true,
            'profile_title' => 'My Account',
            'profile_subtitle_member' => 'Manage your account quickly',
            'profile_subtitle_guest' => 'Sign in to your account',
            'guest_message' => 'After you sign in, your orders and downloads will show up right here.',
            'login_button_label' => 'Sign in or register',
            'logout_label' => 'Log out',

            'custom_css' => '',
        ];
    }

    /**
     * List of keys that are booleans, so validation/sanitizing can treat them consistently.
     */
    public static function booleanKeys(): array
    {
        return [
            'enabled', 'show_labels', 'show_cart_badge', 'enable_home', 'enable_menu',
            'enable_search', 'enable_profile', 'enable_cart', 'enable_shadow',
            'hide_on_product', 'hide_on_shop', 'hide_on_product_cat', 'hide_on_product_tag',
            'show_logged_in', 'show_logged_out', 'profile_show_email', 'profile_show_avatar',
            'support_integration',
        ];
    }

    /**
     * Integer keys mapped to their [min, max] bounds, mirrored in the request rules.
     */
    public static function integerKeys(): array
    {
        return [
            'breakpoint' => [320, 1600],
            'z_index' => [1, 2147483000],
            'side_offset' => [0, 80],
            'bottom_offset' => [0, 120],
            'bar_height' => [54, 120],
            'bar_radius' => [0, 50],
            'item_radius' => [0, 40],
            'icon_size' => [16, 60],
            'blur' => [0, 50],
            'body_padding' => [0, 200],
            'menu_id' => [0, PHP_INT_MAX],
            'support_open_z_index' => [0, 2147483000],
        ];
    }

    public static function urlKeys(): array
    {
        return [
            'home_url', 'shop_url', 'login_url', 'register_url', 'account_url', 'downloads_url',
            'orders_url', 'blog_url', 'home_icon', 'menu_icon', 'profile_icon', 'cart_icon',
        ];
    }

    public static function textKeys(): array
    {
        return [
            'home_label', 'menu_label', 'profile_label', 'cart_label', 'search_label',
            'search_note', 'search_placeholder', 'accent', 'accent_dark', 'light_background',
            'dark_background', 'light_text', 'dark_text', 'light_muted', 'dark_muted',
            'light_border', 'dark_border', 'item_background_light', 'item_background_dark',
            'shadow_value', 'profile_title', 'profile_subtitle_member',
            'profile_subtitle_guest', 'login_button_label', 'logout_label',
        ];
    }

    public static function textareaKeys(): array
    {
        return ['excluded_paths', 'support_selectors', 'guest_message'];
    }

    /**
     * Merge stored settings with defaults, so a fresh install or a
     * partially-populated option table never returns a missing key.
     */
    public static function settings(): array
    {
        $defaults = self::defaults();
        $out = [];
        $booleanKeys = self::booleanKeys();

        foreach ($defaults as $key => $default) {
            $value = self::readSetting($key, $default);

            if (in_array($key, $booleanKeys, true)) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            $out[$key] = $value;
        }

        return $out;
    }

    protected static function readSetting(string $key, mixed $default): mixed
    {
        try {
            if (class_exists(\Botble\Setting\Facades\Setting::class)) {
                $value = \Botble\Setting\Facades\Setting::get(self::SETTING_PREFIX . $key, $default);

                return $value ?? $default;
            }
        } catch (Throwable) {
            // fall through to default below
        }

        return $default;
    }

    /**
     * Turn a raw (already validated) request payload into a clean,
     * fully-typed settings array safe to persist.
     */
    public static function sanitize(array $input): array
    {
        $defaults = self::defaults();
        $out = $defaults;

        foreach (self::booleanKeys() as $key) {
            $out[$key] = ! empty($input[$key]);
        }

        foreach (self::integerKeys() as $key => $bounds) {
            $value = isset($input[$key]) ? (int) $input[$key] : $defaults[$key];
            $out[$key] = max($bounds[0], min($bounds[1], $value));
        }

        foreach (self::urlKeys() as $key) {
            $out[$key] = isset($input[$key]) && $input[$key] !== ''
                ? strip_tags(trim((string) $input[$key]))
                : $defaults[$key];
        }

        foreach (self::textKeys() as $key) {
            $out[$key] = isset($input[$key])
                ? strip_tags(trim((string) $input[$key]))
                : $defaults[$key];
        }

        foreach (self::textareaKeys() as $key) {
            $out[$key] = isset($input[$key])
                ? strip_tags(trim((string) $input[$key]))
                : $defaults[$key];
        }

        $out['custom_css'] = isset($input['custom_css'])
            ? strip_tags((string) $input['custom_css'])
            : '';

        return $out;
    }

    public static function save(array $input): void
    {
        if (! class_exists(\Botble\Setting\Facades\Setting::class)) {
            return;
        }

        $data = self::sanitize($input);

        foreach ($data as $key => $value) {
            \Botble\Setting\Facades\Setting::set(
                self::SETTING_PREFIX . $key,
                is_bool($value) ? (int) $value : $value
            );
        }

        \Botble\Setting\Facades\Setting::save();
    }

    public static function resetToDefaults(): void
    {
        self::save(self::defaults());
    }

    /**
     * Whether the bar should be printed for the current request.
     */
    public static function shouldRender(Request $request): bool
    {
        $settings = self::settings();

        if (empty($settings['enabled'])) {
            return false;
        }

        if (function_exists('is_in_admin') && is_in_admin(true)) {
            return false;
        }

        if ($request->ajax() || $request->wantsJson() || $request->isJson()) {
            return false;
        }

        $isLoggedIn = self::isCustomerLoggedIn();

        if ($isLoggedIn && empty($settings['show_logged_in'])) {
            return false;
        }

        if (! $isLoggedIn && empty($settings['show_logged_out'])) {
            return false;
        }

        if (! empty($settings['hide_on_shop']) && self::isEcommercePageType($request, 'shop')) {
            return false;
        }

        if (! empty($settings['hide_on_product']) && self::isEcommercePageType($request, 'product')) {
            return false;
        }

        if (! empty($settings['hide_on_product_cat']) && self::isEcommercePageType($request, 'category')) {
            return false;
        }

        if (! empty($settings['hide_on_product_tag']) && self::isEcommercePageType($request, 'tag')) {
            return false;
        }

        $path = '/' . ltrim($request->path(), '/');
        $patterns = preg_split('/\r\n|\r|\n/', (string) $settings['excluded_paths']) ?: [];

        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);

            if ($pattern === '') {
                continue;
            }

            $regex = str_replace('\*', '.*', preg_quote($pattern, '~'));

            if (@preg_match('~' . $regex . '~i', $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Best-effort ecommerce page-type detection based on the resolved route
     * name. Botble's ecommerce route names can vary slightly between
     * versions/themes, so this fails safe (returns false) instead of
     * throwing when a route or the ecommerce plugin is not present.
     */
    public static function isEcommercePageType(Request $request, string $type): bool
    {
        try {
            $routeName = (string) ($request->route()?->getName() ?? '');
        } catch (Throwable) {
            return false;
        }

        if ($routeName === '') {
            return false;
        }

        return match ($type) {
            'shop' => str_contains($routeName, 'products') && ! str_contains($routeName, 'detail'),
            'product' => str_contains($routeName, 'products.detail') || str_contains($routeName, 'product.detail') || str_contains($routeName, 'public.single'),
            'category' => str_contains($routeName, 'product-category') || str_contains($routeName, 'products.category'),
            'tag' => str_contains($routeName, 'product-tag') || str_contains($routeName, 'products.tag'),
            default => false,
        };
    }

    public static function isEcommerceActive(): bool
    {
        try {
            return function_exists('is_plugin_active') && is_plugin_active('ecommerce');
        } catch (Throwable) {
            return class_exists(\Botble\Ecommerce\Facades\EcommerceHelper::class);
        }
    }

    public static function isCustomerLoggedIn(): bool
    {
        try {
            if (self::isEcommerceActive() && Auth::guard('customer')->check()) {
                return true;
            }
        } catch (Throwable) {
            // customer guard not configured - ignore
        }

        return false;
    }

    public static function currentCustomer(): mixed
    {
        try {
            if (self::isEcommerceActive()) {
                return Auth::guard('customer')->user();
            }
        } catch (Throwable) {
        }

        return null;
    }

    public static function cartCount(): int
    {
        if (! self::isEcommerceActive()) {
            return 0;
        }

        try {
            if (class_exists(\Botble\Ecommerce\Facades\Cart::class)) {
                return (int) \Botble\Ecommerce\Facades\Cart::instance('cart')->count();
            }
        } catch (Throwable) {
        }

        return 0;
    }

    public static function cartUrl(): string
    {
        try {
            if (function_exists('route') && self::isEcommerceActive()) {
                foreach (['public.cart', 'cart'] as $name) {
                    if (app('router')->has($name)) {
                        return route($name);
                    }
                }
            }
        } catch (Throwable) {
        }

        return url('/cart');
    }

    public static function checkoutUrl(): string
    {
        try {
            if (function_exists('route') && self::isEcommerceActive()) {
                foreach (['public.checkout', 'checkout'] as $name) {
                    if (app('router')->has($name)) {
                        return route($name);
                    }
                }
            }
        } catch (Throwable) {
        }

        return url('/checkout');
    }

    public static function isCartPage(Request $request): bool
    {
        return str_contains((string) ($request->route()?->getName() ?? ''), 'cart');
    }

    public static function isCheckoutPage(Request $request): bool
    {
        return str_contains((string) ($request->route()?->getName() ?? ''), 'checkout');
    }

    /**
     * List of site menus for the settings-page dropdown, as plain
     * [id, name] pairs. Wrapped defensively: if the Menu plugin isn't
     * installed/active, or its model/columns differ from what's
     * expected, this returns an empty array instead of breaking the
     * settings page.
     */
    public static function availableMenus(): array
    {
        if (! class_exists(\Botble\Menu\Models\Menu::class)) {
            return [];
        }

        try {
            return \Botble\Menu\Models\Menu::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get()
                ->map(fn ($menu) => ['id' => (int) $menu->id, 'name' => (string) $menu->name])
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Top-level menu links, pulled from Botble's Menu plugin when available.
     * Fails silently to an empty list so the bar keeps working without it.
     */
    public static function menuItems(int $menuId = 0): array
    {
        if (! class_exists(\Botble\Menu\Models\Menu::class)) {
            return [];
        }

        try {
            $menuModel = \Botble\Menu\Models\Menu::query();

            $menu = $menuId
                ? $menuModel->find($menuId)
                : $menuModel->oldest()->first();

            if (! $menu) {
                return [];
            }

            $nodes = $menu->menuNodes()->whereNull('parent_id')->orderBy('position')->get();

            $items = [];

            foreach ($nodes as $node) {
                $url = method_exists($node, 'getUrl') ? $node->getUrl() : ($node->url ?? '#');

                $items[] = [
                    'title' => strip_tags((string) ($node->name ?? $node->title ?? '')),
                    'url' => $url,
                ];
            }

            return $items;
        } catch (Throwable) {
            return [];
        }
    }

    public static function actionContext(array $settings, Request $request): array
    {
        $action = ! empty($settings['enable_search'])
            ? [
                'type' => 'search',
                'icon' => 'search',
                'label' => $settings['search_label'],
                'note' => $settings['search_note'],
                'url' => '#mcb-search',
            ]
            : [
                'type' => 'link',
                'icon' => 'search',
                'label' => 'View products',
                'note' => 'Go to shop',
                'url' => $settings['shop_url'],
            ];

        if (self::isEcommerceActive() && self::isCartPage($request)) {
            $action = [
                'type' => 'link',
                'icon' => 'checkout',
                'label' => 'Continue to checkout',
                'note' => 'Next step',
                'url' => self::checkoutUrl(),
            ];
        } elseif (self::isEcommerceActive() && self::isCheckoutPage($request)) {
            $action = [
                'type' => 'checkout',
                'icon' => 'checkout',
                'label' => 'Review and place order',
                'note' => 'Final step',
                'url' => '#place_order',
            ];
        }

        return $action;
    }

    public static function svg(string $name): string
    {
        $icons = [
            'search' => '<svg viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.7 4.7"/></svg>',
            'checkout' => '<svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"/><path d="M4 9h16"/><path d="M8 15h4"/></svg>',
            'share' => '<svg viewBox="0 0 24 24"><circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="m8.3 10.8 7.4-4.5M8.3 13.2l7.4 4.5"/></svg>',
            'arrow' => '<svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>',
            'close' => '<svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg>',
            'blog' => '<svg viewBox="0 0 24 24"><path d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M8 9h8M8 13h8M8 17h5"/></svg>',
        ];

        return $icons[$name] ?? $icons['search'];
    }

    public static function fallbackIconUrl(string $key): string
    {
        $map = [
            'home_icon' => 'home.png',
            'menu_icon' => 'app.png',
            'profile_icon' => 'user.png',
            'cart_icon' => 'shopping-bag.png',
        ];

        $file = $map[$key] ?? 'app.png';

        try {
            return mcb_asset('images/' . $file);
        } catch (Throwable) {
            return '';
        }
    }

    public static function iconUrl(array $settings, string $key): string
    {
        return Arr::get($settings, $key) ?: self::fallbackIconUrl($key);
    }

    /**
     * Full view-model handed to the frontend Blade template.
     */
    public static function viewData(Request $request): array
    {
        $settings = self::settings();
        $customer = self::currentCustomer();
        $isLoggedIn = (bool) $customer;

        $profileName = $isLoggedIn
            ? (string) ($customer->name ?? 'Customer')
            : 'Guest';

        $profileDetail = $settings['profile_subtitle_guest'];
        $profileAvatar = '';

        if ($isLoggedIn) {
            $profileDetail = (! empty($settings['profile_show_email']) && ! empty($customer->email))
                ? $customer->email
                : $settings['profile_subtitle_member'];

            if (! empty($settings['profile_show_avatar'])) {
                try {
                    $profileAvatar = (string) ($customer->avatar_url ?? '');
                } catch (Throwable) {
                    $profileAvatar = '';
                }
            }
        }

        return [
            's' => $settings,
            'isLoggedIn' => $isLoggedIn,
            'profileName' => $profileName,
            'profileDetail' => $profileDetail,
            'profileAvatar' => $profileAvatar,
            'cartCount' => self::cartCount(),
            'cartUrl' => self::cartUrl(),
            'action' => self::actionContext($settings, $request),
            'menuItems' => self::menuItems((int) $settings['menu_id']),
            'logoutUrl' => self::logoutUrl(),
        ];
    }

    public static function logoutUrl(): string
    {
        try {
            if (self::isEcommerceActive() && app('router')->has('customer.logout')) {
                return route('customer.logout');
            }
        } catch (Throwable) {
        }

        return url('/');
    }

    /**
     * Compact the rendered front-end fragment for a clean, production-style
     * view-source: collapses the whitespace Blade leaves between tags, and
     * minifies the inline <style>/<script> blocks the same conservative way
     * the shipped CSS/JS assets are minified. Wrapped so that if anything
     * about the markup ever changes shape unexpectedly, minification is
     * simply skipped and the original (still fully functional) HTML is used
     * instead of risking broken output.
     */
    public static function minifyHtmlFragment(string $html): string
    {
        try {
            // Split the fragment on its <style>/<script> blocks, keeping
            // those blocks as their own array entries (PREG_SPLIT_DELIM_CAPTURE).
            // Each piece is then minified appropriately and appended straight
            // to the output - no intermediate placeholder/marker text is
            // used, so there is nothing for any other layer (an HTML
            // minifier, a security filter, etc.) to accidentally corrupt
            // or fail to restore.
            $parts = preg_split(
                '/(<(?:style|script)\b[^>]*>.*?<\/(?:style|script)>)/is',
                $html,
                -1,
                PREG_SPLIT_DELIM_CAPTURE
            );

            if (! $parts) {
                return $html;
            }

            $out = '';

            foreach ($parts as $part) {
                if (preg_match('/^<(style|script)\b([^>]*)>(.*)<\/\1>$/is', $part, $m)) {
                    $tag = strtolower($m[1]);
                    $attrs = $m[2];
                    $body = $tag === 'style'
                        ? self::minifyInlineCss($m[3])
                        : self::minifyInlineJs($m[3]);

                    $out .= "<{$tag}{$attrs}>{$body}</{$tag}>";

                    continue;
                }

                // Plain HTML segment: collapse whitespace between tags
                // (safe here - this fragment has no <pre>/textarea-style
                // elements where whitespace is meaningful) and trim the
                // segment's own edges so no stray blank lines remain at
                // the seams next to the style/script blocks above.
                $part = preg_replace('/>\s+</', '><', $part);
                $out .= trim((string) $part);
            }

            return trim($out);
        } catch (Throwable) {
            return $html;
        }
    }

    protected static function minifyInlineCss(string $css): string
    {
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([{}:;,>~])\s*/', '$1', $css);
        $css = preg_replace('/;}/', '}', $css);

        return trim($css);
    }

    protected static function minifyInlineJs(string $js): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $js) ?: [];
        $out = [];

        foreach ($lines as $line) {
            $line = trim($line);
            $line = preg_replace('/[ \t]{2,}/', ' ', $line);

            if ($line === '' || str_starts_with($line, '//')) {
                continue;
            }

            $out[] = $line;
        }

        $result = '';

        foreach ($out as $line) {
            if ($result !== '') {
                $prev = $result[strlen($result) - 1];
                $cur = $line[0];

                if (preg_match('/[A-Za-z0-9_$]/', $prev) && preg_match('/[A-Za-z0-9_$]/', $cur)) {
                    $result .= ' ';
                }
            }

            $result .= $line;
        }

        return $result;
    }
}
