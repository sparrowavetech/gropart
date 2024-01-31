<?php

namespace Skillcraft\DailyDo\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Skillcraft\DailyDo\Commands\ImportDailyDoCommand;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            ImportDailyDoCommand::class,
        ]);

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command(ImportDailyDoCommand::class)->daily();
        });
    }
}
