<?php

namespace Botble\Pickrr;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove()
    {
        Schema::dropIfExists('pickrrs');
        Schema::dropIfExists('pickrrs_translations');
    }
}
