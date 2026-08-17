<?php

namespace Botble\Sms\Providers;

use Botble\Sms\Models\Sms;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\AliasLoader;
use Botble\Sms\Facades\SmsHelperFacade;
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
            ]);
        });

        $this->app->booted(function () {
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
        });
      //add_filter(BASE_FILTER_AFTER_SETTING_CONTENT, [$this, 'addSettings'], 249);
    }

    private function isRegistrationOtpEnabled(): bool
    {
        return (bool) setting('sms_registration_otp_enabled', setting('sms_otp_enabled'));
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
