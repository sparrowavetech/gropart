<?php

namespace Botble\LoyaltyPoints\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Review;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\LoyaltyPoints\Console\AwardBirthdayPointsCommand;
use Botble\LoyaltyPoints\Console\Commands\RecalculatePointsCommand;
use Botble\LoyaltyPoints\Console\ExpirePointsCommand;
use Botble\LoyaltyPoints\Helpers\LoyaltyHelper;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\LoyaltyPoints\Models\LoyaltyLevel;
use Botble\LoyaltyPoints\Models\OrderLoyaltyPoints;
use Botble\LoyaltyPoints\Models\PointTransaction;
use Botble\LoyaltyPoints\Observers\ReviewObserver;
use Botble\LoyaltyPoints\Services\LoyaltyCardPdfService;
use Botble\LoyaltyPoints\Services\LoyaltyCardService;
use Botble\LoyaltyPoints\Services\LoyaltyEmailService;
use Botble\LoyaltyPoints\Services\LoyaltyMemberService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

if (! defined('LOYALTY_POINTS_MODULE_SCREEN_NAME')) {
    define('LOYALTY_POINTS_MODULE_SCREEN_NAME', 'loyalty-points');
}

class LoyaltyPointsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->setNamespace('plugins/loyalty-points')->loadHelpers();

        $this->app->singleton(LoyaltyHelper::class);
        $this->app->singleton(LoyaltyCardService::class);
        $this->app->singleton(LoyaltyCardPdfService::class);
        $this->app->singleton(LoyaltyMemberService::class);
        $this->app->singleton(LoyaltyEmailService::class);

        $this->app->register(EventServiceProvider::class);

        $this->app->register(HookServiceProvider::class);
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishConfigurations(['permissions', 'email'])
            ->loadAndPublishTranslations()
            ->loadRoutes(['base', 'customer'])
            ->loadAndPublishViews()
            ->publishAssets()
            ->loadHelpers()
            ->loadMigrations();

        $this->app->booted(function (): void {
            EmailHandler::addTemplateSettings(
                LOYALTY_POINTS_MODULE_SCREEN_NAME,
                config('plugins.loyalty-points.email', [])
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                AwardBirthdayPointsCommand::class,
                ExpirePointsCommand::class,
                RecalculatePointsCommand::class,
            ]);
        }

        $this->app->booted(function (): void {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('loyalty:award-birthday-points')->daily();
            $schedule->command('loyalty:expire-points')->daily();
        });

        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-loyalty-points',
                    'priority' => 900,
                    'parent_id' => null,
                    'name' => 'plugins/loyalty-points::loyalty-points.name',
                    'icon' => 'ti ti-gift',
                    'permissions' => ['loyalty-points.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-loyalty-points-reports',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-loyalty-points',
                    'name' => 'plugins/loyalty-points::loyalty-points.menu.reports',
                    'icon' => 'ti ti-chart-bar',
                    'url' => route('loyalty-points.index'),
                    'permissions' => ['loyalty-points.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-loyalty-points-members',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-loyalty-points',
                    'name' => 'plugins/loyalty-points::loyalty-points.members.menu_name',
                    'icon' => 'ti ti-users',
                    'url' => route('loyalty-points.members.index'),
                    'permissions' => ['loyalty-points.members.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-loyalty-points-levels',
                    'priority' => 3,
                    'parent_id' => 'cms-plugins-loyalty-points',
                    'name' => 'plugins/loyalty-points::loyalty-points.levels.menu_name',
                    'icon' => 'ti ti-award',
                    'url' => route('loyalty-points.levels.index'),
                    'permissions' => ['loyalty-points.levels.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-loyalty-points-settings',
                    'priority' => 4,
                    'parent_id' => 'cms-plugins-loyalty-points',
                    'name' => 'plugins/loyalty-points::loyalty-points.menu.settings',
                    'icon' => 'ti ti-settings',
                    'url' => route('loyalty-points.settings.index'),
                    'permissions' => ['loyalty-points.settings'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-loyalty-points-license',
                    'priority' => 9,
                    'parent_id' => 'cms-plugins-loyalty-points',
                    'name' => 'plugins/loyalty-points::loyalty-points.license.title',
                    'icon' => 'ti ti-key',
                    'url' => fn () => route('loyalty-points.license.index'),
                    'permissions' => ['loyalty-points.license'],
                ]);
        });

        DashboardMenu::for('customer')->beforeRetrieving(function (): void {
            $loyaltyHelper = app(LoyaltyHelper::class);

            if (! $loyaltyHelper->isEnabled()) {
                return;
            }

            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-customer-loyalty-points',
                    'priority' => 100,
                    'name' => trans('plugins/loyalty-points::loyalty-points.menu.customer_loyalty_points'),
                    'url' => fn () => route('customer.loyalty-points.index'),
                    'icon' => 'ti ti-gift',
                ]);
        });

        Customer::resolveRelationUsing('pointBalance', function ($model) {
            return $model->hasOne(CustomerPointBalance::class, 'customer_id');
        });

        Customer::resolveRelationUsing('pointTransactions', function ($model) {
            return $model->hasMany(PointTransaction::class, 'customer_id');
        });

        Order::resolveRelationUsing('loyaltyPoints', function ($model) {
            return $model->hasOne(OrderLoyaltyPoints::class, 'order_id');
        });

        if (defined('LANGUAGE_MODULE_SCREEN_NAME') && defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            LanguageAdvancedManager::registerModule(LoyaltyLevel::class, [
                'name',
                'benefits',
            ]);
        }

        // Register Review observer for model events (fallback for older ecommerce versions)
        Review::observe(ReviewObserver::class);
    }
}
