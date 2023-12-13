<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\FormAbstract;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Forms\StoreForm;
use Botble\Marketplace\Models\Store;
use Botble\Media\Facades\RvMedia;
use Botble\Menu\Facades\Menu;
use Botble\SocialLogin\Facades\SocialService;
use Botble\Theme\Facades\Theme;
use Botble\Widget\Events\RenderingWidgetSettings;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Route;

register_page_template([
    'default' => __('Default'),
    'homepage' => __('Homepage'),
    'full-width' => __('Full Width'),
    'coming-soon' => __('Coming Soon'),
]);

RvMedia::setUploadPathAndURLToPublic()
    ->addSize('small', 300, 300);

Menu::addMenuLocation('header-navigation', __('Header Navigation'));

function available_socials_store(): array
{
    return [
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'instagram' => 'Instagram',
        'youtube' => 'Youtube',
        'linkedin' => 'Linkedin',
    ];
}

app()->booted(function () {
    app('events')->listen(RenderingWidgetSettings::class, function () {
        register_sidebar([
            'id' => 'pre_footer_sidebar',
            'name' => __('Top footer sidebar'),
            'description' => __('Widgets in the blog page'),
        ]);

        register_sidebar([
            'id' => 'footer_sidebar',
            'name' => __('Footer sidebar'),
            'description' => __('Widgets in footer sidebar'),
        ]);

        register_sidebar([
            'id' => 'bottom_footer_sidebar',
            'name' => __('Bottom footer sidebar'),
            'description' => __('Widgets in bottom footer sidebar'),
        ]);

        if (is_plugin_active('ecommerce')) {
            register_sidebar([
                'id' => 'products_list_sidebar',
                'name' => __('Products list sidebar'),
                'description' => __('Widgets on header products list page'),
            ]);

            register_sidebar([
                'id' => 'product_detail_sidebar',
                'name' => __('Product detail sidebar'),
                'description' => __('Widgets in the product detail page'),
            ]);
        }
    });

    app('events')->listen(RouteMatched::class, function () {
        add_filter('marketplace_vendor_settings_register_content_tabs', function ($html, $object) {
            if ($object instanceof Store) {
                return $html . view(
                    Theme::getThemeNamespace() . '::views.marketplace.includes.extended-info-tab'
                )->render();
            }

            return $html;
        }, 24, 2);

        add_filter('marketplace_vendor_settings_register_content_tab_inside', function ($html, $object) {
            if ($object instanceof Store) {
                $background = $object->getMetaData('background', true);
                $socials = [];
                $availableSocials = [];
                if (! MarketplaceHelper::hideStoreSocialLinks()) {
                    $socials = $object->getMetaData('socials', true);
                    $availableSocials = available_socials_store();
                }
                $view = Theme::getThemeNamespace() . '::views.marketplace.includes.extended-info-content';

                return $html . view($view, compact('background', 'socials', 'availableSocials'))->render();
            }

            return $html;
        }, 24, 2);

        EmailHandler::addTemplateSettings(Theme::getThemeName(), [
            'name' => __('Theme emails'),
            'description' => __('Config email templates for theme'),
            'templates' => [
                'contact-seller' => [
                    'title' => __('Contact Seller'),
                    'description' => __('Email will be sent to the seller when someone contact from store profile page'),
                    'subject' => __('Message sent via your market profile on {{ site_title }}'),
                    'can_off' => true,
                ],
            ],
            'variables' => [
                'contact_message' => __('Contact seller message'),
                'customer_name' => __('Customer Name'),
                'customer_email' => __('Customer Email'),
                'store_name' => 'plugins/marketplace::marketplace.store_name',
                'store_phone' => 'plugins/marketplace::marketplace.store_phone',
                'store_address' => 'plugins/marketplace::marketplace.store_address',
                'store_url' => 'plugins/marketplace::marketplace.store_url',
                'store' => 'Store',
            ],
        ], 'themes');
    });

    FormAbstract::beforeRendering(function (FormAbstract $form) {
        $request = $form->getRequest();

        $model = $form->getModel();

        if ($model instanceof Store) {
            if (request()->segment(1) === BaseHelper::getAdminPrefix()) {
                if ($model->getMetaData('background', true) != $request->input('background')) {
                    MetaBox::saveMetaBoxData($model, 'background', $request->input('background'));
                }
            } elseif (Route::currentRouteName() == 'marketplace.vendor.settings.post') {
                if ($request->hasFile('background_input')) {
                    $result = RvMedia::handleUpload($request->file('background_input'), 0, 'stores');
                    if (! $result['error']) {
                        $file = $result['data'];
                        MetaBox::saveMetaBoxData($model, 'background', $file->url);
                    }
                }
            }

            if (! MarketplaceHelper::hideStoreSocialLinks() && $request->has('socials')) {
                $availableSocials = available_socials_store();
                $socials = collect((array)$request->input('socials', []))->filter(
                    function ($value, $key) use ($availableSocials) {
                        return filter_var($value, FILTER_VALIDATE_URL) && in_array($key, array_keys($availableSocials));
                    }
                );

                MetaBox::saveMetaBoxData($model, 'socials', $socials);
            }
        }
    }, 230);

    if (is_plugin_active('marketplace')) {
        StoreForm::extend(function (StoreForm $form) {
            $form
                ->when(AdminHelper::isInAdmin(true), function (FormAbstract $form) {
                    $form->addAfter('logo', 'background', MediaImageField::class, [
                        'label' => __('Background'),
                        'metadata' => true,
                    ]);
                });
        });
    }

    if (is_plugin_active('social-login')) {
        $data = SocialService::getModule('customer');
        if ($data) {
            $data['view'] = Theme::getThemeNamespace('partials.social-login-options');
            $data['use_css'] = false;
            SocialService::registerModule($data);
        }
    }
});

if (! function_exists('theme_get_autoplay_speed_options')) {
    function theme_get_autoplay_speed_options(): array
    {
        $options = [2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000];

        return array_combine($options, $options);
    }
}

if (! function_exists('get_store_list_layouts')) {
    function get_store_list_layouts(): array
    {
        return [
            'grid' => __('Grid'),
            'list' => __('List'),
        ];
    }
}
