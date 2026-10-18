<?php
namespace SparroWave\EnquiryProducts\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Slug\Models\Slug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use SparroWave\EnquiryProducts\Support\Catalog;
use SparroWave\EnquiryProducts\Support\CategoryHelper;

class EnquiryProductsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) { return; }
        $this->app->bind(\Botble\Ecommerce\Supports\ProductCategoryHelper::class, CategoryHelper::class);
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) { return; }
        $this->setNamespace('plugins/enquiry-products')->loadAndPublishConfigurations(['permissions', 'email'])
            ->loadAndPublishTranslations()->loadAndPublishViews()->loadMigrations()->loadRoutes(['web'])->publishAssets();
        $this->app['router']->pushMiddlewareToGroup('web', \SparroWave\EnquiryProducts\Http\Middleware\ProtectCheckout::class);
        $this->app['router']->pushMiddlewareToGroup('api', \SparroWave\EnquiryProducts\Http\Middleware\ProtectCheckout::class);
        Product::addGlobalScope(Catalog::SCOPE, function (Builder $query): void {
            $catalog = app(Catalog::class);
            if ($catalog->isStorefront()) { $catalog->constrain($query, false); }
        });
        ProductCategory::addGlobalScope(Catalog::SCOPE, function (Builder $query): void {
            if (app(Catalog::class)->isStorefront()) {
                $query->where(fn (Builder $q) => $q->whereNull('ec_product_categories.is_enquiry')->orWhere('ec_product_categories.is_enquiry', 0));
            }
        });

        $this->app->booted(function (): void {
            EmailHandler::addTemplateSettings('enquiry-products', config('plugins.enquiry-products.email', []));
            FormAbstract::extend(function (FormAbstract $form): void {
                $model = $form->getModel();
                if (! $model instanceof Product && ! $model instanceof ProductCategory) { return; }
                $form->add('is_enquiry', OnOffField::class, OnOffFieldOption::make()
                    ->label(__('plugins/enquiry-products::messages.enquiry_only'))->defaultValue((bool) $model->getAttribute('is_enquiry'))
                    ->helperText(__('plugins/enquiry-products::messages.hide_from_normal_shopping_and_show_in_equipment_enquiry_products_'))->toArray());
            }, 145);
            foreach ([BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT] as $action) {
                add_action($action, function ($type, $request, $model): void {
                    if (($model instanceof Product || $model instanceof ProductCategory) && $request->has('is_enquiry')) {
                        $model->setAttribute('is_enquiry', $request->boolean('is_enquiry'));
                        $model->save();
                        \Botble\Support\Services\Cache\Cache::make(Product::class)->flush();
                        \Botble\Support\Services\Cache\Cache::make(ProductCategory::class)->flush();
                    }
                }, 145, 3);
            }
            add_action('ecommerce_before_add_to_cart', function (Product $product): void {
                if (app(Catalog::class)->isEnquiry($product)) {
                    throw ValidationException::withMessages(['id' => __('plugins/enquiry-products::messages.this_equipment_is_enquiry_only_please_send_an_enquiry_instead')]);
                }
            }, 1);
            add_filter(BASE_FILTER_PUBLIC_SINGLE_DATA, function ($slug) {
                if (! $slug instanceof Slug) { return $slug; }
                if ($slug->reference_type === Product::class) {
                    $product = Product::withoutGlobalScope(Catalog::SCOPE)->wherePublished()->find($slug->reference_id);
                    if ($product && app(Catalog::class)->isEnquiry($product)) {
                        throw new HttpResponseException(redirect()->route('public.enquiry-products.show', $product->is_variation ? $product->original_product->slugable->key : $product->slugable->key, 301));
                    }
                }
                if ($slug->reference_type === ProductCategory::class) {
                    $category = ProductCategory::withoutGlobalScope(Catalog::SCOPE)->wherePublished()->find($slug->reference_id);
                    if ($category?->is_enquiry) {
                        throw new HttpResponseException(redirect()->route('public.enquiry-products.index', ['category' => $category->id]));
                    }
                }
                return $slug;
            }, 5);
            add_filter(THEME_FRONT_HEADER, fn (?string $html) => ($html ?? '') . '<link rel="stylesheet" href="' . e(asset('vendor/core/plugins/enquiry-products/enquiry-products.css') . '?v=' . (is_file(public_path('vendor/core/plugins/enquiry-products/enquiry-products.css')) ? filemtime(public_path('vendor/core/plugins/enquiry-products/enquiry-products.css')) : '1')) . '">', 145);
            if (function_exists('add_shortcode')) {
                add_shortcode('enquiry-products', __('plugins/enquiry-products::messages.enquiry_products'), __('plugins/enquiry-products::messages.searchable_equipment_enquiry_catalog'), function ($shortcode): string {
                    $catalog = app(Catalog::class);
                    $products = $catalog->listing(null, (int) $shortcode->category_id ?: null)->limit(max(1, min(48, (int) ($shortcode->limit ?: 12))))->get();
                    $title = $shortcode->title ?: __('plugins/enquiry-products::messages.equipment_enquiry_products');
                    return view('plugins/enquiry-products::shortcodes.products', compact('products', 'title'))->render();
                });
                shortcode()->setAdminConfig('enquiry-products', fn (array $attributes) => view('plugins/enquiry-products::shortcodes.config', ['attributes' => $attributes, 'categories' => app(Catalog::class)->categories()])->render());
                add_shortcode('enquiry-categories', __('plugins/enquiry-products::messages.enquiry_categories'), __('plugins/enquiry-products::messages.equipment_category_links'), fn () => view('plugins/enquiry-products::shortcodes.categories', ['categories' => app(Catalog::class)->categories()])->render());
            }
        });
        add_filter(BASE_FILTER_APPEND_MENU_NAME, function (?string $html, string $id): ?string {
            if (! in_array($id, ['sparrowave-product-enquiry', 'sparrowave-enquiry-products'], true)
                || ! auth()->user()?->hasPermission('enquiry-products.index')) {
                return $html;
            }
            $key = 'ep_pending_admin_count';
            if (! request()->attributes->has($key)) {
                request()->attributes->set($key, \SparroWave\EnquiryProducts\Models\Enquiry::where('status', 'pending')->count());
            }
            $count = request()->attributes->get($key);
            return ($html ?? '') . ($count ? '<span class="badge bg-danger text-white ms-2" title="' . e(__('plugins/enquiry-products::messages.pending_enquiries')) . '">' . (int) $count . '</span>' : '');
        }, 120, 2);
        add_filter('marketplace_vendor_sidebar_menu_items', function (?string $html): string {
            $customer = auth('customer')->user();
            if (! is_plugin_active('marketplace') || ! $customer || ! $customer->is_vendor || ! $customer->store?->id) {
                return $html ?? '';
            }
            $count = \SparroWave\EnquiryProducts\Models\Enquiry::where('store_id', $customer->store->id)->where('status', 'pending')->count();
            return ($html ?? '') . view('plugins/enquiry-products::vendor.pending-badge', compact('count'))->render();
        }, 120);
        add_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, function ($screen, $store): void {
            if (! is_plugin_active('marketplace') || ! $store instanceof \Botble\Marketplace\Models\Store) { return; }
            if (! app(Catalog::class)->products()->where('store_id', $store->id)->exists()) { return; }
            $url = \SparroWave\EnquiryProducts\Support\StoreUrl::catalogue($store);
            if (! $url) { return; }
            add_filter(THEME_FRONT_FOOTER, fn (?string $html) => ($html ?? '') . view('plugins/enquiry-products::partials.store-catalog-button', ['url' => $url])->render(), 150);
        }, 120, 2);
        $this->app['events']->listen(\Botble\Theme\Events\RenderingSiteMapEvent::class, [\SparroWave\EnquiryProducts\Listeners\Sitemap::class, 'handle']);
        $this->app['events']->listen(\Botble\Theme\Events\ThemeRoutingBeforeEvent::class, function (): void {
            $pages = max(1, (int) ceil(app(Catalog::class)->products()->whereHas('slugable')->count() / \Botble\Theme\Facades\SiteMapManager::getItemsPerPage()));
            for ($page = 1; $page <= $pages; $page++) \Botble\Theme\Facades\SiteMapManager::registerKey('enquiry-products-' . $page);
        });
        DashboardMenu::default()->beforeRetrieving(function (): void {
            if (is_plugin_active('marketplace')) {
                DashboardMenu::make()->registerItem(DashboardMenuItem::make()->id('sparrowave-enquiry-sellers')
                    ->parentId('sparrowave-product-enquiry')->name(__('plugins/enquiry-products::messages.enquiry_sellers'))
                    ->icon('ti ti-building-store')->route('enquiry-products.sellers')->permissions(['enquiry-products.index'])->priority(3));
            }
            DashboardMenu::make()
                ->registerItem(DashboardMenuItem::make()->id('sparrowave-product-enquiry')
                    ->name(__('plugins/enquiry-products::messages.product_enquiry'))->icon('ti ti-message-question')->priority(126))
                ->registerItem(DashboardMenuItem::make()->id('sparrowave-enquiry-products')
                    ->parentId('sparrowave-product-enquiry')->name(__('plugins/enquiry-products::messages.enquiries'))->icon('ti ti-list')
                    ->route('enquiry-products.index')->permissions(['enquiry-products.index'])->priority(1))
                ->registerItem(DashboardMenuItem::make()->id('sparrowave-enquiry-settings')
                    ->parentId('sparrowave-product-enquiry')->name(__('plugins/enquiry-products::messages.enquiry_page_settings'))->icon('ti ti-settings')
                    ->route('enquiry-products.settings')->permissions(['enquiry-products.settings'])->priority(2));
        });
        if (is_plugin_active('marketplace')) {
            DashboardMenu::for('vendor')->beforeRetrieving(function (): void {
                DashboardMenu::make()->registerItem(DashboardMenuItem::make()->id('sparrowave-vendor-enquiries')
                    ->name(__('plugins/enquiry-products::messages.equipment_enquiries'))->icon('ti ti-message-question')->route('enquiry-products.vendor.index')->priority(126));
            });
        }
    }
}
