<?php

namespace SparroWave\RazorpayEasyEmi\Providers;

use Botble\Base\Facades\AdminHelper;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Models\Product;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiProductConfig;
use SparroWave\RazorpayEasyEmi\Services\EmiResolutionService;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. Admin Product Edit Meta Box & Save Handlers
        add_action(BASE_ACTION_META_BOXES, [$this, 'addProductMetaBox'], 120, 2);
        add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [$this, 'saveProductEmiConfig'], 120, 3);
        add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [$this, 'saveProductEmiConfig'], 120, 3);

        // 2. Marketplace Vendor Product Meta Box
        if (is_plugin_active('marketplace')) {
            add_action(BASE_ACTION_META_BOXES, [$this, 'addVendorProductMetaBox'], 125, 2);
        }

        // Global Front Storefront Assets Registration (Universal across ANY Botble theme)
        if (defined('THEME_FRONT_HEADER')) {
            add_filter([THEME_FRONT_HEADER, 'ecommerce_checkout_header'], function (?string $html): ?string {
                if (AdminHelper::isInAdmin()) {
                    return $html;
                }

                $cssUrl = asset('vendor/core/plugins/razorpay-easy-emi/css/razorpay-easy-emi.css');
                $v = file_exists(public_path('vendor/core/plugins/razorpay-easy-emi/css/razorpay-easy-emi.css'))
                    ? filemtime(public_path('vendor/core/plugins/razorpay-easy-emi/css/razorpay-easy-emi.css'))
                    : '1.0';

                return ($html ?? '') . "\n" . '<link rel="stylesheet" href="' . $cssUrl . '?v=' . $v . '">';
            }, 125);
        }

        if (defined('THEME_FRONT_FOOTER')) {
            add_filter(THEME_FRONT_FOOTER, function (?string $html): ?string {
                if (AdminHelper::isInAdmin()) {
                    return $html;
                }

                $jsUrl = asset('vendor/core/plugins/razorpay-easy-emi/js/razorpay-easy-emi.js');
                $v = file_exists(public_path('vendor/core/plugins/razorpay-easy-emi/js/razorpay-easy-emi.js'))
                    ? filemtime(public_path('vendor/core/plugins/razorpay-easy-emi/js/razorpay-easy-emi.js'))
                    : '1.0';

                return ($html ?? '') . "\n" . '<script src="' . $jsUrl . '?v=' . $v . '"></script>';
            }, 125);
        }

        // 3. Product Detail Page (PDP) Widget Hook
        // Priority 140 renders below Seller Name & Free Shipping badge (125), and directly above Short Description
        add_filter('ecommerce_before_product_description', [$this, 'renderPdpWidget'], 140, 2);

        // 4. Universal Catalog / Grid Card Badge Hooks (Matches advanced-cod and product-free-shipping patterns)
        add_filter('ecommerce_after_product_price', [$this, 'renderCatalogBadge'], 130, 2);
        add_filter('ecommerce_product_item_badges', [$this, 'renderCatalogBadge'], 130, 2);
        add_filter('ecommerce_product_loop_extra_html', [$this, 'renderCatalogBadge'], 130, 2);
        add_filter('farmart_helper_product_card_extra_html', [$this, 'renderCatalogBadge'], 130, 2);

        // Standard Ecommerce card forms view composer for Farmart and other themes
        $this->app['view']->composer([
            'theme.*::partials.ecommerce.product-cart-form',
            'plugins/farmart-helper::product-cards.*',
        ], function ($view): void {
            $data = $view->getData();
            $product = $data['product'] ?? null;
            $isCard = $product && ! array_key_exists('withVariations', $data)
                && ! array_key_exists('withProductOptions', $data)
                && ! array_key_exists('isQuickShop', $data)
                && ! array_key_exists('withBuyNow', $data);

            request()->attributes->set('razorpay_easy_emi_card_context', $isCard ? (string) ($product->id ?? '') : null);
        });

        add_filter(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, function (mixed $html, mixed $product = null): mixed {
            $id = is_object($product) ? (string) ($product->id ?? '') : '';
            $isCard = request()->attributes->get('farmart_helper_card_context', false)
                || request()->attributes->get('razorpay_easy_emi_card_context') === $id;

            if ($id === '' || ! $isCard || request()->attributes->get('razorpay_easy_emi_native_card_badge') === $id) {
                return $html;
            }

            return $this->renderCatalogBadge((string) $html, $product);
        }, 130, 2);

        add_filter(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, static function (mixed $html): mixed {
            request()->attributes->remove('razorpay_easy_emi_card_context');
            request()->attributes->remove('razorpay_easy_emi_native_card_badge');

            return $html;
        }, 250);

        // 5. Cart Reminder Banner
        add_filter('ecommerce_cart_after_subtotal', [$this, 'renderCartEmiReminder'], 120, 2);
        if (defined('THEME_FRONT_FOOTER')) {
            add_filter(THEME_FRONT_FOOTER, [$this, 'renderUniversalCartBridge'], 120);
        }

        // 6. Checkout Auto-Select & Re-sequencing Bridge
        add_filter('ecommerce_checkout_footer', [$this, 'renderCheckoutBridge'], 120);
    }

    public function addProductMetaBox(string $priority, mixed $model): void
    {
        if (! $model instanceof Product || ! AdminHelper::isInAdmin(true) || request()->is('vendor/*') || request()->is('*/vendor/*')) {
            return;
        }

        MetaBox::addMetaBox(
            'razorpay_easy_emi_meta_box',
            trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.title'),
            function () use ($model) {
                return view('plugins/razorpay-easy-emi::admin.product-metabox', compact('model'))->render();
            },
            get_class($model),
            'advanced',
            'default'
        );
    }

    public function addVendorProductMetaBox(string $priority, mixed $model): void
    {
        if (! $model instanceof Product) {
            return;
        }

        // Strictly prevent loading in CMS Admin panel
        if (AdminHelper::isInAdmin(true)) {
            return;
        }

        // Must strictly be on a vendor route
        if (! request()->is('vendor/*') && ! request()->is('*/vendor/*')) {
            return;
        }

        MetaBox::addMetaBox(
            'razorpay_easy_emi_vendor_meta_box',
            trans('plugins/razorpay-easy-emi::razorpay-easy-emi.vendor.card_title'),
            function () use ($model) {
                return view('plugins/razorpay-easy-emi::vendor.product-request-box', compact('model'))->render();
            },
            get_class($model),
            'advanced',
            'default'
        );
    }

    public function saveProductEmiConfig(string $screen, Request $request, mixed $model): void
    {
        if (! $model instanceof Product || request()->is('vendor/*') || request()->is('*/vendor/*') || ! $request->has('razorpay_emi_status')) {
            return;
        }

        $emiType = $request->input('razorpay_emi_type', 'standard');
        $offerMode = $request->input('razorpay_emi_offer_mode', 'auto');
        $rawOfferIds = $request->input('razorpay_emi_offer_ids');
        $singleOfferId = $request->input('razorpay_emi_offer_id');

        $offerIds = [];
        if ($offerMode === 'custom') {
            if (is_array($rawOfferIds)) {
                $offerIds = array_values(array_filter($rawOfferIds));
            } elseif (! empty($singleOfferId)) {
                $offerIds = [(string) $singleOfferId];
            }

            // Prune incompatible offers according to selected scheme
            if (! empty($offerIds)) {
                $cachedOffers = \SparroWave\RazorpayEasyEmi\Models\RazorpayEmiCachedOffer::query()
                    ->whereIn('id', $offerIds)
                    ->get()
                    ->keyBy('id');

                $offerIds = array_values(array_filter($offerIds, function ($id) use ($cachedOffers, $emiType) {
                    $offer = $cachedOffers->get($id);
                    if (! $offer) {
                        return true;
                    }
                    if ($emiType === 'standard' && ($offer->isNoCostEmi() || $offer->isLowCostEmi())) {
                        return false;
                    }
                    if ($emiType === 'no_cost' && $offer->isLowCostEmi()) {
                        return false;
                    }
                    if ($emiType === 'low_cost' && $offer->isNoCostEmi()) {
                        return false;
                    }

                    return true;
                }));
            }
        }

        RazorpayEmiProductConfig::query()->updateOrCreate(
            ['product_id' => $model->id],
            [
                'status' => $request->input('razorpay_emi_status', 'inherit'),
                'emi_type' => $emiType,
                'offer_mode' => $offerMode,
                'offer_id' => ! empty($offerIds) ? json_encode($offerIds) : null,
                'subvention_payer' => $request->input('razorpay_emi_subvention_payer', 'platform'),
                'custom_badge' => $request->input('razorpay_emi_custom_badge') ?: null,
                'min_amount' => $request->filled('razorpay_emi_min_amount') ? (float) $request->input('razorpay_emi_min_amount') : null,
            ]
        );
    }

    public function renderPdpWidget(?string $html, mixed $product = null): ?string
    {
        if (! $product instanceof Product) {
            return $html;
        }

        $resolutionService = app(EmiResolutionService::class);
        $resolution = $resolutionService->resolveForProduct($product);

        if (! ($resolution['eligible'] ?? false)) {
            return $html;
        }

        // Register assets
        if (class_exists(Theme::class)) {
            Theme::asset()
                ->usePath(false)
                ->add('razorpay-easy-emi-css', asset('vendor/core/plugins/razorpay-easy-emi/css/razorpay-easy-emi.css'));

            Theme::asset()
                ->container('footer')
                ->usePath(false)
                ->add('razorpay-easy-emi-js', asset('vendor/core/plugins/razorpay-easy-emi/js/razorpay-easy-emi.js'));
        }

        $widgetHtml = view('plugins/razorpay-easy-emi::front.widget', compact('resolution'))->render();

        return ($html ?? '') . $widgetHtml;
    }

    public function renderCatalogBadge(?string $html, mixed $product = null): ?string
    {
        if (! $product instanceof Product || ! (bool) setting('razorpay_easy_emi_enable_catalog_badge', true)) {
            return $html;
        }

        // Avoid rendering inside single product detail page where PDP widget is already present
        if (request()->routeIs('public.single*')) {
            return $html;
        }

        $id = (string) $product->id;
        if (request()->attributes->get('razorpay_easy_emi_rendered_' . $id)) {
            return $html;
        }

        $resolutionService = app(EmiResolutionService::class);
        $resolution = $resolutionService->resolveForProduct($product);

        if (! ($resolution['eligible'] ?? false)) {
            return $html;
        }

        request()->attributes->set('razorpay_easy_emi_rendered_' . $id, true);
        request()->attributes->set('razorpay_easy_emi_native_card_badge', $id);

        $badgeHtml = view('plugins/razorpay-easy-emi::front.catalog-badge', compact('resolution'))->render();

        return ($html ?? '') . $badgeHtml;
    }

    public function renderCartEmiReminder(?string $html = null, mixed $products = null): ?string
    {
        if (! (bool) setting('razorpay_easy_emi_enable_cart_banner', true) || ! (bool) setting('razorpay_easy_emi_enabled', true)) {
            return $html;
        }

        if (! is_plugin_active('razorpay') || (string) get_payment_setting('status', 'razorpay') !== '1') {
            return $html;
        }

        $cartTotal = (float) (class_exists(\Botble\Ecommerce\Facades\Cart::class) ? (\Botble\Ecommerce\Facades\Cart::instance('cart')->rawTotal() ?: \Botble\Ecommerce\Facades\Cart::instance('cart')->rawSubTotal()) : 0);
        $minThreshold = (float) setting('razorpay_easy_emi_min_amount', 2500);

        if ($cartTotal < $minThreshold) {
            return $html;
        }

        $minMonthly = (int) round($cartTotal / 24);
        $bannerHtml = '<div class="alert alert-info py-2 px-3 small my-3 border-0 bg-info-subtle text-info-emphasis rounded-2 d-flex align-items-center gap-2 razorpay-cart-emi-alert">'
            . '<i class="ti ti-credit-card fs-4 flex-shrink-0"></i>'
            . '<div><strong>' . trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.emi_available') . ':</strong> '
            . trans('plugins/razorpay-easy-emi::razorpay-easy-emi.front.cart_emi_hint', ['amount' => format_price($minMonthly)])
            . '</div></div>';

        return ($html ?? '') . $bannerHtml;
    }

    public function renderUniversalCartBridge(?string $html = null): ?string
    {
        if (! request()->routeIs('public.cart') && ! request()->is('cart') && ! request()->is('*/cart')) {
            return $html;
        }

        $banner = $this->renderCartEmiReminder('');
        if (empty($banner)) {
            return $html;
        }

        $cssUrl = asset('vendor/core/plugins/razorpay-easy-emi/css/razorpay-easy-emi.css');
        $jsUrl = asset('vendor/core/plugins/razorpay-easy-emi/js/razorpay-easy-emi.js');

        $template = '<link rel="stylesheet" href="' . $cssUrl . '">'
            . '<div id="razorpay-cart-emi-dock-template" style="display: none;">' . $banner . '</div>'
            . '<script src="' . $jsUrl . '"></script>';

        return ($html ?? '') . $template;
    }

    public function renderCheckoutBridge(?string $html = null): ?string
    {
        if (! (bool) setting('razorpay_easy_emi_auto_select_checkout', true) || ! (bool) setting('razorpay_easy_emi_enabled', true)) {
            return $html;
        }

        if (! is_plugin_active('razorpay') || (string) get_payment_setting('status', 'razorpay') !== '1') {
            return $html;
        }

        $minThreshold = (float) setting('razorpay_easy_emi_min_amount', 2500);
        $cart = class_exists(\Botble\Ecommerce\Facades\Cart::class) ? \Botble\Ecommerce\Facades\Cart::instance('cart') : null;
        $cartTotal = (float) ($cart ? ($cart->rawTotal() ?: $cart->rawSubTotal()) : 0);

        $hasNoCostEmi = false;
        $hasLowCostEmi = false;
        $applicableOfferIds = [];

        if ($cart) {
            $cartProductIds = $cart->products()->pluck('id')->toArray();
            if (! empty($cartProductIds)) {
                $products = Product::query()->whereIn('id', $cartProductIds)->get();
                $parentIds = $products->map(fn ($p) => $p->is_variation ? ($p->original_product->id ?? $p->id) : $p->id)->unique()->toArray();

                $configs = RazorpayEmiProductConfig::query()
                    ->whereIn('product_id', $parentIds)
                    ->get();

                foreach ($configs as $config) {
                    if ($config->status === 'disabled') {
                        continue;
                    }

                    $scheme = $config->emi_type ? $config->emi_type->getValue() : setting('razorpay_easy_emi_default_scheme', 'standard');

                    if ($scheme === 'no_cost') {
                        $hasNoCostEmi = true;
                    } elseif ($scheme === 'low_cost') {
                        $hasLowCostEmi = true;
                    }

                    if ($config->offer_mode === 'custom' && ! empty($config->offer_ids)) {
                        $applicableOfferIds = array_merge($applicableOfferIds, $config->offer_ids);
                    }
                }
            }
        }

        // If no custom offer IDs were specifically curated on products, fetch active offers matching cart schemes
        if (empty($applicableOfferIds)) {
            $cachedOffers = \SparroWave\RazorpayEasyEmi\Models\RazorpayEmiCachedOffer::query()
                ->where('is_active', true)
                ->get();

            if ($hasNoCostEmi) {
                $noCostOffers = $cachedOffers->filter(fn ($o) => $o->isNoCostEmi())->pluck('id')->toArray();
                $applicableOfferIds = array_merge($applicableOfferIds, $noCostOffers);
            }
            if ($hasLowCostEmi) {
                $lowCostOffers = $cachedOffers->filter(fn ($o) => $o->isLowCostEmi())->pluck('id')->toArray();
                $applicableOfferIds = array_merge($applicableOfferIds, $lowCostOffers);
            }
        }

        $activeOfferIds = array_values(array_unique(array_filter($applicableOfferIds)));
        $isEligible = $cartTotal >= $minThreshold || $cartTotal == 0;

        $bridgeHtml = view('plugins/razorpay-easy-emi::front.checkout-bridge', compact(
            'cartTotal',
            'minThreshold',
            'hasNoCostEmi',
            'activeOfferIds',
            'isEligible'
        ))->render();

        return ($html ?? '') . $bridgeHtml;
    }
}
