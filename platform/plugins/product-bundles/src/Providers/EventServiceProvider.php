<?php

namespace Botble\ProductBundles\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\ProductBundles\Listeners\SaveBundleFromProductListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CreatedContentEvent::class => [
            SaveBundleFromProductListener::class,
        ],
        UpdatedContentEvent::class => [
            SaveBundleFromProductListener::class,
        ],
    ];
}
