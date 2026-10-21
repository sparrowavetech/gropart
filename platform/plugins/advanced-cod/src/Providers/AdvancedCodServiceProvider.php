<?php

namespace SparroWave\AdvancedCod\Providers;

use Botble\Base\Facades\Assets;
use SparroWave\AdvancedCod\Hooks\AdvancedCodCheckoutListener;
use SparroWave\AdvancedCod\Hooks\AdvancedCodHookListener;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;

class AdvancedCodServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (is_plugin_active('razorpay')) {
            $this->app->bind(
                \Botble\Razorpay\Http\Controllers\RazorpayController::class,
                \SparroWave\AdvancedCod\Http\Controllers\PartialPaymentRazorpayController::class
            );
        }
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/advanced-cod')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'api'])
            ->loadMigrations()
            ->publishAssets();

        $this->app->register(SettlementServiceProvider::class);
        $this->app->register(ManualConfirmationServiceProvider::class);

        $this->app->booted(function () {
            add_filter('ecommerce_order_checkout_locked', fn ($locked, $order) => $locked || (bool) \SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order), 20, 2);
            add_action('ecommerce_checkout_restarted', function () { if (request()->hasSession()) session()->forget(['advanced_cod_prepayment', 'advanced_cod_order_ids']); }, 20, 2);
            add_action('payment_after_api_response', function ($method, $requestData, $response) {
                $rawOrderIds = $requestData['notes']['order_id'] 
                    ?? $requestData['metadata']['order_id'] 
                    ?? $requestData['order_id'] 
                    ?? '';
                if (is_array($rawOrderIds)) {
                    $ids = $rawOrderIds;
                } else {
                    $ids = explode(',', (string) $rawOrderIds);
                }
                $ids = array_filter(array_map('intval', $ids));
                if (empty($ids)) {
                    return;
                }

                $resId = is_array($response) ? ($response['id'] ?? null) : ($response->id ?? null);
                $resAmount = is_array($response) ? ($response['amount'] ?? null) : ($response->amount ?? null);

                foreach (\Botble\Ecommerce\Models\Order::whereIn('id', $ids)->get() as $order) {
                    $intent = \SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order);
                    if ($intent && $intent['gateway'] === $method && $resId && $resAmount) {
                        $order->setOrderMetadata('advanced_cod_gateway_attempt', json_encode([
                            'gateway' => $method,
                            'id' => $resId,
                            'amount' => $resAmount,
                        ]));
                    }
                }
            }, 110, 3);
            foreach (['creating', 'updating'] as $event) {
                \Botble\Ecommerce\Models\OrderProduct::$event([\SparroWave\AdvancedCod\Supports\AdvancePayment::class, 'protectItem']);
            }
            \Botble\Ecommerce\Models\OrderProduct::deleting(function ($item) {
                $order = \Botble\Ecommerce\Models\Order::find($item->order_id);
                if ($order && \SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order)) {
                    throw new \RuntimeException('An advance payment has started; its items cannot be deleted.');
                }
            });
            \Botble\Payment\Models\Payment::updating(function ($payment) {
                $order = \Botble\Ecommerce\Models\Order::find($payment->order_id);
                if (!$order || !\SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order)
                    || (string) $payment->getOriginal('status') !== 'completed') return;
                // Core coupon/order finalizers must not turn the captured advance into a full receipt.
                foreach (['amount', 'currency', 'payment_channel', 'charge_id'] as $field) {
                    if ($payment->isDirty($field)) $payment->setAttribute($field, $payment->getRawOriginal($field));
                }
            });
            \Botble\Ecommerce\Models\Order::updating([\SparroWave\AdvancedCod\Supports\AdvancePayment::class,'protect']);
            add_filter('ecommerce_order_can_finalize', fn ($allowed, $order) => $allowed && \SparroWave\AdvancedCod\Supports\AdvancePayment::mayFinalize($order), 20, 2);
            $this->app->extend('core.action', fn (\Botble\Base\Supports\Action $inner) => new \SparroWave\AdvancedCod\Supports\AdvancePaymentAction($inner));
            \Botble\Base\Facades\Action::clearResolvedInstance('core.action');
            add_filter('ecommerce_handle_verified_partial_payment', fn ($handled, $data) => $handled || \SparroWave\AdvancedCod\Supports\AdvancePayment::handle($data), 20, 2);
            add_filter('ecommerce_expected_online_amount', function ($amount, $orders) {
                if (!$orders->contains(fn ($order) => \SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order))) return $amount;
                if ($orders->contains(fn ($order) => !\SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order))) throw new \RuntimeException('Mixed payment intents.');
                return $orders->sum(fn ($order) => \SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order)['advance']);
            }, 20, 2);

            add_action(BASE_ACTION_META_BOXES, [AdvancedCodHookListener::class, 'addProductMetaBox'], 120, 2);
            add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [AdvancedCodHookListener::class, 'saveProductCodEligibility'], 120, 3);
            add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [AdvancedCodHookListener::class, 'saveProductCodEligibility'], 120, 3);

            add_filter('ecommerce_before_product_description', [AdvancedCodHookListener::class, 'addCodLabelToProductPage'], 120, 2);
            add_filter('ecommerce_after_product_price', [AdvancedCodHookListener::class, 'addCodLabelToProductCard'], 120, 2);
            add_filter('ecommerce_product_item_badges', [AdvancedCodHookListener::class, 'addCodLabelToProductCard'], 120, 2);
            add_filter('ecommerce_product_loop_extra_html', [AdvancedCodHookListener::class, 'addCodLabelToProductCard'], 120, 2);
            add_filter('ecommerce_product_detail_after_cart_actions', [AdvancedCodHookListener::class, 'addCodLabelToProductPage'], 120, 2);
            
            // Register COD settings field
            add_filter(PAYMENT_METHOD_SETTINGS_CONTENT, [AdvancedCodHookListener::class, 'addCodSettings'], 120, 2);

            if (is_plugin_active('ecommerce')) {
                // UI: Conflict Notice (replaces checkout button area)
                add_filter('ecommerce_checkout_form_after', [AdvancedCodCheckoutListener::class, 'renderCartConflictNotice'], 120);
                
                // UI: Product Labels (Badge)
                add_filter('ecommerce_cart_after_item_content', [AdvancedCodCheckoutListener::class, 'addCodLabelToCheckoutItem'], 120, 2);

                // UI: Prepayment Breakdown (inside payment method description)
                add_filter('payment_method_display_body', [AdvancedCodCheckoutListener::class, 'renderCodPrepaymentBreakdown'], 120, 3);
                
                // Logic: Partial Payment Handling
                add_filter(FILTER_ECOMMERCE_PROCESS_PAYMENT, [AdvancedCodCheckoutListener::class, 'handleAdvancedCodPrepayment'], 5, 2);
                add_filter(PAYMENT_FILTER_PAYMENT_DATA, [AdvancedCodCheckoutListener::class, 'adjustPaymentDataAmount'], 125, 2);
                // AdvancePaymentAction owns verified advance persistence and finalization.
                
                // Logic: Hide COD if not eligible (Gatekeeper)
                add_filter('payment_methods_excluded', [AdvancedCodCheckoutListener::class, 'filterPaymentMethods'], 120);
            }
        });
    }
}
