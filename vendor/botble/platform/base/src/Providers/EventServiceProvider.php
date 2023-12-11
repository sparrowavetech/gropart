<?php

namespace Botble\Base\Providers;

use Botble\ACL\Events\RoleAssignmentEvent;
use Botble\ACL\Events\RoleUpdateEvent;
use Botble\Base\Events\AdminNotificationEvent;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\SendMailEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Events\UpdatedEvent;
use Botble\Base\Listeners\AdminNotificationListener;
use Botble\Base\Listeners\BeforeEditContentListener;
use Botble\Base\Listeners\ClearDashboardMenuCaches;
use Botble\Base\Listeners\ClearDashboardMenuCachesForLoggedUser;
use Botble\Base\Listeners\CreatedContentListener;
use Botble\Base\Listeners\DeletedContentListener;
use Botble\Base\Listeners\SendMailListener;
use Botble\Base\Listeners\UpdatedContentListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SendMailEvent::class => [
            SendMailListener::class,
        ],
        CreatedContentEvent::class => [
            CreatedContentListener::class,
        ],
        UpdatedContentEvent::class => [
            UpdatedContentListener::class,
        ],
        DeletedContentEvent::class => [
            DeletedContentListener::class,
        ],
        BeforeEditContentEvent::class => [
            BeforeEditContentListener::class,
        ],
        AdminNotificationEvent::class => [
            AdminNotificationListener::class,
        ],
        UpdatedEvent::class => [
            ClearDashboardMenuCaches::class,
        ],
        Login::class => [
            ClearDashboardMenuCachesForLoggedUser::class,
        ],
        RoleAssignmentEvent::class => [
            ClearDashboardMenuCaches::class,
        ],
        RoleUpdateEvent::class => [
            ClearDashboardMenuCaches::class,
        ],
    ];

    public function boot(): void
    {
        $this->app['events']->listen(['cache:cleared'], function () {
            $this->app['files']->delete(storage_path('cache_keys.json'));
        });
    }
}
