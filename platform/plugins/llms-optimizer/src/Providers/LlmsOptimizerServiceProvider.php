<?php

namespace Shaqi\LlmsOptimizer\Providers;

use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\PanelSections\SettingOthersPanelSection;
use Shaqi\LlmsOptimizer\Services\LlmsGeneratorService;
use Shaqi\LlmsOptimizer\Supports\LlmsOptimizerHelper;

class LlmsOptimizerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->singleton(LlmsGeneratorService::class, function ($app) {
            return new LlmsGeneratorService();
        });

        $this->app->singleton(LlmsOptimizerHelper::class, function ($app) {
            return new LlmsOptimizerHelper();
        });
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/llms-optimizer')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['general'])
            ->loadAndPublishViews()
            ->loadRoutes(['web']);

        PanelSectionManager::default()->beforeRendering(function (): void {
            PanelSectionManager::registerItem(
                SettingOthersPanelSection::class,
                fn () => PanelSectionItem::make('llms-optimizer')
                    ->setTitle('LLMS Optimizer')
                    ->withIcon('ti ti-robot')
                    ->withPriority(440)
                    ->withDescription('Configure llms.txt generation for AI crawlers')
                    ->withRoute('llms-optimizer.settings.index')
            );
        });

        $this->app->booted(function (): void {
            $this->app->register(HookServiceProvider::class);
        });
    }
}

