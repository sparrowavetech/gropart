<?php

namespace FriendsOfBotble\FacebookCatalogFeed;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('fob_facebook_catalog_feed_logs');
    }
}
