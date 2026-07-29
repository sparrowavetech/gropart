<?php

namespace Botble\Sms\Providers;

use Botble\Sms\Events\SendSmsEvent;
use Botble\Sms\Listeners\SendSmsListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        SendSmsEvent::class => [
            SendSmsListener::class,
        ],
        \Botble\Ecommerce\Events\OrderConfirmedEvent::class => [
            [\Botble\Sms\Listeners\OrderSmsListener::class, 'handleOrderConfirmed'],
        ],
        \Botble\Ecommerce\Events\OrderCompletedEvent::class => [
            [\Botble\Sms\Listeners\OrderSmsListener::class, 'handleOrderCompleted'],
        ],
        \Botble\Ecommerce\Events\OrderCancelledEvent::class => [
            [\Botble\Sms\Listeners\OrderSmsListener::class, 'handleOrderCancelled'],
        ],
    ];
   
}