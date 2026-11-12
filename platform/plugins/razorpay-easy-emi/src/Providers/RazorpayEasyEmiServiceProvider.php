<?php

namespace SparroWave\RazorpayEasyEmi\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\PanelSections\SettingEcommercePanelSection;

class RazorpayEasyEmiServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->register(HookServiceProvider::class);
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/razorpay-easy-emi')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web'])
            ->publishAssets();

        // 1. Register in Admin Ecommerce Settings Panel
        PanelSectionManager::default()->beforeRendering(function () {
            PanelSectionManager::registerItem(
                SettingEcommercePanelSection::class,
                fn () => PanelSectionItem::make('ecommerce.settings.razorpay-easy-emi')
                    ->setTitle(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.title'))
                    ->withIcon('ti ti-credit-card')
                    ->withDescription(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.settings.description'))
                    ->withPriority(175)
                    ->withPermission('razorpay-easy-emi.settings')
                    ->withRoute('razorpay-easy-emi.settings')
            );
        });

        // 2. Register EMI Requests under Marketplace Menu in Admin
        DashboardMenu::beforeRetrieving(function (): void {
            DashboardMenu::make()->registerItem([
                'id' => 'sparrowave-razorpay-emi-requests',
                'priority' => 17,
                'parent_id' => 'cms-plugins-marketplace',
                'name' => 'plugins/razorpay-easy-emi::razorpay-easy-emi.admin_menu.emi_requests',
                'icon' => 'ti ti-credit-card',
                'url' => fn () => route('razorpay-easy-emi.requests.index'),
                'permissions' => ['razorpay-easy-emi.requests.index'],
            ]);
        });
    }
}

