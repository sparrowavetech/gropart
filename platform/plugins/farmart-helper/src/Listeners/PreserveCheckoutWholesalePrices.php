<?php
namespace SparroWave\FarmartHelper\Listeners;

use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\EcommerceWholesale\Listeners\SaveOrderWholesalePrices;
use SparroWave\FarmartHelper\Supports\PaymentSnapshot;

class PreserveCheckoutWholesalePrices extends SaveOrderWholesalePrices
{
    public function handle(OrderPlacedEvent $event): void
    {
        if (app(PaymentSnapshot::class)->read($event->order)
            || apply_filters('ecommerce_order_checkout_locked', false, $event->order)
            || $event->order->products->contains(fn ($item) => (bool)($item->options['price_includes_tax'] ?? false))) return;
        parent::handle($event);
    }
}
