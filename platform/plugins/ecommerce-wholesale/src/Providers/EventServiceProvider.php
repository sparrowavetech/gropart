<?php

namespace Botble\EcommerceWholesale\Providers;

use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\EcommerceWholesale\Listeners\SaveOrderWholesalePrices;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderPlacedEvent::class => [
            SaveOrderWholesalePrices::class,
        ],
    ];
}
