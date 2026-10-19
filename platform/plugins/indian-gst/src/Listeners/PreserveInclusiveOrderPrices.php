<?php
namespace SparroWave\IndianGst\Listeners;

use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\EcommerceWholesale\Listeners\SaveOrderWholesalePrices;

class PreserveInclusiveOrderPrices extends SaveOrderWholesalePrices
{
    public function handle(OrderPlacedEvent $event): void
    {
        // Checkout has already applied sale/wholesale prices. Repricing an inclusive
        // basket after payment loses its net subtotal and charges its GST twice.
        // Preserve the entire snapshot for mixed baskets too, including discounts.
        if ($event->order->products()->get()->contains(fn ($item) => (bool) ($item->options['price_includes_tax'] ?? false))) {
            return;
        }
        parent::handle($event);
    }
}
