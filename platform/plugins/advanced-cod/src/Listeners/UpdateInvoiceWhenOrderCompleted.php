<?php
namespace SparroWave\AdvancedCod\Listeners;

use Botble\Ecommerce\Events\OrderCompletedEvent;
use SparroWave\AdvancedCod\Supports\Settlement;

/** Delivery completes fulfilment, not a partial COD receivable. */
class UpdateInvoiceWhenOrderCompleted extends \Botble\Ecommerce\Listeners\UpdateInvoiceWhenOrderCompleted
{
    public function handle(OrderCompletedEvent $event): void
    {
        if (Settlement::applies($event->order)) {
            Settlement::sync($event->order->fresh());
            return;
        }
        parent::handle($event);
    }
}
