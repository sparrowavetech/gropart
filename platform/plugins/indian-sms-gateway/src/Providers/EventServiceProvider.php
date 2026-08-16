<?php

namespace Ashikul\IndiaSmsGateway\Providers;

use Ashikul\IndiaSmsGateway\Listeners\HandleCustomerRegistered;
use Ashikul\IndiaSmsGateway\Listeners\HandleEcommerceOrderEvent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            HandleCustomerRegistered::class,
        ],
        \Botble\Ecommerce\Events\OrderCreated::class => [
            HandleEcommerceOrderEvent::class,
        ],
        \Botble\Ecommerce\Events\OrderPlacedEvent::class => [
            HandleEcommerceOrderEvent::class,
        ],
        \Botble\Ecommerce\Events\OrderConfirmedEvent::class => [
            HandleEcommerceOrderEvent::class,
        ],
        \Botble\Ecommerce\Events\OrderCompletedEvent::class => [
            HandleEcommerceOrderEvent::class,
        ],
        \Botble\Ecommerce\Events\OrderCancelledEvent::class => [
            HandleEcommerceOrderEvent::class,
        ],
        \Botble\Ecommerce\Events\OrderReturnedEvent::class => [
            HandleEcommerceOrderEvent::class,
        ],
        \Botble\Ecommerce\Events\OrderPaymentConfirmedEvent::class => [
            HandleEcommerceOrderEvent::class,
        ],
        \Botble\Ecommerce\Events\ShippingStatusChanged::class => [
            HandleEcommerceOrderEvent::class,
        ],
    ];
}
