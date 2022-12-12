<?php

namespace Botble\Sms\Providers;

use Botble\Sms\Models\Sms;
use Illuminate\Support\ServiceProvider;
use Botble\Sms\Repositories\Caches\SmsCacheDecorator;
use Botble\Sms\Repositories\Eloquent\SmsRepository;
use Botble\Sms\Repositories\Interfaces\SmsInterface;
use Illuminate\Support\Facades\Event;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;

class SmsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(SmsInterface::class, function () {
            return new SmsCacheDecorator(new SmsRepository(new Sms));
        });

        $this->setNamespace('plugins/sms')->loadHelpers();
    }

    public function boot()
    {
        $this
            ->loadAndPublishConfigurations(['permissions'])
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
                'name'        => 'plugins/sms::sms.name',
                'icon'        => 'fa fa-envelope',
                'url'         => route('sms.index'),
                'permissions' => ['sms.index'],
            ]);
        });
        add_filter(BASE_FILTER_AFTER_SETTING_CONTENT, [$this, 'addSettings'], 249);
    }
     /**
     * @param null $data
     * @return string
     * @throws \Throwable
     */
    public function addSettings($data = null)
    {
        $sms_url = setting('sms_url');
        return $data . view('plugins/sms::settings', compact('sms_url'))
                ->render();
    }
}
