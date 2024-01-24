<?php

namespace Skillcraft\ContactManager\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Skillcraft\Core\PanelSections\CorePanelSection;

class ContactManagerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (is_plugin_active('skillcraft-core') || is_plugin_active('contact-manager')) {
            return;
        }

        if (! is_plugin_active('sc-core')) {
            return;
        }
        
        $this
            ->setNamespace('plugins/sc-contact-manager')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();

        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::make()->registerItem([
                'id' => 'cms-plugins-contact-manager',
                'priority' => 1,
                'parent_id' => null,
                'name' => 'plugins/sc-contact-manager::contact-manager.name',
                'icon' => 'ti ti-user-plus',
                'url' => null,
                'permissions' => [],
            ])
                ->registerItem([
                    'id' => 'cms-plugins-contact-manager-2',
                    'priority' => 0,
                    'parent_id' => 'cms-plugins-contact-manager',
                    'name' => 'plugins/sc-contact-manager::contact-manager.name',
                    'icon' => null,
                    'url' => route('contact-manager.index'),
                    'permissions' => ['contact-manager.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-contact-manager.models.tag',
                    'priority' => 0,
                    'parent_id' => 'cms-plugins-contact-manager',
                    'name' => 'plugins/sc-contact-manager::contact-manager.models.tag.name',
                    'icon' => null,
                    'url' => route('contact-tag.index'),
                    'permissions' => ['contact-tag.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-contact-manager.models.group',
                    'priority' => 0,
                    'parent_id' => 'cms-plugins-contact-manager',
                    'name' => 'plugins/sc-contact-manager::contact-manager.models.group.name',
                    'icon' => null,
                    'url' => route('contact-group.index'),
                    'permissions' => ['contact-group.index'],
                ]);
        });

        PanelSectionManager::default()->beforeRendering(function () {
            PanelSectionManager::registerItem(
                CorePanelSection::class,
                fn() => PanelSectionItem::make('contact-manager')
                    ->setTitle(trans('plugins/sc-contact-manager::contact-manager.settings.title'))
                    ->withIcon('ti ti-user-plus')
                    ->withDescription(trans('plugins/sc-contact-manager::contact-manager.settings.description'))
                    ->withPriority(1)
                    ->withRoute('contact-manager.settings')
            );
        });

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });

        $this->app->register(EventServiceProvider::class);
    }
}
