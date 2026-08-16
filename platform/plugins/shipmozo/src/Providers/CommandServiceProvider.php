<?php

namespace SparroWave\Shipmozo\Providers;

use Illuminate\Support\ServiceProvider;
use SparroWave\Shipmozo\Commands\InitShipmozoCommand;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            InitShipmozoCommand::class,
        ]);
    }
}
