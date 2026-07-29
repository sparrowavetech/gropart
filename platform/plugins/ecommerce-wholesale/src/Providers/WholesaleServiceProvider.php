<?php

namespace Botble\EcommerceWholesale\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenu as DashboardMenuSupport;
use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Http\Middleware\InjectWholesaleBoxLoader;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Repositories\Eloquent\CustomerGroupRepository;
use Botble\EcommerceWholesale\Repositories\Interfaces\CustomerGroupInterface;
use Botble\EcommerceWholesale\Supports\WholesaleHelper as WholesaleHelperSupport;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class WholesaleServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this->app->singleton(WholesaleHelperSupport::class);

        $this->app->bind(CustomerGroupInterface::class, function () {
            return new CustomerGroupRepository(new CustomerGroup());
        });

        Helper::autoload(__DIR__ . '/../../helpers');

        AliasLoader::getInstance()->alias('WholesaleHelper', WholesaleHelper::class);
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this
            ->setNamespace('plugins/ecommerce-wholesale')
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'public', 'customer'])
            ->publishAssets();

        if (is_plugin_active('marketplace')) {
            $this->loadRoutes(['vendor']);
        }

        $this->app['router']->pushMiddlewareToGroup('web', InjectWholesaleBoxLoader::class);

        DashboardMenu::beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-wholesale',
                    'priority' => 50,
                    'parent_id' => null,
                    'name' => 'plugins/ecommerce-wholesale::wholesale.name',
                    'icon' => 'ti ti-building-warehouse',
                    'url' => fn () => route('wholesale.customer-groups.index'),
                    'permissions' => ['wholesale.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-wholesale-customer-groups',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-wholesale',
                    'name' => 'plugins/ecommerce-wholesale::wholesale.customer_groups',
                    'icon' => 'ti ti-users-group',
                    'url' => fn () => route('wholesale.customer-groups.index'),
                    'permissions' => ['wholesale.customer-groups.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-wholesale-pricing-rules',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-wholesale',
                    'name' => 'plugins/ecommerce-wholesale::wholesale.pricing_rules',
                    'icon' => 'ti ti-receipt',
                    'url' => fn () => route('wholesale.pricing-rules.index'),
                    'permissions' => ['wholesale.pricing-rules.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-wholesale-products',
                    'priority' => 3,
                    'parent_id' => 'cms-plugins-wholesale',
                    'name' => 'plugins/ecommerce-wholesale::wholesale.wholesale_products',
                    'icon' => 'ti ti-packages',
                    'url' => fn () => route('wholesale.products.index'),
                    'permissions' => ['wholesale.products.index'],
                ])
                ->when(
                    WholesaleHelper::isApprovalRequired(),
                    function (DashboardMenuSupport $dashboardMenu): void {
                        $dashboardMenu
                            ->registerItem([
                                'id' => 'cms-plugins-wholesale-applications',
                                'priority' => 4,
                                'parent_id' => 'cms-plugins-wholesale',
                                'name' => 'plugins/ecommerce-wholesale::wholesale.applications',
                                'icon' => 'ti ti-file-text',
                                'url' => fn () => route('wholesale.applications.index'),
                                'permissions' => ['wholesale.applications.index'],
                            ]);
                    }
                )
                ->registerItem([
                    'id' => 'cms-plugins-wholesale-settings',
                    'priority' => 99,
                    'parent_id' => 'cms-plugins-wholesale',
                    'name' => 'plugins/ecommerce-wholesale::wholesale.settings',
                    'icon' => 'ti ti-settings',
                    'url' => fn () => route('wholesale.settings'),
                    'permissions' => ['wholesale.settings'],
                ]);
        });

        $this->app->register(HookServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }
}
