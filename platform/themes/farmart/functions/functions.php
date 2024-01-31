<?php

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Ecommerce\Facades\FlashSale;
use Botble\Ecommerce\Supports\FlashSaleSupport;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Forms\StoreForm;
use Botble\Marketplace\Forms\VendorStoreForm;
use Botble\Media\Facades\RvMedia;
use Botble\Menu\Facades\Menu;
use Botble\SocialLogin\Facades\SocialService;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;
use Theme\Farmart\Supports\Wishlist;

register_page_template([
    'default' => __('Default'),
    'default-sidebar' => __('Default with Sidebar'),
    'homepage' => __('Homepage'),
    'full-width' => __('Full Width'),
    'coming-soon' => __('Coming Soon'),
    'blog-right-sidebar' => __('Blog with Sidebar'),
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
    'id' => 'default_page_sidebar',
    'name' => __('Default Page sidebar'),
    'description' => __('Widgets in Default Page sidebar'),
]);

register_sidebar([
    'id' => 'blog-right-sidebar',
    'name' => __('Blog with Sidebar'),
    'description' => __('Blogs with sidebar'),
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

    add_filter('ecommerce_quick_view_data', function (array $data): array {
        return [
            ...$data,
            'wishlistIds' => Wishlist::getWishlistIds([$data['product']->getKey()]),
        ];
    });
}

app()->booted(function () {
    if (method_exists(FlashSaleSupport::class, 'addShowSaleCountLeftSetting')) {
        FlashSale::addShowSaleCountLeftSetting();
    }

    if (is_plugin_active('marketplace')) {
        StoreForm::extend(function (StoreForm $form) {
            $form->addAfter('logo', 'background', MediaImageField::class, [
                'label' => __('Background'),
                'metadata' => true,
            ]);
        });

        VendorStoreForm::extend(function (VendorStoreForm $form) {
            $store = $form->getModel();

            $background = $store->getMetaData('background', true);
            $socials = [];
            $availableSocials = [];

            if (! MarketplaceHelper::hideStoreSocialLinks()) {
                $socials = $store->getMetaData('socials', true);
                $availableSocials = available_socials_store();
            }

            $view = Theme::getThemeNamespace() . '::views.marketplace.includes.extended-info-content';

            $form
                ->addBefore('submit', 'extended_info_content', HtmlField::class, [
                    'html' => view($view, compact('background', 'socials', 'availableSocials'))->render(),
                ]);
        });

        VendorStoreForm::afterSaving(function (VendorStoreForm $form) {
            $request = $form->getRequest();

            $store = $form->getModel();

            if ($request->hasFile('background_input')) {
                $result = RvMedia::handleUpload($request->file('background_input'), 0, 'stores');
                if (! $result['error']) {
                    MetaBox::saveMetaBoxData($store, 'background', $result['data']->url);
                }
            } elseif ($request->input('background')) {
                MetaBox::saveMetaBoxData($store, 'background', $request->input('background'));
            } elseif ($request->has('background')) {
                MetaBox::deleteMetaData($store, 'background');
            }

            if (! MarketplaceHelper::hideStoreSocialLinks() && $request->has('socials')) {
                $availableSocials = available_socials_store();
                $socials = collect((array)$request->input('socials', []))->filter(
                    function ($value, $key) use ($availableSocials) {
                        return filter_var($value, FILTER_VALIDATE_URL) && in_array($key, array_keys($availableSocials));
                    }
                );

                MetaBox::saveMetaBoxData($store, 'socials', $socials);
            }
        }, 230);
    }

    app('events')->listen(RouteMatched::class, function () {
        EmailHandler::addTemplateSettings(Theme::getThemeName(), [
            'name' => __('Theme emails'),
            'description' => __('Config email templates for theme'),
            'templates' => [
                'contact-seller' => [
                    'title' => __('Contact Seller'),
                    'description' => __(
                        'Email will be sent to the seller when someone contact from store profile page'
                    ),
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
