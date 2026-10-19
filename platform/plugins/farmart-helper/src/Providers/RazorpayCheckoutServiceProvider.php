<?php
namespace SparroWave\FarmartHelper\Providers;

use Illuminate\Support\ServiceProvider;
use Botble\Razorpay\Providers\HookServiceProvider;
use SparroWave\FarmartHelper\Hooks\DeferredRazorpayCheckout;

/** Adapt the author's Razorpay checkout without changing its configured presentation mode. */
class RazorpayCheckoutServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!is_plugin_active('razorpay')) return;
        $this->app->booted(function () {
            $adapter = new DeferredRazorpayCheckout($this->app);
            foreach ([PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS => 'registerRazorpayMethod', PAYMENT_FILTER_AFTER_POST_CHECKOUT => 'checkoutWithRazorpay'] as $hook => $method) {
                $listeners = get_hooks($hook);
                remove_filter($hook);
                foreach ($listeners as $priority => $listener) {
                    $callback = $listener['callback'];
                    if (is_array($callback) && $callback[0] instanceof HookServiceProvider && $callback[1] === $method) $callback = [$adapter, $method];
                    add_filter($hook, $callback, $priority, $listener['arguments']);
                }
            }
        });
    }
}
