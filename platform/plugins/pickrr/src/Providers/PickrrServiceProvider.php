<?php

namespace Botble\Pickrr\Providers;

use Botble\Pickrr\Models\Pickrr;
use Illuminate\Support\ServiceProvider;
use Botble\Pickrr\Repositories\Caches\PickrrCacheDecorator;
use Botble\Pickrr\Repositories\Eloquent\PickrrRepository;
use Botble\Pickrr\Repositories\Interfaces\PickrrInterface;
use Illuminate\Support\Facades\Event;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Pickrr\Pickrr  as PickrrShippment;

class PickrrServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(PickrrInterface::class, function () {
            return new PickrrCacheDecorator(new PickrrRepository(new Pickrr));
        });

        $this->setNamespace('plugins/pickrr')->loadHelpers();
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
                \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Pickrr::class, [
                    'name',
                ]);
            } else {
                // Use language v1
                $this->app->booted(function () {
                    \Language::registerModule([Pickrr::class]);
                });
            }
        }

        // Event::listen(RouteMatched::class, function () {
        //     dashboard_menu()->registerItem([
        //         'id'          => 'cms-plugins-pickrr',
        //         'priority'    => 5,
        //         'parent_id'   => null,
        //         'name'        => 'plugins/pickrr::pickrr.name',
        //         'icon'        => 'fa fa-list',
        //         'url'         => route('pickrr.index'),
        //         'permissions' => ['pickrr.index'],
        //     ]);
        // });
      //  add_filter('handle_shipping_fee', [$this, 'handleShippingFee'], 11, 3);

        if ( defined('SHIPPING_METHODS_SETTINGS_PAGE')) {
            add_filter(SHIPPING_METHODS_SETTINGS_PAGE, [$this, 'addSettings'], 2);
        }

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == ShippingMethodEnum::class) {
                $values['PICKRR'] = PICKRR_MODULE_SCREEN_NAME;
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == ShippingMethodEnum::class && $value == PICKRR_MODULE_SCREEN_NAME) {
                $value = 'Pickrr';
            }

            return $value;
        }, 2, 2);

       
    }

    /**
     * @param string|null $settings
     * @return string
     *
     * @throws Throwable
     */
    public function addSettings(?string $settings): string
    {
        return $settings . view('plugins/pickrr::settings')->render();
    }
}
