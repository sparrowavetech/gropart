<?php

namespace Skillcraft\ContactManager;

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (self::pluginTables() as $table) {
            Schema::dropIfExists($table);
        }
    }

    public static function pluginModels():array
    {
        $pluginModels = [];

        $files = BaseHelper::scanFolder(__DIR__ . '/Models');

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $className = 'Skillcraft\\ContactManager\\Models\\' . pathinfo($file, PATHINFO_FILENAME);
                $pluginModels[] = new $className();
            }
        }
        return $pluginModels;
    }

    public static function pluginTables():array
    {
        $pluginTables = [];

        foreach (self::pluginModels() as $model) {
            $pluginTables[] = $model->getTable();
        }

        $pluginTables[] = 'contacts_tags';

        return $pluginTables;
    }

    public function pluginInstallSchema():void
    {
        foreach (self::pluginModels() as $model) {
            if (method_exists($model, 'modelInstallSchema')) {
                $model->modelInstallSchema();
            }
        }

        Schema::create('contacts_tags', function (Blueprint $table) {
            $table->id();
            $table->integer('tag_id')->unsigned()->references('id')->on('contact_tags')->onDelete('cascade');
            $table->integer('contact_id')->unsigned()->references('id')->on('contact_manager')->onDelete('cascade');
        });
    }
}
