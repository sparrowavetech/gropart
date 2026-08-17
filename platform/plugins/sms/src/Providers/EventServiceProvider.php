<?php

namespace Botble\Sms\Providers;

use Botble\Sms\Events\SendSmsEvent;
use Botble\Sms\Listeners\OrderSmsListener;
use Botble\Sms\Listeners\SendSmsListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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
    ];

    public function boot(): void
    {
        parent::boot();

        foreach ([
            'Botble\Ecommerce\Events\OrderConfirmedEvent' => 'handleOrderConfirmed',
            'Botble\Ecommerce\Events\OrderCompletedEvent' => 'handleOrderCompleted',
            'Botble\Ecommerce\Events\OrderCancelledEvent' => 'handleOrderCancelled',
        ] as $event => $method) {
            if (class_exists($event)) {
                Event::listen($event, [OrderSmsListener::class, $method]);
            }
        }
    }
}
