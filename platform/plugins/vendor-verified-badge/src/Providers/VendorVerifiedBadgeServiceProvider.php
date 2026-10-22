<?php

namespace SparroWave\VendorVerifiedBadge\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\MacroableModels;
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
        MacroableModels::addMacro(Store::class, 'getBadgeHtmlAttribute', function () {
            return VendorBadgeHelper::renderBadges($this);
        });

        MacroableModels::addMacro(Store::class, 'getVerifiedBadgeHtmlAttribute', function () {
            return VendorBadgeHelper::renderVerifiedIcon($this);
        });

        MacroableModels::addMacro(Store::class, 'getShopTypeBadgeHtmlAttribute', function () {
            return VendorBadgeHelper::renderShopTypeBadge($this);
        });
    }

    protected function registerThemeAssets(): void
    {
        Theme::asset()
            ->usePath(false)
            ->add(
                'vendor-badge-css',
                asset('vendor/core/plugins/vendor-verified-badge/css/vendor-badge.css') . '?v=20261020-2'
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
            $translations = [
                'are_you_a' => trans('plugins/vendor-verified-badge::vendor-badge.are_you_a'),
                'select_shop_type' => trans('plugins/vendor-verified-badge::vendor-badge.select_shop_type'),
                'types' => [
                    'manufacture' => trans('plugins/vendor-verified-badge::vendor-badge.types.manufacture'),
                    'wholesaler' => trans('plugins/vendor-verified-badge::vendor-badge.types.wholesaler'),
                    'retailer' => trans('plugins/vendor-verified-badge::vendor-badge.types.retailer'),
                ],
            ];
            $jsTranslations = json_encode($translations);
            $html .= "<script>window.vendorBadgeTranslations = $jsTranslations;</script>";

            if (auth('customer')->check() && auth('customer')->user()->is_vendor) {
                $store = auth('customer')->user()->store;
                if ($store && $store->id) {
                    $jsData = json_encode([
                        'isVerified' => (bool) $store->is_verified,
                        'verifiedHtml' => (string) VendorBadgeHelper::renderVerifiedIcon($store),
                        'shopTypeHtml' => (string) VendorBadgeHelper::renderShopTypeBadge($store),
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
