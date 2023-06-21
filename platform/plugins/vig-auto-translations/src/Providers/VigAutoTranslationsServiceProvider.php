<?php

namespace VigStudio\VigAutoTranslations\Providers;

use Illuminate\Support\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;
use VigStudio\VigAutoTranslations\Commands\AutoTranslateCommand;
use VigStudio\VigAutoTranslations\Manager;
use VigStudio\VigAutoTranslations\Services\AWSTranslator;
use VigStudio\VigAutoTranslations\Services\ChatGPTTranslator;
use VigStudio\VigAutoTranslations\Services\GoogleTranslator;

class VigAutoTranslationsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->setNamespace('plugins/vig-auto-translations')->loadHelpers();

        $this->app->singleton(Manager::class, function () {
            $manager = new Manager();

            $driver = setting('vig_translate_driver');

            if ($driver === 'chatgpt') {
                return $manager->setDriver(new ChatGPTTranslator());
            }

            if ($driver === 'aws') {
                return $manager->setDriver(new AWSTranslator());
            }

            return $manager->setDriver(new GoogleTranslator());
        });
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();

        $this->commands(AutoTranslateCommand::class);

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });

        $this->app['events']->listen(RouteMatched::class, function () {
            dashboard_menu()
                ->registerItem([
                    'id' => 'cms-plugins-vig-auto-translations',
                    'priority' => 80,
                    'parent_id' => 'cms-plugin-translation',
                    'name' => 'plugins/vig-auto-translations::vig-auto-translations.name_theme',
                    'icon' => null,
                    'url' => route('vig-auto-translations.theme'),
                    'permissions' => ['vig-auto-translations.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-vig-auto-translations-plugin',
                    'priority' => 81,
                    'parent_id' => 'cms-plugin-translation',
                    'name' => 'plugins/vig-auto-translations::vig-auto-translations.name_plugin',
                    'icon' => null,
                    'url' => route('vig-auto-translations.plugin'),
                    'permissions' => ['vig-auto-translations.index'],
                ]);
        });
    }
}
