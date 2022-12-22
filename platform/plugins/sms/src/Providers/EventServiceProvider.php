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
        ]
    ];
   
}