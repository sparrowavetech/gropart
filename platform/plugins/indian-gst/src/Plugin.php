<?php

namespace SparroWave\IndianGst;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\File;

class Plugin extends PluginOperationAbstract
{
    public static function activated(): void
    {
        // Preserve settings and any customized invoice template on reactivation.
        $templateSource = plugin_path('indian-gst/resources/templates/invoice.tpl');
        $destination = storage_path('app/templates/ecommerce/invoice.tpl');
        if (File::exists($templateSource) && ! File::exists($destination)) {
            File::ensureDirectoryExists(dirname($destination));
            File::copy($templateSource, $destination);
        }
    }

    public static function deactivated(): void
    {
        // Keep settings intact on deactivation
    }

    public static function remove(): void
    {
        // Existing invoices and product/store tax identity must survive uninstall.
    }
}
