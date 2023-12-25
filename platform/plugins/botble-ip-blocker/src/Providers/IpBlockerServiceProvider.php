<?php

namespace ArchiElite\IpBlocker\Providers;

use ArchiElite\IpBlocker\Http\Middleware\IpBlockerMiddleware;
use ArchiElite\IpBlocker\Models\History;
use ArchiElite\IpBlocker\Repositories\Caches\IpBlockerCacheDecorator;
use ArchiElite\IpBlocker\Repositories\Eloquent\IpBlockerRepository;
use ArchiElite\IpBlocker\Repositories\Interfaces\IpBlockerInterface;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class IpBlockerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(IpBlockerInterface::class, function () {
            return new IpBlockerCacheDecorator(new IpBlockerRepository(new History()));
        });
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/ip-blocker')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadHelpers()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadMigrations()
            ->loadAndPublishTranslations();

        Event::listen(RouteMatched::class, function () {
            $this->app['router']->pushMiddlewareToGroup('web', IpBlockerMiddleware::class);

            DashboardMenu::registerItem([
                'id' => 'cms-plugins-ip-blocker',
                'priority' => 1001,
                'parent_id' => 'cms-core-settings',
                'name' => 'plugins/ip-blocker::ip-blocker.menu',
                'url' => route('ip-blocker.settings'),
                'permissions' => ['ip-blocker.settings'],
            ]);
        });

        if ($this->app->runningInConsole()) {
            $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
                $schedule->command(PruneCommand::class, ['--model' => History::class])->dailyAt('00:30');
            });
        }
    }
}
