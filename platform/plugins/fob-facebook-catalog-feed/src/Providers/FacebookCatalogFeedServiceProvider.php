<?php

namespace FriendsOfBotble\FacebookCatalogFeed\Providers;

use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\PanelSections\SettingEcommercePanelSection;
use FriendsOfBotble\FacebookCatalogFeed\Services\FacebookCatalogFeedService;
use FriendsOfBotble\FacebookCatalogFeed\Widgets\FacebookCatalogFeedWidget;

class FacebookCatalogFeedServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->singleton(FacebookCatalogFeedService::class);
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this
            ->setNamespace('plugins/fob-facebook-catalog-feed')
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadHelpers()
            ->loadRoutes()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadMigrations();

        $this->app->booted(function () {
            register_widget(FacebookCatalogFeedWidget::class);
            
            // Register settings in e-commerce settings panel
            PanelSectionManager::beforeRendering(function () {
                PanelSectionManager::default()->registerItem(
                    SettingEcommercePanelSection::class,
                    fn () => PanelSectionItem::make('settings.ecommerce.facebook_catalog_feed')
                        ->setTitle(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.name'))
                        ->withIcon('ti ti-rss')
                        ->withDescription(trans('plugins/fob-facebook-catalog-feed::fob-facebook-catalog-feed.description'))
                        ->withPriority(200)
                        ->withPermission('fob-facebook-catalog-feed.settings')
                        ->withRoute('fob-facebook-catalog-feed.settings')
                );
            });
        });
    }
}
