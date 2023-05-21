<?php

namespace Botble\Sms\Providers;

use Botble\Sms\Models\Sms;
use Botble\Sms\Events\SendSmsEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\AliasLoader;
use Botble\Sms\Facades\SmsHelperFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Sms\Repositories\Eloquent\SmsRepository;
use Botble\Sms\Repositories\Interfaces\SmsInterface;
use Botble\Sms\Repositories\Caches\SmsCacheDecorator;

class SmsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;
    protected $listen = [
        SendSmsEvent::class => [
            SendSmsListener::class,
        ],
    ];
    public function register()
    {
        $this->app->bind(SmsInterface::class, function () {
            return new SmsCacheDecorator(new SmsRepository(new Sms));
        });
        $loader = AliasLoader::getInstance();
        $loader->alias('SmsHelper', SmsHelperFacade::class);
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
            ]);
        });
      //  add_filter(BASE_FILTER_AFTER_SETTING_CONTENT, [$this, 'addSettings'], 249);
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
