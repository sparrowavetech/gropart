<?php

namespace Botble\Slug\Providers;

use Botble\Slug\Commands\ChangeSlugPrefixCommand;
use Botble\Base\Supports\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            ChangeSlugPrefixCommand::class,
        ]);
    }
}
