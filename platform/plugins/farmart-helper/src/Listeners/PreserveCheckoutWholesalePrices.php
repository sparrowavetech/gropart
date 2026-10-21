<?php

namespace SparroWave\FarmartHelper\Listeners;

use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\EcommerceWholesale\Listeners\SaveOrderWholesalePrices;
use SparroWave\FarmartHelper\Supports\PaymentSnapshot;

class PreserveCheckoutWholesalePrices
{
    public function __construct(private readonly SaveOrderWholesalePrices $listener)
    {
    }

    public function handle(OrderPlacedEvent $event): void
    {
        if (app(PaymentSnapshot::class)->read($event->order)
            || apply_filters('ecommerce_order_checkout_locked', false, $event->order)) {
            return;
        }

        // Retain protection for historical inclusive orders if Indian GST is disabled.
        // When active, its existing listener owns inclusive and mixed-basket policy.
        if (! is_plugin_active('indian-gst')
            && $event->order->products->contains(fn ($item) => (bool) ($item->options['price_includes_tax'] ?? false))) {
            return;
        }

        $this->listener->handle($event);
    }
}
