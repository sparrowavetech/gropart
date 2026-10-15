<?php
namespace SparroWave\EnquiryProducts;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function activate(): void
    {
        if (! is_plugin_active('ecommerce') || ! class_exists(\Botble\Ecommerce\Models\Product::class)) {
            throw new \RuntimeException('Enquiry Products requires Ecommerce to be installed and activated first.');
        }
    }

    public static function deactivate(): void
    {
        foreach (['ec_products', 'ec_product_categories'] as $table) {
            if (Schema::hasColumn($table, 'is_enquiry') && DB::table($table)->where('is_enquiry', 1)->exists()) {
                throw new \RuntimeException('Convert enquiry products/categories to normal sale before deactivating Enquiry Products. This prevents accidental purchases.');
            }
        }
    }
    public static function remove(): void
    {
        // Enquiries, legacy flags and uploaded documents are retained deliberately.
    }
}
