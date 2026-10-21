<?php

namespace SparroWave\AdvancedCod\Hooks;

use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Order;
use Illuminate\Support\Facades\Route;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Ecommerce\Enums\OrderHistoryActionEnum;
use SparroWave\AdvancedCod\Supports\CodAmounts;

class AdvancedCodCheckoutListener
{
    public static function filterPaymentMethods(array $excludedMethods): array
    {
        if (! is_plugin_active('payment') || get_payment_setting('status', 'cod') == 0) {
            return $excludedMethods;
        }

        $cartItems = Cart::instance('cart')->content();
        
        foreach ($cartItems as $item) {
            $product = self::getProductForCodEligibility($item->id);
            if (! $product || ! $product->is_cod_eligible) {
                $excludedMethods[] = 'cod';
                break;
            }
        }

        return $excludedMethods;
    }

    public static function renderCartConflictNotice(?string $html): ?string
    {
        // Only render on checkout page
        if (Route::currentRouteName() !== 'public.checkout.information' && Route::currentRouteName() !== 'public.checkout.payment') {
            return $html;
        }

        $cartContent = Cart::instance('cart')->content();
        $hasCodEligible = false;
        $hasNonEligible = false;
        $ineligibleItems = [];

        foreach ($cartContent as $item) {
            $product = self::getProductForCodEligibility($item->id);
            if ($product && $product->is_cod_eligible) {
                $hasCodEligible = true;
            } else {
                $hasNonEligible = true;
                $ineligibleItems[] = [
                    'name' => $product ? $product->name : $item->name,
                    'rowId' => $item->rowId,
                ];
            }
        }

        // Scenario 1: Mixed Cart (Conflict) -> Hide Checkout Button & Show Error
        if ($hasCodEligible && $hasNonEligible) {
            $conflictHtml = '
                <style>
                    /* Premium Checkout Button & Theme Button Styling */
                    .payment-checkout-btn, #checkout-button, .btn-checkout, form.checkout-form button[type="submit"], .btn-payment-checkout {
                        display: none !important;
                    }
                
                    /* Existing Conflict Card Styles */
                    .conflict-alert-card {
                        background: #fff;
                        border: 1px solid #fee2e2;
                        border-left: 4px solid #ef4444;
                        border-radius: 8px;
                        padding: 20px;
                        margin-bottom: 24px;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                        width: 100%;
                    }
                    .conflict-alert-header {
                        display: flex;
                        align-items: center;
                        color: #b91c1c;
                        font-weight: 700;
                        font-size: 1.1rem;
                        margin-bottom: 12px;
                    }
                    .conflict-alert-header svg { margin-right: 8px; }
                    .conflict-alert-body {
                        color: #4b5563;
                        font-size: 0.95rem;
                        margin-bottom: 20px;
                        line-height: 1.5;
                    }
                    .ineligible-item-row {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        background: #fef2f2;
                        border: 1px solid #fee2e2;
                        padding: 12px 16px;
                        border-radius: 6px;
                        margin-bottom: 8px;
                    }
                    .ineligible-item-name {
                        font-weight: 600;
                        color: #1f2937;
                        font-size: 0.95rem;
                    }
                    .btn-remove-item {
                        color: #dc2626;
                        font-size: 0.85rem;
                        font-weight: 600;
                        text-decoration: none;
                        padding: 6px 12px;
                        border: 1px solid #dc2626;
                        border-radius: 4px;
                        background: #fff;
                        transition: all 0.2s;
                        white-space: nowrap;
                        margin-left: 10px;
                        display: inline-flex;
                        align-items: center;
                    }
                    .btn-remove-item:hover {
                        background: #dc2626;
                        color: #fff;
                        text-decoration: none;
                    }
                    .btn-pay-online-wrapper {
                        margin-top: 24px;
                        border-top: 1px solid #f3f4f6;
                        padding-top: 20px;
                    }
                    .btn-pay-online {
                        width: 100%;
                        background: linear-gradient(135deg, #0ea5e9, #0284c7);
                        color: white;
                        padding: 14px;
                        border-radius: 8px;
                        font-weight: 600;
                        font-size: 1rem;
                        border: none;
                        cursor: pointer;
                        transition: all 0.3s;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2);
                    }
                    .btn-pay-online:hover {
                        background: linear-gradient(135deg, #0284c7, #0369a1);
                        transform: translateY(-2px);
                        box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3);
                    }
                    .btn-pay-online svg { margin-right: 8px; }

                    /* Undo Box Styling */
                    .undo-conflict-box {
                        display: none;
                        background: #f0f9ff;
                        border: 1px solid #bae6fd;
                        border-left: 4px solid #0ea5e9;
                        padding: 16px;
                        border-radius: 8px;
                        margin-bottom: 24px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                        animation: fadeIn 0.3s ease-in-out;
                    }
                    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
                    
                    .undo-content {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 10px;
                    }
                    .undo-text {
                        color: #0369a1;
                        font-size: 0.95em;
                        font-weight: 500;
                        display: flex;
                        align-items: center;
                    }
                    .btn-undo-action {
                        background: white;
                        border: 1px solid #0ea5e9;
                        color: #0284c7;
                        padding: 6px 12px;
                        border-radius: 20px;
                        font-size: 0.85rem;
                        font-weight: 600;
                        text-decoration: none;
                        display: inline-flex;
                        align-items: center;
                        transition: all 0.2s;
                    }
                    .btn-undo-action:hover {
                        background: #0ea5e9;
                        color: white;
                        text-decoration: none;
                    }
                </style>

                <div class="conflict-alert-card" id="cod-conflict-alert">
                    <div class="conflict-alert-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-alert-triangle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                        ' . trans('plugins/advanced-cod::advanced-cod.conflict_title') . '
                    </div>
                    <p class="conflict-alert-body">
                        ' . trans('plugins/advanced-cod::advanced-cod.conflict_body') . '
                    </p>
                    <div class="ineligible-list">
            ';

            foreach ($ineligibleItems as $item) {
                $removeUrl = route('public.cart.remove', $item['rowId']);
                $conflictHtml .= '
                        <div class="ineligible-item-row">
                            <span class="ineligible-item-name">' . e($item['name']) . '</span>
                            <a href="' . $removeUrl . '" class="btn-remove-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                ' . trans('plugins/advanced-cod::advanced-cod.remove') . '
                            </a>
                        </div>
                ';
            }

            $conflictHtml .= '
                    </div>
                    <div class="btn-pay-online-wrapper">
                        <button type="button" class="btn-pay-online" id="btn-pay-online-action">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M3 10l18 0" /><path d="M7 15l.01 0" /><path d="M11 15l2 0" /></svg>
                            ' . trans('plugins/advanced-cod::advanced-cod.pay_entire_online') . '
                        </button>
                        <p class="text-center mt-2 mb-0" style="font-size: 0.85rem; color: #6b7280;">' . trans('plugins/advanced-cod::advanced-cod.secure_online_payment') . '</p>
                    </div>
                </div>
                
                <div id="cod-conflict-minimized" class="undo-conflict-box">
                    <div class="undo-content">
                        <span class="undo-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-info-circle" style="margin-right: 8px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                            ' . trans('plugins/advanced-cod::advanced-cod.conflict_minimized') . '
                        </span>
                        <a href="javascript:void(0);" id="btn-undo-cod-conflict" class="btn-undo-action">
                            ' . trans('plugins/advanced-cod::advanced-cod.review_conflict') . '
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11l-4 4l4 4m-4 -4h11a4 4 0 0 0 0 -8h-1" /></svg>
                        </a>
                    </div>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var payBtn = document.getElementById("btn-pay-online-action");
                        var undoBtn = document.getElementById("btn-undo-cod-conflict");
                        var alertCard = document.getElementById("cod-conflict-alert");
                        var minimizedCard = document.getElementById("cod-conflict-minimized");
                        var checkoutSelector = ".payment-checkout-btn, #checkout-button, .btn-checkout, form.checkout-form button[type=\'submit\'], .btn-payment-checkout";

                        if (payBtn) {
                            payBtn.addEventListener("click", function(e) {
                                e.preventDefault();
                                // Unhide checkout buttons across any theme
                                var checkoutBtns = document.querySelectorAll(checkoutSelector);
                                checkoutBtns.forEach(function(el) { el.style.setProperty("display", "", "important"); });
                                
                                // Hide conflict alert & Show minimized
                                if (alertCard) alertCard.style.display = "none";
                                if (minimizedCard) minimizedCard.style.display = "block";
                                
                                // Select first available online method
                                var onlineInputs = document.querySelectorAll("input[name=\'payment_method\']");
                                var found = false;
                                onlineInputs.forEach(function(input) {
                                    if (input.value !== "cod" && !found) {
                                        input.click(); 
                                        input.checked = true;
                                        found = true;
                                        input.scrollIntoView({ behavior: "smooth", block: "center" });
                                    }
                                });
                                
                                if (!found) {
                                    alert(' . json_encode(trans('plugins/advanced-cod::advanced-cod.no_online_method_found')) . ');
                                }
                            });
                        }

                        if (undoBtn) {
                            undoBtn.addEventListener("click", function(e) {
                                e.preventDefault();
                                // Hide checkout buttons again
                                var checkoutBtns = document.querySelectorAll(checkoutSelector);
                                checkoutBtns.forEach(function(el) { el.style.setProperty("display", "none", "important"); });

                                // Show conflict alert & Hide minimized
                                if (alertCard) alertCard.style.display = "block";
                                if (minimizedCard) minimizedCard.style.display = "none";

                                // Scroll back to alert
                                if (alertCard) alertCard.scrollIntoView({ behavior: "smooth", block: "center" });
                            });
                        }
                    });
                </script>
            ';

            return $html . $conflictHtml;
        }

        return $html;
    }

    public static function addCodLabelToCheckoutItem(?string $html, $cartItem): string
    {
        $product = self::getProductForCodEligibility($cartItem->id);
        
        if (!$product) {
            return $html;
        }

        $label = '';
        if ($product->is_cod_eligible) {
            $label = '<div style="margin-top: 4px;"><span class="badge bg-success text-white" style="font-size: 0.7em; padding: 0.35em 0.65em;">' . trans('plugins/advanced-cod::advanced-cod.cod_eligible') . '</span></div>';
        } else {
            $label = '<div style="margin-top: 4px;"><span class="badge bg-danger text-white" style="font-size: 0.7em; padding: 0.35em 0.65em;">' . trans('plugins/advanced-cod::advanced-cod.not_eligible_for_cod') . '</span></div>';
        }

        return $html . $label;
    }

    public static function renderCodPrepaymentBreakdown(?string $html, string $paymentName, ?string $paymentLabel = null): string
    {
        if ($paymentName !== 'cod') {
            return $html;
        }

        // Check if cart is valid for COD (all eligible)
        $cartItems = Cart::instance('cart')->content();
        if ($cartItems->isEmpty()) {
            return $html;
        }

        $allEligible = true;
        foreach ($cartItems as $item) {
            $product = self::getProductForCodEligibility($item->id);
            if (!$product || !$product->is_cod_eligible) {
                $allEligible = false;
                break;
            }
        }

        if (!$allEligible) {
            return $html; 
        }

        // We use a safe fallback total from PHP (Cart Raw Total) to prevent "0" flash.
        // But the REAL calculation will happen via JS to include Shipping/Tax updates dynamically.
        $cart = Cart::instance('cart');
        $rawTotal = $cart->rawTotal(); 
        $percentage = \SparroWave\AdvancedCod\Supports\CodAmounts::getPrepaymentPercentage();
        
        $initialPrepayment = \SparroWave\AdvancedCod\Supports\CodAmounts::split((float)$rawTotal, $percentage)['advance'];
        $token = request()->route('token') ?: (request()->hasSession() ? session('tracked_start_checkout') : null);
        $frozenAdvance = null;
        if ($token) {
            $saved = Order::where('token', $token)->get()->map(fn ($order) => \SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order));
            if ($saved->isNotEmpty() && $saved->every(fn ($intent) => (bool)$intent)) {
                $frozenAdvance = (float)$saved->sum('advance');
                $initialPrepayment = $frozenAdvance;
                $rawTotal = (float)$saved->sum('total');
            }
        }
        $initialRemaining = $rawTotal - $initialPrepayment;
        $currency = get_application_currency();
        $currencySymbol = $currency->symbol;

        $breakdownHtml = '
            <div class="cod-payment-breakdown mt-2 p-3" style="background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 6px;">
                <h6 class="text-success mb-2" style="font-weight: bold; display: flex; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-check" style="margin-right: 5px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                    ' . trans('plugins/advanced-cod::advanced-cod.smart_cod_available', ['percentage' => $percentage]) . '
                </h6>
                <p class="mb-2" style="font-size: 0.9em;">
                    ' . trans('plugins/advanced-cod::advanced-cod.pay_just_amount_now', ['percentage' => $percentage, 'amount' => '<strong id="cod-text-amount">' . format_price($initialPrepayment) . '</strong>']) . '
                </p>
                <div style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #eee;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9em; margin-bottom: 4px;">
                        <span>' . trans('plugins/advanced-cod::advanced-cod.pay_now_online') . '</span>
                        <strong class="text-success" id="cod-pay-now-val">' . format_price($initialPrepayment) . '</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.9em;">
                        <span>' . trans('plugins/advanced-cod::advanced-cod.pay_on_delivery') . '</span>
                        <strong id="cod-pay-later-val">' . format_price($initialRemaining) . '</strong>
                    </div>
                </div>
                <p class="mt-2 mb-0 text-muted" style="font-size: 0.8em; font-style: italic;">
                    ' . trans('plugins/advanced-cod::advanced-cod.calculated_on_total') . '
                </p>
            </div>
            
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const percentage = ' . $percentage . ';
                    const frozenAdvance = ' . json_encode($frozenAdvance) . ';
                    const currencyCode = "' . $currency->title . '";

                    function parseAmount(text) {
                        if (!text) return 0;
                        let clean = text.replace(/[^0-9.,]/g, "");
                        if (clean.includes(",") && clean.includes(".")) {
                            if (clean.indexOf(",") < clean.indexOf(".")) {
                                clean = clean.replace(/,/g, "");
                            } else {
                                clean = clean.replace(/\./g, "").replace(",", ".");
                            }
                        } else if (clean.includes(",")) {
                            clean = clean.replace(/,/g, "");
                        }
                        return parseFloat(clean) || 0;
                    }

                    function updateCodBreakdown() {
                        const totalEl = document.querySelector(".ec-checkout-total-row .total-text, .checkout-total-amount, .order-summary .total, .payment-total-amount, [data-checkout-total]");
                        if (!totalEl) return;

                        let total = parseAmount(totalEl.innerText.trim());
                        if (isNaN(total) || total <= 0) return;

                        const remain = frozenAdvance !== null ? Math.round((total - frozenAdvance) * 100) / 100 : Math.min(Math.floor(total), Math.round(total * (1 - percentage / 100)));
                        const prepay = Math.round((total - remain) * 100) / 100;

                        const locale = document.documentElement.lang || "en-US";
                        const formatter = new Intl.NumberFormat(locale, {
                            style: "currency",
                            currency: currencyCode,
                            minimumFractionDigits: 2
                        });

                        const payNowEl = document.getElementById("cod-pay-now-val");
                        const payLaterEl = document.getElementById("cod-pay-later-val");
                        const textEl = document.getElementById("cod-text-amount");

                        if (payNowEl) payNowEl.innerText = formatter.format(prepay);
                        if (payLaterEl) payLaterEl.innerText = formatter.format(remain);
                        if (textEl) textEl.innerText = formatter.format(prepay);
                    }

                    updateCodBreakdown();
                    setInterval(updateCodBreakdown, 1000);
                });
            </script>
        ';

        return $html . $breakdownHtml;
    }

    public static function renderCodPrepaymentNotice(?string $html, string $method): ?string
    {
        // Deprecated, doing nothing to avoid double notices
        return $html;
    }

    public static function handleAdvancedCodPrepayment(array $data, \Illuminate\Http\Request $request): array
    {
        if ($request->input('payment_method') !== 'cod') {
            return $data;
        }

        if (! self::isAdvancedCodRequired()) {
            return $data;
        }

        $gateways = apply_filters('advanced_cod_online_gateways', array_values(\Botble\Payment\Enums\PaymentMethodEnum::toArray()));
        $preferred = (string) get_payment_setting('advance_gateway', 'cod', 'auto');
        if ($preferred !== 'auto' && !empty($preferred)) {
            $gateways = array_unique(array_merge([$preferred], $gateways));
        }

        $onlineMethod = null;
        foreach ($gateways as $gateway) {
            if (!in_array($gateway, ['cod', 'bank_transfer', 'auto'], true) && get_payment_setting('status', $gateway) == 1) {
                $onlineMethod = $gateway;
                break;
            }
        }

        if (!$onlineMethod) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'payment_method' => trans('plugins/advanced-cod::advanced-cod.no_gateway_available_for_advance')
            ]);
        }

        \SparroWave\AdvancedCod\Supports\AdvancePayment::finalizeFees((array)$request->input('order_id', []));
        \SparroWave\AdvancedCod\Supports\AdvancePayment::prepare((array)$request->input('order_id', []), $onlineMethod);
        // Request-scoped intent: stale session flags cannot discount a later prepaid checkout.
        $request->attributes->set('advanced_cod_order_ids', (array)$request->input('order_id', []));
        $request->merge(['payment_method' => $onlineMethod]);
        $data['type'] = $onlineMethod;
        return $data;
    }

    public static function adjustPaymentDataAmount(array $data, \Illuminate\Http\Request $request): array
    {
        $ids = $request->attributes->get('advanced_cod_order_ids', []);
        if (!$ids || !isset($data['amount'])) return $data;
        $orders = Order::whereIn('id', $ids)->get();
        if ($request->hasSession()) session()->put('selected_payment_method', 'cod');

        $advanceTotal = round($orders->sum(fn ($order) => \SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order)['advance'] ?? 0), 2);
        $data['amount'] = $advanceTotal;

        // Bridge for line-item based gateways (Stripe, PayPal, Mollie, etc.)
        // When gateways construct checkout sessions from $data['products'] & shipping,
        // this synthetic line item ensures only the partial advance is charged.
        $orderCodes = $orders->pluck('code')->filter()->implode(', ');
        $percentage = \SparroWave\AdvancedCod\Supports\CodAmounts::getPrepaymentPercentage();
        $lineItemTitle = trans('plugins/advanced-cod::advanced-cod.advance_line_item_name', [
            'percentage' => $percentage,
            'order' => $orderCodes ? '(' . $orderCodes . ')' : '',
        ]);

        $data['products'] = [
            [
                'id' => 'cod_advance_prepayment',
                'name' => $lineItemTitle,
                'price' => $advanceTotal,
                'qty' => 1,
            ],
        ];

        if (isset($data['shipping_amount'])) {
            $data['shipping_amount'] = 0;
        }
        if (isset($data['tax_amount'])) {
            $data['tax_amount'] = 0;
        }
        if (isset($data['discount_amount'])) {
            $data['discount_amount'] = 0;
        }

        return $data;
    }

    protected static function isAdvancedCodRequired(): bool
    {
        $cartItems = Cart::instance('cart')->content();
        if ($cartItems->isEmpty()) {
            return false;
        }

        foreach ($cartItems as $item) {
            $product = self::getProductForCodEligibility($item->id);
            if (! $product || ! $product->is_cod_eligible) {
                return false;
            }
        }
        
        return true;
    }

    protected static function getProductForCodEligibility(int|string|null $productId): ?Product
    {
        if (! $productId) {
            return null;
        }

        $product = Product::query()
            ->with('variationInfo.configurableProduct')
            ->find($productId);

        if (! $product) {
            return null;
        }

        if ($product->is_variation && $product->variationInfo->configurableProduct->id) {
            return $product->variationInfo->configurableProduct;
        }

        return $product;
    }

}
