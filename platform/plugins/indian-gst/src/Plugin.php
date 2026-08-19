<?php

namespace SparroWave\IndianGst;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function activated(): void
    {
        Setting::set([
            'indian_gst_enabled' => '1',
            'indian_gst_display_inclusive_price' => '1',
            'indian_gst_default_company_state' => 'Rajasthan',
            'indian_gst_default_company_state_code' => '08',
            'indian_gst_default_company_gstin' => '08AUBPA5903F1Z5',
        ]);
        Setting::save();

        // Auto-install and deploy Indian GST Invoice Template into storage
        $templateSource = plugin_path('indian-gst/resources/templates/invoice.tpl');
        $destination = storage_path('app/templates/ecommerce/invoice.tpl');
        if (File::exists($templateSource)) {
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
        if (Schema::hasTable('ec_products') && Schema::hasColumn('ec_products', 'hsn_code')) {
            Schema::table('ec_products', function ($table) {
                $table->dropColumn('hsn_code');
            });
        }

        if (Schema::hasTable('mp_stores')) {
            Schema::table('mp_stores', function ($table) {
                $columns = array_filter([
                    Schema::hasColumn('mp_stores', 'gstin') ? 'gstin' : null,
                    Schema::hasColumn('mp_stores', 'vendor_managed_shipping') ? 'vendor_managed_shipping' : null,
                ]);

                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
}
