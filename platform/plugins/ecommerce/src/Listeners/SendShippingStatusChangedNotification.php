<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Events\ShippingStatusChanged;
use EmailHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use OrderHelper;

class SendShippingStatusChangedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ShippingStatusChanged $event): void
    {
        if ($event->shipment->status == ShippingStatusEnum::DELIVERING) {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);
            if ($mailer->templateEnabled('customer_delivery_order')) {
                $order = $event->shipment->order;

                OrderHelper::setEmailVariables($order);
                $mailer->sendUsingTemplate(
                    'customer_delivery_order',
                    $order->user->email ?: $order->address->email
                );
            }
        }
    }
}
