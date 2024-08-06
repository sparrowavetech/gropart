<?php

namespace Botble\Sms;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove()
    {
        Schema::dropIfExists('sms');
        Schema::dropIfExists('sms_translations');
    }
}
