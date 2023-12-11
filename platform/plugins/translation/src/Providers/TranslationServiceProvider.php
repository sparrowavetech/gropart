<?php

namespace Botble\Translation\Providers;

use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Translation\Console\CleanCommand;
use Botble\Translation\Console\DownloadLocaleCommand;
use Botble\Translation\Console\ExportCommand;
use Botble\Translation\Console\ImportCommand;
use Botble\Translation\Console\RemoveLocaleCommand;
use Botble\Translation\Console\RemoveUnusedTranslationsCommand;
use Botble\Translation\Console\ResetCommand;
use Botble\Translation\Console\UpdateThemeTranslationCommand;
use Botble\Translation\Models\Translation;
use Botble\Translation\PanelSections\LocalizationPanelSection;
use Botble\Translation\Repositories\Eloquent\TranslationRepository;
use Botble\Translation\Repositories\Interfaces\TranslationInterface;

class TranslationServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(TranslationInterface::class, function () {
            return new TranslationRepository(new Translation());
        });
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/translation')
            ->loadAndPublishConfigurations(['general', 'permissions'])
            ->loadMigrations()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets();

        PanelSectionManager::beforeRendering(function () {
            PanelSectionManager::register(LocalizationPanelSection::class);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportCommand::class,
                ResetCommand::class,
                ExportCommand::class,
                CleanCommand::class,
                UpdateThemeTranslationCommand::class,
                RemoveUnusedTranslationsCommand::class,
                DownloadLocaleCommand::class,
                RemoveLocaleCommand::class,
            ]);
        }
    }
}
