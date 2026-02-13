<?php

namespace Botble\ContentInjector;


use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('ContentInjectors');
        Schema::dropIfExists('ContentInjectors_translations');
    }
}
