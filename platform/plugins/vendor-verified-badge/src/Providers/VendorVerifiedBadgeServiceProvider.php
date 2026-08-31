<?php

namespace SparroWave\VendorVerifiedBadge\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\PanelSections\SettingMarketplacePanelSection;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Blade;
use SparroWave\VendorVerifiedBadge\Enums\ShopTypeEnum;
use SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper;

class VendorVerifiedBadgeServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! class_exists('Botble\Marketplace\Enums\ShopTypeEnum')) {
            class_alias(ShopTypeEnum::class, 'Botble\Marketplace\Enums\ShopTypeEnum');
        }
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce') || ! is_plugin_active('marketplace')) {
            return;
        }

        $this
            ->setNamespace('plugins/vendor-verified-badge')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes()
            ->publishAssets();

        $this->app->register(HookServiceProvider::class);

        $this->registerBladeDirectives();
        $this->registerStoreMacros();
        $this->registerThemeAssets();
        $this->registerSettingsMenu();
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('vendorBadges', function ($expression) {
            return "<?php echo \\SparroWave\\VendorVerifiedBadge\\Supports\\VendorBadgeHelper::renderBadges($expression); ?>";
        });

        Blade::directive('vendorVerified', function ($expression) {
            return "<?php echo \\SparroWave\\VendorVerifiedBadge\\Supports\\VendorBadgeHelper::renderVerifiedIcon($expression); ?>";
        });

        Blade::directive('vendorShopType', function ($expression) {
            return "<?php echo \\SparroWave\\VendorVerifiedBadge\\Supports\\VendorBadgeHelper::renderShopTypeBadge($expression); ?>";
        });

        Blade::directive('vendorProfileProgress', function () {
            return "<?php echo view('plugins/vendor-verified-badge::dashboard.progress-bar')->render(); ?>";
        });
    }

    protected function registerStoreMacros(): void
    {
        Store::macro('getBadgeHtmlAttribute', function () {
            return VendorBadgeHelper::renderBadges($this);
        });

        Store::macro('getVerifiedBadgeHtmlAttribute', function () {
            return VendorBadgeHelper::renderVerifiedIcon($this);
        });

        Store::macro('getShopTypeBadgeHtmlAttribute', function () {
            return VendorBadgeHelper::renderShopTypeBadge($this);
        });
    }

    protected function registerThemeAssets(): void
    {
        Theme::asset()
            ->usePath(false)
            ->add(
                'vendor-badge-css',
                asset('vendor/core/plugins/vendor-verified-badge/css/vendor-badge.css')
            )
            ->add(
                'vendor-badge-js',
                asset('vendor/core/plugins/vendor-verified-badge/js/vendor-badge.js'),
                ['jquery']
            );

        add_action(BASE_ACTION_ENQUEUE_SCRIPTS, function (): void {
            \Botble\Base\Facades\Assets::addScriptsDirectly('vendor/core/plugins/vendor-verified-badge/js/vendor-badge.js');
            \Botble\Base\Facades\Assets::addStylesDirectly('vendor/core/plugins/vendor-verified-badge/css/vendor-badge.css');
        });

        add_filter('theme_front_footer', function ($html) {
            if (auth('customer')->check() && auth('customer')->user()->is_vendor) {
                $store = auth('customer')->user()->store;
                if ($store && $store->id) {
                    $jsData = json_encode([
                        'isVerified' => (bool) $store->is_verified,
                        'verifiedHtml' => VendorBadgeHelper::renderVerifiedIcon($store),
                        'shopTypeHtml' => VendorBadgeHelper::renderShopTypeBadge($store),
                    ]);
                    $html .= "<script>window.vendorBadgeData = $jsData;</script>";
                }
            }

            return $html;
        }, 120);
    }

    protected function registerSettingsMenu(): void
    {
        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-vendor-verified-badge',
                    'priority' => 999,
                    'parent_id' => 'cms-plugins-marketplace',
                    'name' => trans('plugins/vendor-verified-badge::vendor-badge.name'),
                    'icon' => 'ti ti-discount-check',
                    'url' => fn () => route('vendor-verified-badge.settings'),
                    'permissions' => ['vendor-verified-badge.settings'],
                ]);
        });

        PanelSectionManager::default()->beforeRendering(function (): void {
            PanelSectionManager::registerItem(
                SettingMarketplacePanelSection::class,
                fn () => PanelSectionItem::make('vendor-verified-badge.settings')
                    ->setTitle(trans('plugins/vendor-verified-badge::vendor-badge.name'))
                    ->withIcon('ti ti-discount-check')
                    ->withDescription(trans('plugins/vendor-verified-badge::vendor-badge.settings.description'))
                    ->withPriority(150)
                    ->withRoute('vendor-verified-badge.settings')
            );
        });
    }
}
