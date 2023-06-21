<?php

namespace Botble\Location\Providers;

use Botble\Location\Commands\MigrateLocationCommand;
use Botble\Base\Supports\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            MigrateLocationCommand::class,
        ]);
    }
}
