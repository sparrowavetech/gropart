<?php

namespace SparroWave\FarmartHelper\Providers;

use Botble\Base\Facades\Action;
use Botble\Base\Supports\Action as BaseAction;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Supports\OrderHelper as BaseOrderHelper;
use Illuminate\Support\ServiceProvider;
use SparroWave\FarmartHelper\Http\Middleware\GuardCheckoutSuccess;
use SparroWave\FarmartHelper\Supports\CheckoutOrderHelper;
use SparroWave\FarmartHelper\Supports\PaymentActionGuard;

class CheckoutIntegrityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! is_plugin_active('ecommerce') || ! is_plugin_active('payment')) {
            return;
        }

        $this->app->bind(BaseOrderHelper::class, CheckoutOrderHelper::class);
        OrderHelper::clearResolvedInstance(BaseOrderHelper::class);

        $this->app->booted(function () {
            if (is_plugin_active('ecommerce-wholesale')) {
                // Wrap the resolved listener so Indian GST retains its pricing policy.
                $this->app->extend(
                    \Botble\EcommerceWholesale\Listeners\SaveOrderWholesalePrices::class,
                    fn ($listener) => new \SparroWave\FarmartHelper\Listeners\PreserveCheckoutWholesalePrices($listener)
                );
            }
            if (is_plugin_active('loyalty-points')) {
                $this->app->bind(\Botble\LoyaltyPoints\Listeners\SaveOrderLoyaltyPoints::class, \SparroWave\FarmartHelper\Listeners\FinalizeLoyaltySnapshot::class);
                add_action('ecommerce_before_processing_payment', [app(\SparroWave\FarmartHelper\Supports\FinalizeCheckoutDiscount::class), 'handle'], 110, 4);
            }

            $this->app->forgetInstance(\Botble\Ecommerce\Services\Footprints\FootprinterInterface::class);
            $this->app->singleton(\Botble\Ecommerce\Services\Footprints\FootprinterInterface::class, \SparroWave\FarmartHelper\Supports\SessionFootprinter::class);
        });

        // Decorate the existing dispatcher, retaining all registered listeners.
        // Action callbacks cannot stop propagation by returning false in this CMS.
        $this->app->extend('core.action', fn (BaseAction $actions) => new PaymentActionGuard($actions));
        Action::clearResolvedInstance('core.action');
    }

    public function boot(): void
    {
        if (is_plugin_active('ecommerce') && is_plugin_active('payment')) {
            $this->app['router']->pushMiddlewareToGroup('web', \SparroWave\FarmartHelper\Http\Middleware\RestartChangedCheckout::class);
            $this->app['router']->pushMiddlewareToGroup('web', GuardCheckoutSuccess::class);
            $this->app['router']->pushMiddlewareToGroup('web', \SparroWave\FarmartHelper\Http\Middleware\SerializeCheckout::class);
            $this->app['router']->pushMiddlewareToGroup('web', \SparroWave\FarmartHelper\Http\Middleware\RestartPaymentMode::class);
            add_filter(THEME_FRONT_FOOTER, fn ($html) => $html . view('plugins/farmart-helper::checkout.source')->render(), 150);
            add_filter('ecommerce_payment_attempt_locked', fn ($locked, $order) => $locked || (bool) app(\SparroWave\FarmartHelper\Supports\PaymentSnapshot::class)->read($order), 20, 2);
            add_filter('ecommerce_payment_attempt_inconsistent', function ($inconsistent, $order) {
                $snapshot = app(\SparroWave\FarmartHelper\Supports\PaymentSnapshot::class)->read($order);
                if (!$snapshot) return $inconsistent;
                $orders = \Botble\Ecommerce\Models\Order::where('token', $order->token)->get();
                $expected = apply_filters('ecommerce_expected_online_amount', (float) $orders->sum('amount'), $orders);
                return $inconsistent || (int) $snapshot['payable_paise'] !== (int) round($expected * 100);
            }, 20, 2);
            add_action('payment_before_making_api_request', [app(\SparroWave\FarmartHelper\Supports\PaymentSnapshot::class), 'before'], 5, 2);
            add_action('payment_after_api_response', [app(\SparroWave\FarmartHelper\Supports\PaymentSnapshot::class), 'after'], 100, 3);
            \Botble\Ecommerce\Models\Order::updating(fn ($order) => app(\SparroWave\FarmartHelper\Supports\PaymentSnapshot::class)->protect($order));
            foreach (['creating', 'updating', 'deleting'] as $event) {
                \Botble\Ecommerce\Models\OrderProduct::$event(function ($item) use ($event) {
                    $order = \Botble\Ecommerce\Models\Order::find($item->order_id);
                    if (!$order || !app(\SparroWave\FarmartHelper\Supports\PaymentSnapshot::class)->read($order)) return;
                    if ($event === 'updating' && !$item->isDirty(['price','qty','product_id','tax_amount'])) {
                        $old = $item->getOriginal('options') ?: []; $new = $item->options ?: [];
                        unset($old['extras']['wholesale_discount'], $new['extras']['wholesale_discount']);
                        if (empty($old['extras'])) unset($old['extras']);
                        if (empty($new['extras'])) unset($new['extras']);
                        if ($old == $new) return;
                    }
                    throw \Illuminate\Validation\ValidationException::withMessages(['payment'=> 'Payment has already started for this order. Its items and total cannot be changed.']);
                });
            }
        }
    }
}
