<?php

namespace Botble\LoyaltyPoints\Providers;

use Botble\Base\Facades\Assets;
use Botble\Language\Facades\Language;
use Botble\LoyaltyPoints\Enums\ProductInfoBoxStyleEnum;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\OrderLoyaltyPoints;
use Botble\LoyaltyPoints\Plugin;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerAdminOrderHooks();
        $this->registerOrderRedemptionIntentHook();
        $this->registerMarketplaceOrderDiscountHook();
        add_filter('ecommerce_checkout_form_before_payment_form', function (?string $html) {
            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return $html;
            }

            $currentToken = request()->route('token');
            $lastToken = session('loyalty_last_checkout_token');

            if ($currentToken && $lastToken && $currentToken !== $lastToken) {
                session()->forget([
                    'loyalty_points_to_redeem',
                    'applied_loyalty_points',
                    'loyalty_points_discount',
                    'loyalty_guest_member_id',
                    'loyalty_guest_customer',
                    'loyalty_guest_customer_id',
                ]);
            }

            session(['loyalty_last_checkout_token' => $currentToken]);

            // Show points redemption form for logged-in customers
            if (Auth::guard('customer')->check()) {
                $html .= view('plugins/loyalty-points::themes.checkout.partials.loyalty-points-form')->render();
            } elseif ($loyaltyHelper->isGuestCheckoutMemberIdEnabled()) {
                // Show member ID form for guests if enabled
                $html .= view('plugins/loyalty-points::themes.checkout.partials.guest-member-id-form')->render();
            }

            return $html;
        }, 99);

        add_filter('ecommerce_cart_raw_total', function ($total) {
            if (! Auth::guard('customer')->check()) {
                return $total;
            }

            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return $total;
            }

            $discount = (float) session('loyalty_points_discount', 0);

            if ($discount <= 0) {
                return $total;
            }

            return max(0, $total - $discount);
        }, 999);

        add_filter('ecommerce_order_detail_sidebar_bottom', function (?string $html, $order) {
            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return $html;
            }

            $orderLoyalty = OrderLoyaltyPoints::query()->where('order_id', $order->id)->first();

            if (! $orderLoyalty) {
                return $html;
            }

            $html .= view('plugins/loyalty-points::admin.orders.loyalty-info', [
                'order' => $order,
                'orderLoyalty' => $orderLoyalty,
            ])->render();

            return $html;
        }, 99, 2);

        add_filter('ecommerce_product_price_after', function (?string $html, $product) {
            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return $html;
            }

            $points = $loyaltyHelper->calculatePointsFromAmount($product->price);

            if ($points <= 0) {
                return $html;
            }

            $html .= sprintf(
                '<div class="loyalty-points-badge mt-2"><small class="text-success"><i class="ti ti-gift"></i> %s</small></div>',
                trans('plugins/loyalty-points::loyalty-points.customer.earn_points_badge', ['points' => number_format($points)])
            );

            return $html;
        }, 99, 2);

        add_filter('ecommerce_cart_table_after_subtotal', function (?string $html) {
            if (! Auth::guard('customer')->check()) {
                return $html;
            }

            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return $html;
            }

            $cart = app('cart');
            $subtotal = (float) $cart->rawSubTotal();
            $points = $loyaltyHelper->calculatePointsFromAmount($subtotal);

            if ($points <= 0) {
                return $html;
            }

            $html .= sprintf(
                '<tr class="loyalty-points-info"><td colspan="2"><small class="text-success"><i class="ti ti-gift"></i> %s</small></td></tr>',
                trans('plugins/loyalty-points::loyalty-points.customer.earn_points_badge', ['points' => number_format($points)])
            );

            return $html;
        }, 99);

        add_filter('ecommerce_checkout_after_subtotal', function (?string $html) {
            $appliedPoints = session('applied_loyalty_points', 0);
            $discount = session('loyalty_points_discount', 0);

            if ($appliedPoints <= 0 || $discount <= 0) {
                return $html;
            }

            $html .= sprintf(
                '<div class="row"><div class="col-6"><p>%s:</p></div><div class="col-6"><p class="price-text loyalty-discount-text" data-price="%s">-%s</p></div></div>',
                trans('plugins/loyalty-points::loyalty-points.checkout.points_discount') . ' (' . number_format($appliedPoints) . ' ' . trans('plugins/loyalty-points::loyalty-points.points.points') . ')',
                format_price($discount, null, true),
                format_price($discount)
            );

            return $html;
        }, 99);

        add_filter('ecommerce_thank_you_total_info', function (?string $html, $order) {
            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return $html;
            }

            $orderLoyalty = OrderLoyaltyPoints::query()->where('order_id', $order->id)->first();

            if (! $orderLoyalty) {
                return $html;
            }

            if ($orderLoyalty->points_redeemed > 0 && $orderLoyalty->discount_amount > 0) {
                $html .= sprintf(
                    '<div class="row"><div class="col-6"><p>%s <small>(%s %s)</small>:</p></div><div class="col-6 float-end"><p class="price-text">-%s</p></div></div>',
                    trans('plugins/loyalty-points::loyalty-points.checkout.points_discount'),
                    number_format($orderLoyalty->points_redeemed),
                    trans('plugins/loyalty-points::loyalty-points.points.points'),
                    format_price($orderLoyalty->discount_amount)
                );
            }

            return $html;
        }, 99, 2);

        add_filter('ecommerce_thank_you_customer_info', function (?string $html, $order) {
            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled() || ! $order->user_id) {
                return $html;
            }

            $orderLoyalty = OrderLoyaltyPoints::query()->where('order_id', $order->id)->first();

            if (! $orderLoyalty || ($orderLoyalty->points_to_earn <= 0 && $orderLoyalty->points_redeemed <= 0)) {
                return $html;
            }

            $html .= '<link rel="stylesheet" href="' . asset('vendor/core/plugins/loyalty-points/css/loyalty-points.css') . '?v=' . Plugin::ASSETS_VERSION . '">';
            $html .= '<div class="order-loyalty-info mt-3 mt-md-4 mb-0 mb-sm-4">';
            $html .= '<div class="loyalty-info-card">';
            $html .= '<div class="loyalty-info-header">';
            $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6"/><path d="M4 8V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2"/><path d="M12 4v16"/><path d="M2 8h20"/></svg>';
            $html .= '<span>' . trans('plugins/loyalty-points::loyalty-points.thank_you.loyalty_points') . '</span>';
            $html .= '</div>';
            $html .= '<div class="loyalty-info-body">';

            if ($orderLoyalty->points_redeemed > 0) {
                $html .= '<div class="loyalty-info-row loyalty-redeemed">';
                $html .= '<div class="loyalty-info-label">';
                $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 15l6-6"/><circle cx="9.5" cy="9.5" r=".5" fill="currentColor"/><circle cx="14.5" cy="14.5" r=".5" fill="currentColor"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-3-2-2 2-2-2-2 2-2-2-3 2z"/></svg>';
                $html .= sprintf('<span>%s</span>', trans('plugins/loyalty-points::loyalty-points.thank_you.points_redeemed'));
                $html .= '</div>';
                $html .= sprintf(
                    '<div class="loyalty-info-value text-danger"><span class="points-amount">-%s %s</span><span class="saved-amount">(%s %s)</span></div>',
                    number_format($orderLoyalty->points_redeemed),
                    trans('plugins/loyalty-points::loyalty-points.points.points'),
                    trans('plugins/loyalty-points::loyalty-points.thank_you.saved'),
                    format_price($orderLoyalty->discount_amount)
                );
                $html .= '</div>';
            }

            if ($orderLoyalty->points_to_earn > 0) {
                $html .= '<div class="loyalty-info-row loyalty-earned">';
                $html .= '<div class="loyalty-info-label">';
                $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                $html .= sprintf('<span>%s</span>', trans('plugins/loyalty-points::loyalty-points.thank_you.points_to_earn'));
                $html .= '</div>';
                $html .= sprintf(
                    '<div class="loyalty-info-value text-success"><span class="points-amount">+%s %s</span></div>',
                    number_format($orderLoyalty->points_to_earn),
                    trans('plugins/loyalty-points::loyalty-points.points.points')
                );
                $html .= '</div>';

                $html .= '<div class="loyalty-info-notice">';
                $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
                $html .= sprintf('<span>%s</span>', trans('plugins/loyalty-points::loyalty-points.thank_you.points_earn_notice'));
                $html .= '</div>';
            }

            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';

            return $html;
        }, 99, 2);

        add_filter('ecommerce_customer_order_view_before_actions', function (?string $html, $order) {
            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled() || ! $order->user_id) {
                return $html;
            }

            Theme::asset()
                ->usePath(false)
                ->add('loyalty-points-css', 'vendor/core/plugins/loyalty-points/css/loyalty-points.css');

            $html .= view('plugins/loyalty-points::themes.customers.order-loyalty-info', compact('order'))->render();

            return $html;
        }, 99, 2);

        add_filter('ecommerce_after_product_description', [$this, 'showLoyaltyPointsInfo'], 10, 2);

        $this->registerLanguageSwitcherHook();
    }

    protected function registerLanguageSwitcherHook(): void
    {
        if (! is_plugin_active('language')) {
            return;
        }

        add_filter('language_switcher_get_url', function (?string $url, string $localeCode, string $languageCode) {
            $currentUrl = request()->url();
            $allSlugs = $this->getAllLanguageSlugs();

            foreach ($allSlugs as $slug) {
                $slugPattern = preg_quote($slug, '/');

                if (preg_match('/\/' . $slugPattern . '(\/|$)/', $currentUrl)) {
                    $targetSlug = get_loyalty_customer_page_slug($localeCode);

                    return get_loyalty_customer_page_url($localeCode);
                }
            }

            return $url;
        }, 99, 3);
    }

    protected function getAllLanguageSlugs(): array
    {
        $slugs = [];

        $defaultSlug = get_loyalty_setting('customer_page_slug', 'customer/loyalty-points');
        $slugs[] = $defaultSlug;

        if (is_plugin_active('language')) {
            $languages = Language::getActiveLanguage(['lang_locale']);

            foreach ($languages as $language) {
                $langSlug = get_loyalty_setting('customer_page_slug_' . $language->lang_locale);

                if ($langSlug && ! in_array($langSlug, $slugs)) {
                    $slugs[] = $langSlug;
                }
            }
        }

        return $slugs;
    }

    protected function registerAdminOrderHooks(): void
    {
        add_action(BASE_ACTION_ENQUEUE_SCRIPTS, function (): void {
            if (! request()->routeIs('orders.create')) {
                return;
            }

            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return;
            }

            Assets::addScriptsDirectly('vendor/core/plugins/loyalty-points/js/admin-order-member-id.js?v=' . Plugin::ASSETS_VERSION);
        }, 99);
    }

    /**
     * Persist the loyalty redemption intent on the order BEFORE the customer is
     * redirected to a payment gateway. This guarantees the OrderPlaced listener
     * can recover the redemption details from the DB even when the gateway calls
     * back via webhook (no PHP session, no auth guard).
     */
    protected function registerOrderRedemptionIntentHook(): void
    {
        add_action('ecommerce_before_processing_payment', function ($products, $request, $token, $sessionData): void {
            if (! app(LoyaltyHelper::class)->isEnabled()) {
                return;
            }

            $orderId = $request->input('order_id');

            // Marketplace checkout passes an array of per-store order IDs (one
            // order per vendor); the standard single-vendor checkout passes a
            // scalar. Normalize to a single concrete ID so the redemption intent
            // is persisted once - and, critically, so updateOrCreate() never
            // receives an array as the order_id value, which makes Laravel's
            // query grammar treat each column value as a row and crash on the
            // null customer_id (HTTP 500 at checkout on marketplace sites).
            if (is_array($orderId)) {
                $orderId = collect($orderId)->filter()->first();
            }

            if (! $orderId) {
                return;
            }

            $appliedPoints = (int) session('applied_loyalty_points', 0);
            $discount = (float) session('loyalty_points_discount', 0);
            $guestCustomerId = session('loyalty_guest_customer_id');

            // When the buyer removes the loyalty discount on a retry of the same
            // order (same checkout token = same order_id), drop any stale intent
            // row so the webhook listener cannot replay a redemption the buyer
            // already cancelled.
            if ($appliedPoints <= 0 && $discount <= 0 && ! $guestCustomerId) {
                OrderLoyaltyPoints::query()->where('order_id', $orderId)->delete();

                return;
            }

            OrderLoyaltyPoints::query()->updateOrCreate(
                ['order_id' => $orderId],
                [
                    'customer_id' => $guestCustomerId,
                    'points_redeemed' => $appliedPoints,
                    'discount_amount' => $discount,
                ]
            );
        }, 99, 4);
    }

    /**
     * Apply the loyalty redemption discount to the per-vendor marketplace orders
     * BEFORE the payment amount is summed and sent to the gateway.
     *
     * Marketplace splits a checkout into one order per vendor and computes the
     * payment total as the sum of each order's amount - a path that never ran the
     * loyalty discount, so the gateway (e.g. PayPal) charged the full price while
     * the order recorded the discount, overcharging the buyer (ticket #4567670).
     *
     * We mirror the single-vendor flow: reduce each order's amount by its share of
     * the discount (distributed by amount, with the last order absorbing the
     * rounding remainder so the shares sum exactly). We deliberately do NOT touch
     * discount_amount here - the OrderPlacedEvent listener records that for display
     * and, seeing the amount already reduced, will not subtract it twice.
     */
    protected function registerMarketplaceOrderDiscountHook(): void
    {
        add_filter('marketplace_checkout_orders_before_processing_payment', function ($orders, $request, $token) {
            if (! app(LoyaltyHelper::class)->isEnabled()) {
                return $orders;
            }

            if (! Auth::guard('customer')->check()) {
                return $orders;
            }

            $discount = (float) session('loyalty_points_discount', 0);

            if ($discount <= 0 || ! $orders || $orders->isEmpty()) {
                return $orders;
            }

            // Only orders with a positive amount can absorb the discount.
            $payableOrders = $orders->filter(fn ($order) => (float) $order->amount > 0)->values();
            $total = (float) $payableOrders->sum('amount');

            if ($total <= 0) {
                return $orders;
            }

            // Never discount more than the payable total.
            $discount = min($discount, $total);

            $remaining = $discount;
            $lastIndex = $payableOrders->count() - 1;

            foreach ($payableOrders as $index => $order) {
                if ($index === $lastIndex) {
                    // Last order absorbs the remainder so the shares sum exactly.
                    $share = $remaining;
                } else {
                    $share = round($discount * ((float) $order->amount / $total), 2);
                    $share = min($share, $remaining);
                }

                if ($share <= 0) {
                    continue;
                }

                $order->amount = max(0, (float) $order->amount - $share);
                $order->save();

                $remaining = round($remaining - $share, 2);
            }

            return $orders;
        }, 99, 3);
    }

    public function showLoyaltyPointsInfo(?string $html, $product): string
    {
        $loyaltyHelper = app(LoyaltyHelper::class);

        if (! $loyaltyHelper->isEnabled() || ! $loyaltyHelper->isProductInfoEnabled()) {
            return $html ?? '';
        }

        $productPrice = $product->price()->getPrice();

        if ($productPrice <= 0) {
            return $html ?? '';
        }

        $basePointsToEarn = $loyaltyHelper->calculatePointsFromAmount($productPrice);

        // Apply tier multiplier for logged-in customers
        $tierMultiplier = 1;
        $customerLevel = null;
        $customer = auth('customer')->user();
        if ($customer && $customer->pointBalance && $customer->pointBalance->level) {
            $customerLevel = $customer->pointBalance->level;
            if ($customerLevel->earning_rate > 1) {
                $tierMultiplier = $customerLevel->earning_rate;
            }
        }
        $pointsToEarn = (int) floor($basePointsToEarn * $tierMultiplier);

        $maxPointsCanUse = $loyaltyHelper->calculateMaxPointsFromAmount($productPrice);
        $maxDiscount = $loyaltyHelper->calculateDiscountFromPoints($maxPointsCanUse);

        $maxRedemptionPercentage = $loyaltyHelper->getMaxRedemptionPercentage();
        if ($maxRedemptionPercentage > 0) {
            $maxAllowedDiscount = ($productPrice * $maxRedemptionPercentage) / 100;
            if ($maxDiscount > $maxAllowedDiscount) {
                $maxDiscount = $maxAllowedDiscount;
                $maxPointsCanUse = (int) ceil(($maxDiscount * $loyaltyHelper->getRedemptionRate()) / $loyaltyHelper->getRedemptionCurrency());
            }
        }

        if ($pointsToEarn <= 0 && $maxPointsCanUse <= 0) {
            return $html ?? '';
        }

        $version = '1.2.0';

        Theme::asset()
            ->usePath(false)
            ->add('loyalty-points-css', 'vendor/core/plugins/loyalty-points/css/loyalty-points.css', version: $version);

        Theme::asset()
            ->container('footer')
            ->add('loyalty-product-variation-js', 'vendor/core/plugins/loyalty-points/js/loyalty-product-variation.js', ['jquery', 'front-ecommerce-js'], version: $version);

        $boxStyle = get_loyalty_setting('product_info_box_style', ProductInfoBoxStyleEnum::DEFAULT);

        $currencyConfig = get_application_currency();
        $loyaltyConfig = [
            'earningRate' => $loyaltyHelper->getEarningRate(),
            'earningCurrency' => $loyaltyHelper->getEarningCurrency(),
            'redemptionRate' => $loyaltyHelper->getRedemptionRate(),
            'redemptionCurrency' => $loyaltyHelper->getRedemptionCurrency(),
            'maxRedemptionPercentage' => $maxRedemptionPercentage,
            'tierMultiplier' => $tierMultiplier,
            'currencySymbol' => $currencyConfig?->symbol ?? '$',
            'currencyPosition' => get_ecommerce_setting('currency_position', 'before'),
            'thousandsSeparator' => get_ecommerce_setting('thousands_separator', ','),
            'decimalSeparator' => get_ecommerce_setting('decimal_separator', '.'),
            'decimals' => (int) get_ecommerce_setting('decimal_digits', 0),
        ];

        $appearanceSettings = [
            'bg_color' => get_loyalty_setting('product_info_bg_color', '#f8f9fa'),
            'text_color' => get_loyalty_setting('product_info_text_color', '#6c757d'),
            'icon_color' => get_loyalty_setting('product_info_icon_color', '#2fb344'),
            'border_color' => get_loyalty_setting('product_info_border_color', '#e0e0e0'),
            'border_radius' => get_loyalty_setting('product_info_border_radius', ''),
            'padding' => get_loyalty_setting('product_info_padding', ''),
        ];

        $loyaltyHtml = view('plugins/loyalty-points::themes.loyalty-product-info', [
            'product' => $product,
            'productPrice' => $productPrice,
            'pointsToEarn' => $pointsToEarn,
            'maxPointsCanUse' => $maxPointsCanUse,
            'maxDiscount' => $maxDiscount,
            'customerLevel' => $customerLevel,
            'boxStyle' => $boxStyle,
            'loyaltyConfig' => $loyaltyConfig,
            'appearanceSettings' => $appearanceSettings,
        ])->render();

        return ($html ?? '') . $loyaltyHtml;
    }
}
