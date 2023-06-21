<?php

namespace Botble\Chart\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;

class ChartServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this->setNamespace('core/chart')
            ->loadAndPublishViews();
    }
}
