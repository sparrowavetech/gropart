<?php

namespace SparroWave\Shipmozo\Providers;

use SparroWave\Shipmozo\Commands\InitShipmozoCommand;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            InitShipmozoCommand::class,
        ]);
    }
}
