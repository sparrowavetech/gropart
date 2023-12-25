<?php

namespace Botble\Base\Providers;

use App\Http\Middleware\VerifyCsrfToken;
use Botble\Base\Contracts\GlobalSearchableManager as GlobalSearchableManagerContract;
use Botble\Base\Contracts\PanelSections\Manager;
use Botble\Base\Events\PanelSectionsRendering;
use Botble\Base\Exceptions\Handler;
use Botble\Base\Facades\AdminAppearance;
use Botble\Base\Facades\AdminHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Breadcrumb as BreadcrumbFacade;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\Facades\PanelSectionManager as PanelSectionManagerFacade;
use Botble\Base\Forms\Form;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Forms\FormHelper;
use Botble\Base\GlobalSearch\GlobalSearchableManager;
use Botble\Base\Hooks\EmailSettingHooks;
use Botble\Base\Http\Middleware\AdminLocaleMiddleware;
use Botble\Base\Http\Middleware\CoreMiddleware;
use Botble\Base\Http\Middleware\DisableInDemoModeMiddleware;
use Botble\Base\Http\Middleware\EnsureLicenseHasBeenActivated;
use Botble\Base\Http\Middleware\HttpsProtocolMiddleware;
use Botble\Base\Http\Middleware\LocaleMiddleware;
use Botble\Base\Listeners\PushDashboardMenuToSystemPanel;
use Botble\Base\Models\AdminNotification;
use Botble\Base\Models\BaseModel;
use Botble\Base\Models\MetaBox as MetaBoxModel;
use Botble\Base\PanelSections\System\SystemPanelSection;
use Botble\Base\Repositories\Eloquent\MetaBoxRepository;
use Botble\Base\Repositories\Interfaces\MetaBoxInterface;
use Botble\Base\Supports\Action;
use Botble\Base\Supports\Breadcrumb;
use Botble\Base\Supports\BreadcrumbsManager;
use Botble\Base\Supports\CustomResourceRegistrar;
use Botble\Base\Supports\Database\Blueprint;
use Botble\Base\Supports\Filter;
use Botble\Base\Supports\GoogleFonts;
use Botble\Base\Supports\Helper;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Widgets\AdminWidget;
use Botble\Base\Widgets\Contracts\AdminWidget as AdminWidgetContract;
use Botble\Setting\Providers\SettingServiceProvider;
use Botble\Setting\Supports\SettingStore;
use Botble\Support\Http\Middleware\BaseMiddleware;
use DateTimeZone;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Schema\Builder;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Throwable;

class BaseServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected bool $defer = true;

    public function register(): void
    {
        $this->app->instance('core.middleware', []);

        $this->app->bind(ResourceRegistrar::class, function ($app) {
            return new CustomResourceRegistrar($app['router']);
        });

        $this
            ->setNamespace('core/base')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['general']);

        $this->app->register(SettingServiceProvider::class);

        $this->app->singleton(ExceptionHandler::class, Handler::class);

        $this->app->singleton(Breadcrumb::class);

        $this->app->singleton(BreadcrumbsManager::class, BreadcrumbsManager::class);

        $this->app->singleton('core.panel-sections.manager', function () {
            return new \Botble\Base\PanelSections\Manager('settings');
        });
        $this->app->alias('core.panel-sections.manager', Manager::class);

        $globalSearchManager = 'core.global-search.manager';
        $this->app->singleton($globalSearchManager, GlobalSearchableManager::class);
        $this->app->alias($globalSearchManager, GlobalSearchableManagerContract::class);

        $this->app->bind(MetaBoxInterface::class, function () {
            return new MetaBoxRepository(new MetaBoxModel());
        });

        $this->app['config']->set([
            'session.cookie' => 'botble_session',
            'ziggy.except' => ['debugbar.*'],
            'app.debug_blacklist' => [
                '_ENV' => [
                    'APP_KEY',
                    'ADMIN_DIR',
                    'DB_DATABASE',
                    'DB_USERNAME',
                    'DB_PASSWORD',
                    'REDIS_PASSWORD',
                    'MAIL_PASSWORD',
                    'PUSHER_APP_KEY',
                    'PUSHER_APP_SECRET',
                ],
                '_SERVER' => [
                    'APP_KEY',
                    'ADMIN_DIR',
                    'DB_DATABASE',
                    'DB_USERNAME',
                    'DB_PASSWORD',
                    'REDIS_PASSWORD',
                    'MAIL_PASSWORD',
                    'PUSHER_APP_KEY',
                    'PUSHER_APP_SECRET',
                ],
                '_POST' => [
                    'password',
                ],
            ],
            'datatables-buttons.pdf_generator' => 'excel',
            'excel.exports.csv.use_bom' => true,
            'dompdf.public_path' => public_path(),
            'debugbar.enabled' => $this->app->hasDebugModeEnabled() &&
                ! $this->app->runningInConsole() &&
                ! $this->app->environment(['testing', 'production']),
            'debugbar.capture_ajax' => false,
            'laravel-form-builder.plain_form_class' => Form::class,
            'laravel-form-builder.form_builder_class' => FormBuilder::class,
            'laravel-form-builder.form_helper_class' => FormHelper::class,
        ]);

        $this->app->singleton('core.action', function () {
            return new Action();
        });

        $this->app->singleton('core.filter', function () {
            return new Filter();
        });

        $this->app->singleton(AdminWidgetContract::class, AdminWidget::class);

        $this->app->singleton('core.google-fonts', function (Application $app) {
            return new GoogleFonts(
                filesystem: $app->make(FilesystemManager::class)->disk('public'),
                path: 'fonts',
                inline: true,
                userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15',
            );
        });

        Route::macro('wherePrimaryKey', function (array|string|null $name = 'id') {
            if (! $name) {
                $name = 'id';
            }

            /**
             * @var Route $this
             */
            if (BaseModel::determineIfUsingUuidsForId()) {
                return $this->whereUuid($name);
            }

            if (BaseModel::determineIfUsingUlidsForId()) {
                return $this->whereUlid($name);
            }

            return $this->whereNumber($name);
        });

        $aliasLoader = AliasLoader::getInstance();

        if (! class_exists('BaseHelper')) {
            $aliasLoader->alias('BaseHelper', BaseHelper::class);
            $aliasLoader->alias('DashboardMenu', DashboardMenu::class);
            $aliasLoader->alias('PageTitle', PageTitle::class);
        }

        if (! class_exists('Breadcrumb')) {
            $aliasLoader->alias('Breadcrumb', BreadcrumbFacade::class);
        }

        if (! class_exists('PanelSectionManager')) {
            $aliasLoader->alias('PanelSectionManager', PanelSectionManagerFacade::class);
        }

        if (! class_exists('AdminAppearance')) {
            $aliasLoader->alias('AdminAppearance', AdminAppearance::class);
        }

        if (! class_exists('AdminHelper')) {
            $aliasLoader->alias('AdminHelper', AdminHelper::class);
        }
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishConfigurations(['permissions', 'assets'])
            ->loadAndPublishViews()
            ->loadAnonymousComponents()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadMigrations()
            ->publishAssets();

        $config = $this->app['config'];

        $config->set('laravel-form-builder.defaults', [
            ...$config->get('laravel-form-builder.defaults', []),
            'wrapper_class' => 'mb-3 position-relative',
            'label_class' => 'form-label',
            'field_error_class' => 'is-invalid',
            'help_block_class' => 'form-hint',
            'error_class' => 'invalid-feedback',
            'help_block_tag' => 'small',
            'select' => [
                'field_class' => 'form-select',
            ],
        ]);

        $this->app['blade.compiler']->anonymousComponentPath($this->getViewsPath() . '/components', 'core');

        Schema::defaultStringLength(191);

        if (BaseHelper::hasDemoModeEnabled() || $config->get('core.base.general.disable_verify_csrf_token', false)) {
            $this->app->instance(VerifyCsrfToken::class, new BaseMiddleware());
        }

        $this->app->booted(function () use ($config) {
            do_action(BASE_ACTION_INIT);
            add_action(BASE_ACTION_META_BOXES, [MetaBox::class, 'doMetaBoxes'], 8, 2);
            add_filter(
                BASE_FILTER_AFTER_SETTING_EMAIL_CONTENT,
                [EmailSettingHooks::class, 'addEmailTemplateSettings'],
                99
            );

            add_filter(BASE_FILTER_TOP_HEADER_LAYOUT, function ($options) {
                try {
                    $countNotificationUnread = AdminNotification::countUnread();
                } catch (Throwable) {
                    $countNotificationUnread = 0;
                }

                return $options . view('core/base::notification.nav-item', compact('countNotificationUnread'));
            }, 99);

            add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function ($html) {
                if (! Auth::guard()->check()) {
                    return $html;
                }

                return $html . view('core/base::notification.notification');
            }, 99);

            $setting = $this->app[SettingStore::class];
            $timezone = $setting->get('time_zone', $config->get('app.timezone'));
            $locale = $setting->get('locale', $config->get('core.base.general.locale', $config->get('app.locale')));

            $config->set([
                'app.locale' => $locale,
                'app.timezone' => $timezone,
            ]);

            $this->app->setLocale($locale);

            if (in_array($timezone, DateTimeZone::listIdentifiers())) {
                date_default_timezone_set($timezone);
            }
        });

        $this->registerDashboardMenus();

        $events = $this->app['events'];

        $events->listen(RouteMatched::class, function () {
            $this->registerPanelSections();

            /**
             * @var Router $router
             */
            $router = $this->app['router'];

            $router->pushMiddlewareToGroup('web', LocaleMiddleware::class);
            $router->pushMiddlewareToGroup('web', AdminLocaleMiddleware::class);
            $router->pushMiddlewareToGroup('web', HttpsProtocolMiddleware::class);
            $router->aliasMiddleware('preventDemo', DisableInDemoModeMiddleware::class);
            $router->middlewareGroup('core', [CoreMiddleware::class]);

            $this->app->extend('core.middleware', function ($middleware) {
                return array_merge($middleware, [
                    EnsureLicenseHasBeenActivated::class,
                ]);
            });
        });

        Paginator::useBootstrap();

        $baseConfig = $config->get('core.base.general', []);

        $forceUrl = Arr::get($baseConfig, 'force_root_url');
        if (! empty($forceUrl)) {
            URL::forceRootUrl($forceUrl);
        }

        $forceSchema = Arr::get($baseConfig, 'force_schema');
        if (! empty($forceSchema)) {
            $this->app['request']->server->set('HTTPS', 'on');

            URL::forceScheme($forceSchema);
        }

        $this->configureIni();

        $config->set([
            'purifier.settings' => array_merge(
                $config->get('purifier.settings', []),
                Arr::get($baseConfig, 'purifier', [])
            ),
            'laravel-form-builder.defaults.wrapper_class' => 'mb-3 position-relative',
            'database.connections.mysql.strict' => Arr::get($baseConfig, 'db_strict_mode'),
        ]);

        if (
            ! $config->has('logging.channels.deprecations')
            && $this->app->isLocal()
            && $this->app->hasDebugModeEnabled()
        ) {
            $config->set([
                'logging.channels.deprecations' => [
                    'driver' => 'single',
                    'path' => storage_path('logs/php-deprecation-warnings.log'),
                ],
            ]);
        }

        $this->app->extend('db.schema', function (Builder $schema) {
            $schema->blueprintResolver(function ($table, $callback, $prefix) {
                return new Blueprint($table, $callback, $prefix);
            });

            return $schema;
        });

        if ($this->app->isLocal()) {
            DB::listen(function (QueryExecuted $queryExecuted) {
                if ($queryExecuted->time < 500) {
                    return;
                }

                Log::warning(sprintf('DB query exceeded %s ms. SQL: %s', $queryExecuted->time, $queryExecuted->sql));
            });
        }

        $events->listen(PanelSectionsRendering::class, PushDashboardMenuToSystemPanel::class);
    }

    protected function registerDashboardMenus(): void
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-core-system',
                    'priority' => 10000,
                    'name' => 'core/base::layouts.platform_admin',
                    'icon' => 'ti ti-user-shield',
                    'route' => 'system.index',
                    'permissions' => ['core.system'],
                ]);
        });
    }

    protected function registerPanelSections(): void
    {
        PanelSectionManager::group('system')
            ->setGroupName(trans('core/base::layouts.platform_admin'))
            ->register(SystemPanelSection::class);
    }

    protected function configureIni(): void
    {
        $currentLimit = @ini_get('memory_limit');
        $currentLimitInt = Helper::convertHrToBytes($currentLimit);

        $memoryLimit = $this->app['config']->get('core.base.general.memory_limit');

        if (! $memoryLimit) {
            if (false === Helper::isIniValueChangeable('memory_limit')) {
                $memoryLimit = $currentLimit;
            } else {
                $memoryLimit = '128M';
            }
        }

        $limitInt = Helper::convertHrToBytes($memoryLimit);
        if (-1 !== $currentLimitInt && (-1 === $limitInt || $limitInt > $currentLimitInt)) {
            BaseHelper::iniSet('memory_limit', $memoryLimit);
        }
    }

    public function provides(): array
    {
        return [BreadcrumbsManager::class];
    }
}
