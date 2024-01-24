<?php

namespace Skillcraft\ContactManager\Providers;

use Botble\Contact\Events\SentContactEvent;
use Skillcraft\ContactManager\Models\ContactManager;
use Skillcraft\ContactManager\Listeners\SentContactListener;
use Skillcraft\ContactManager\Observers\ContactManagerObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SentContactEvent::class => [
            SentContactListener::class,
        ],
    ];

    protected $observers = [
        ContactManager::class => [
            ContactManagerObserver::class
        ],
    ];
}
