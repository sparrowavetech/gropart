<?php

namespace Botble\Sms\Providers;

use Botble\Sms\Models\Sms;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\AliasLoader;
use Botble\Sms\Facades\SmsHelperFacade;
use Botble\Sms\Observers\MarketplaceSmsObserver;
use Illuminate\Support\ServiceProvider;
use Botble\Sms\Providers\EventServiceProvider;
use Illuminate\Routing\Events\RouteMatched;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Sms\Repositories\Eloquent\SmsRepository;
use Botble\Sms\Repositories\Interfaces\SmsInterface;
use Botble\Sms\Repositories\Caches\SmsCacheDecorator;

class SmsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(SmsInterface::class, function () {
            return new SmsCacheDecorator(new SmsRepository(new Sms));
        });
        $loader = AliasLoader::getInstance();
        $loader->alias('SmsHelper', SmsHelperFacade::class);
        $this->app->register(EventServiceProvider::class);
        $this->setNamespace('plugins/sms')->loadHelpers();
    }

    public function boot()
    {
        if (! config()->has('logging.channels.sms')) {
            config()->set('logging.channels.sms', [
                'driver' => 'daily',
                'path' => storage_path('logs/sms.log'),
                'days' => 30,
                'level' => 'info',
            ]);
        }

        $this
            ->loadAndPublishConfigurations(['permissions','sms','general'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web']);

        if (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
            if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
                // Use language v2
                \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Sms::class, [
                    'name',
                ]);
            } else {
                // Use language v1
                $this->app->booted(function () {
                    \Language::registerModule([Sms::class]);
                });
            }
        }

        Event::listen(RouteMatched::class, function () {
            dashboard_menu()->registerItem([
                'id'          => 'cms-plugins-sms',
                'priority'    => 5,
                'parent_id'   => null,
                'name'        => 'plugins/sms::sms.title',
                'icon'        => 'fa fa-envelope',
                'url'         => route('sms.index'),
                'permissions' => ['sms.index'],
            ])->registerItem([
                'id'          => 'cms-plugins-sms-template',
                'priority'    => 1,
                'parent_id'   => 'cms-plugins-sms',
                'name'        => 'plugins/sms::sms.name',
                'icon'        => 'fa fa-envelope',
                'url'         => route('sms.index'),
                'permissions' => ['sms.index'],
            ])->registerItem([
                'id'          => 'cms-plugins-sms-setting',
                'priority'    => 2,
                'parent_id'   => 'cms-plugins-sms',
                'name'        => 'plugins/sms::sms.setting',
                'icon'        => 'fa fa-cog',
                'url'         => route('sms.settings'),
                'permissions' => ['sms.settings'],
            ])->registerItem([
                'id'          => 'cms-plugins-sms-delivery-report',
                'priority'    => 3,
                'parent_id'   => 'cms-plugins-sms',
                'name'        => 'plugins/sms::sms.delivery_report.title',
                'icon'        => 'fa fa-list',
                'url'         => route('sms.delivery-reports.index'),
                'permissions' => ['sms.delivery-reports.index'],
            ])->registerItem([
                'id'          => 'cms-plugins-sms-logs',
                'priority'    => 4,
                'parent_id'   => 'cms-plugins-sms',
                'name'        => 'plugins/sms::sms.logs.title',
                'icon'        => 'ti ti-file-text',
                'url'         => route('sms.logs.index'),
                'permissions' => ['sms.logs.index'],
            ]);
        });

        $this->app->booted(function () {
            $this->bindCustomerAuthRoutes();
            $this->registerMarketplaceObservers();
            $this->registerCustomerTableHooks();
            view()->composer('plugins/ecommerce::customers.view', function ($view): void {
                $view->setPath(plugin_path('sms/resources/views/overrides/customer-view.blade.php'));
            });

            // Apply customer registration phone validation rule
            add_filter('ecommerce_customer_registration_form_validation_rules', function (array $rules) {
                if (is_plugin_active('sms') && $this->isRegistrationOtpEnabled()) {
                    $rules['phone'] = array_merge($rules['phone'] ?? [], ['required', 'string', 'max:20']);
                    $rules['phone'] = array_filter($rules['phone'], fn($rule) => $rule !== 'nullable');
                }
                return $rules;
            }, 120);

            // Apply checkout form validation rule
            add_filter('checkout_rules_request', function (array $rules) {
                if (is_plugin_active('sms') && $this->isRegistrationOtpEnabled()) {
                    $rules['address.phone'] = 'required|string|max:20';
                }
                return $rules;
            }, 120);

            add_filter(BASE_FILTER_AFTER_LOGIN_OR_REGISTER_FORM, function (?string $html, string $model): ?string {
                if (
                    ! is_plugin_active('sms') ||
                    ! setting('sms_login_otp_enabled') ||
                    $model !== \Botble\Ecommerce\Models\Customer::class ||
                    ! request()->routeIs('customer.login')
                ) {
                    return $html;
                }

                return ($html ?: '') . view('plugins/sms::themes.customers.partials.login-otp-link')->render();
            }, 20, 2);

            add_filter(THEME_FRONT_FOOTER, function (?string $html): string {
                if (! is_plugin_active('sms') || ! request()->routeIs('customer.register', 'customer.login.otp', 'customer.otp', 'customer.login.otp.verify')) {
                    return $html ?? '';
                }

                $html = ($html ?? '') . view('plugins/sms::themes.customers.partials.submit-loader', [
                    'registrationOtpEnabled' => $this->isRegistrationOtpEnabled(),
                ])->render();

                if (request()->routeIs('customer.register')) {
                    $html .= view('plugins/sms::themes.customers.partials.register-phone-check')->render();
                }

                return $html;
            }, 180);
        });
      //add_filter(BASE_FILTER_AFTER_SETTING_CONTENT, [$this, 'addSettings'], 249);
    }

    private function isRegistrationOtpEnabled(): bool
    {
        return (bool) setting('sms_registration_otp_enabled', setting('sms_otp_enabled'));
    }

    private function bindCustomerAuthRoutes(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        foreach ([
            'customer.register.post' => [\Botble\Sms\Http\Controllers\Fronts\SmsOtpRegisterController::class, 'register', 'POST'],
            'customer.confirm' => [\Botble\Sms\Http\Controllers\Fronts\SmsOtpRegisterController::class, 'confirm', 'GET'],
            'customer.login.post' => [\Botble\Sms\Http\Controllers\Fronts\SmsLoginController::class, 'login', 'POST'],
        ] as $name => [$class, $method, $verb]) {
            $route = app('router')->getRoutes()->getByName($name);

            if (! $route || ! in_array($verb, $route->methods(), true)) {
                continue;
            }

            // Ecommerce's later route registration wins; replace only these active auth actions.
            $controller = $class . '@' . $method;
            $route->setAction(array_replace($route->getAction(), [
                'uses' => $controller,
                'controller' => $controller,
            ]));

            if ($name === 'customer.register.post' && ! in_array('throttle:5,1', $route->middleware(), true)) {
                $route->middleware('throttle:5,1');
            }

            $route->flushController();
        }
    }

    private function registerCustomerTableHooks(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $hasOtpColumn = \Illuminate\Support\Facades\Schema::hasColumn(
            (new \Botble\Ecommerce\Models\Customer())->getTable(),
            'sms_otp_verified_at'
        );
        $hasRequiredColumn = \Illuminate\Support\Facades\Schema::hasColumn(
            (new \Botble\Ecommerce\Models\Customer())->getTable(),
            'sms_otp_required_at'
        );

        add_filter(BASE_FILTER_TABLE_HEADINGS, function (array $columns, $model, $table) use ($hasRequiredColumn) {
            if (! $table instanceof \Botble\Ecommerce\Tables\CustomerTable) {
                return $columns;
            }

            $names = array_map(fn ($column) => $column->name ?? null, $columns);
            $emailIndex = array_search('email', $names, true);
            $phoneIndex = array_search('phone', $names, true);

            if ($emailIndex === false) {
                array_splice($columns, $phoneIndex === false ? count($columns) : $phoneIndex, 0, [
                    \Botble\Table\Columns\EmailColumn::make(),
                ]);
                $names = array_map(fn ($column) => $column->name ?? null, $columns);
                $emailIndex = array_search('email', $names, true);
                $phoneIndex = array_search('phone', $names, true);
            }

            if ($phoneIndex === false) {
                array_splice($columns, $emailIndex + 1, 0, [
                    \Botble\Table\Columns\PhoneColumn::make()->title(trans('plugins/sms::sms.mobile_number')),
                ]);
                $phoneIndex = $emailIndex + 1;
            }

            array_splice($columns, $phoneIndex + 1, 0, [
                \Botble\Table\Columns\FormattedColumn::make('sms_otp_verified_at')
                    ->title(trans('plugins/sms::sms.otp_verified'))
                    ->orderable(false)
                    ->searchable(false)
                    ->renderUsing(function (\Botble\Table\Columns\FormattedColumn $column) {
                        $verified = ! empty($column->getItem()->sms_otp_verified_at);
                        $pending = ! $verified && ! empty($column->getItem()->sms_otp_required_at);
                        $class = $verified ? 'bg-success text-success-fg' : ($pending ? 'bg-warning text-warning-fg' : 'bg-secondary text-secondary-fg');
                        $label = $verified ? 'otp_verified_yes' : ($pending ? 'otp_pending' : 'otp_not_recorded');

                        return '<span class="badge ' . $class . '">' . e(trans('plugins/sms::sms.' . $label)) . '</span>';
                    }),
            ]);

            return $columns;
        }, 180, 3);

        add_filter('base_filter_table_filters', function (array $filters, $table) use ($hasOtpColumn, $hasRequiredColumn): array {
            if ($table instanceof \Botble\Ecommerce\Tables\CustomerTable) {
                $filters['phone'] = [
                    'title' => trans('plugins/sms::sms.mobile_number'),
                    'type' => 'text',
                    'validate' => 'required|string|max:30',
                ];

                if ($hasOtpColumn && $hasRequiredColumn) {
                    $filters['sms_otp_status'] = [
                        'title' => trans('plugins/sms::sms.mobile_verified_filter'),
                        'type' => 'select',
                        'choices' => [
                            'verified' => trans('plugins/sms::sms.otp_verified_yes'),
                            'pending' => trans('plugins/sms::sms.otp_pending'),
                            'not_recorded' => trans('plugins/sms::sms.otp_not_recorded'),
                        ],
                        'validate' => 'required|in:verified,pending,not_recorded',
                    ];

                    // TableAbstract has one filter callback; retain callbacks installed by other plugins.
                    static $wrappers;
                    $wrappers ??= new \WeakMap();
                    $property = new \ReflectionProperty($table, 'onFilterQueryCallback');
                    $current = $property->isInitialized($table) ? $property->getValue($table) : null;
                    $previous = $current && $wrappers->offsetExists($current) ? $wrappers[$current] : $current;
                    $customerTable = (new \Botble\Ecommerce\Models\Customer())->getTable();
                    $callback = function ($query, $key, $operator, $value) use ($previous, $customerTable) {
                        if ($key === 'sms_otp_status') {
                            return match ($value) {
                                'verified' => $query->whereNotNull($customerTable . '.sms_otp_verified_at'),
                                'pending' => $query->whereNotNull($customerTable . '.sms_otp_required_at')
                                    ->whereNull($customerTable . '.sms_otp_verified_at'),
                                'not_recorded' => $query->whereNull($customerTable . '.sms_otp_required_at')
                                    ->whereNull($customerTable . '.sms_otp_verified_at'),
                                default => $query->whereRaw('1 = 0'),
                            };
                        }

                        return $previous ? $previous($query, $key, $operator, $value) : false;
                    };
                    $wrappers[$callback] = $previous;
                    $table->onFilterQuery($callback);
                }
            }

            return $filters;
        }, 180, 2);

        if (! $hasOtpColumn) {
            return;
        }

        add_filter(BASE_FILTER_TABLE_QUERY, function ($query, $table) use ($hasRequiredColumn) {
            if ($table instanceof \Botble\Ecommerce\Tables\CustomerTable) {
                $query->addSelect((new \Botble\Ecommerce\Models\Customer())->getTable() . '.sms_otp_verified_at');
                if ($hasRequiredColumn) {
                    $query->addSelect((new \Botble\Ecommerce\Models\Customer())->getTable() . '.sms_otp_required_at');
                }
            }

            return $query;
        }, 180, 2);

        \Botble\Ecommerce\Models\Customer::updating(function (\Botble\Ecommerce\Models\Customer $customer): void {
            if ($customer->isDirty('phone')) {
                $customer->sms_otp_verified_at = null;
            }
        });
    }

    private function registerMarketplaceObservers(): void
    {
        if (! is_plugin_active('marketplace')) {
            return;
        }

        foreach ([
            'Botble\Ecommerce\Models\Product',
            'Botble\Marketplace\Models\Withdrawal',
            'Botble\Marketplace\Models\Vendor',
        ] as $model) {
            if (class_exists($model)) {
                $model::observe(MarketplaceSmsObserver::class);
            }
        }
    }
     /**
     * @param null $data
     * @return string
     * @throws \Throwable
     */
    public function addSettings($data = null)
    {
        // $sms_url = setting('sms_url');
        // return $data . view('plugins/sms::settings', compact('sms_url'))
        //         ->render();
    }
}
