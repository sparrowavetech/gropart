<?php

namespace Botble\ProductBundles\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\CheckboxField;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\Fields\UiSelectorField;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Models\Product;
use Botble\ProductBundles\Models\Bundle;
use Botble\ProductBundles\Providers\EventServiceProvider;
use Botble\ProductBundles\Supports\ProductBundlesManager;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Arr;

class ProductBundlesServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->singleton('product-bundles', function () {
            return new ProductBundlesManager();
        });

        $this->app->register(EventServiceProvider::class);
    }

    public function boot(): void
    {
        // Require ecommerce plugin to be active (for Product model / cart).
        if (function_exists('is_plugin_active') && ! is_plugin_active('ecommerce')) {
            return;
        }

        // Ensure helpers are loaded even when the plugin isn't part of the root Composer autoload.
        // Some Botble installations don't automatically load plugin-level composer "files" autoload.
        foreach ([__DIR__ . '/../Helpers/constants.php', __DIR__ . '/../Helpers/helpers.php'] as $helperFile) {
            if (is_file($helperFile)) {
                require_once $helperFile;
            }
        }

        $this->setNamespace('plugins/product-bundles')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadMigrations()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->publishAssets();

        // Admin menu
        if (class_exists(DashboardMenu::class)) {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-product-bundles',
                'priority' => 35,
                'parent_id' => 'cms-plugins-ecommerce', // will fall back if parent not found
                'name' => trans('plugins/product-bundles::bundles.menu'),
                'icon' => 'ti ti-package',
                'url' => route('product-bundles.index'),
                'permissions' => ['product-bundles.index'],
            ]);
        }

        if (class_exists(MetaBox::class) && class_exists(Product::class)) {
            $metaBoxCallback = function ($product) {
                if (! $product instanceof Product) {
                    return '';
                }

                $bundle = null;
                if ($product->exists) {
                    $bundle = Bundle::query()
                        ->where('ecommerce_product_id', $product->getKey())
                        ->with(['items.product', 'groups.items.product', 'products'])
                        ->first();
                }

                return view('plugins/product-bundles::admin.product-metabox', [
                    'product' => $product,
                    'bundle' => $bundle,
                ])->render();
            };

            MetaBox::addMetaBox(
                'product-bundles-box',
                trans('plugins/product-bundles::bundles.metabox.title'),
                $metaBoxCallback,
                Product::class,
                'advanced',
                'default'
            );

            if (function_exists('add_action')) {
                add_action(BASE_ACTION_META_BOXES, function ($context, $object = null) use ($metaBoxCallback) {
                    if (! $object instanceof Product) {
                        return;
                    }

                    MetaBox::addMetaBox(
                        'product-bundles-box',
                        trans('plugins/product-bundles::bundles.metabox.title'),
                        $metaBoxCallback,
                        Product::class,
                        'advanced',
                        'default'
                    );
                }, 1, 2);
            }
        }

        if (defined('ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML')) {
            add_filter(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, function ($html, $product = null) {
                if (! $product instanceof Product) {
                    return $html;
                }

                $bundle = Bundle::query()
                    ->where('ecommerce_product_id', $product->getKey())
                    ->where('is_active', true)
                    ->with(['items.product', 'groups.items.product'])
                    ->first();

                if (! $bundle || ! $bundle->isInDateRange()) {
                    return $html;
                }

                return $html . view('plugins/product-bundles::front.product-combo', [
                    'bundle' => $bundle,
                ])->render();
            }, 120, 2);
        }

        // Shortcode support (optional): [product_bundles product_id="123"]
        // This allows embedding the bundles UI in any editor area that supports shortcodes.
        $shortcodeFacade = 'Botble\\Shortcode\\Facades\\Shortcode';
        if (class_exists($shortcodeFacade)) {
            try {
                $callback = function ($shortcode) {
                    $attrs = [];

                    try {
                        if (is_object($shortcode) && method_exists($shortcode, 'toArray')) {
                            $attrs = (array) $shortcode->toArray();
                        } elseif (is_object($shortcode) && property_exists($shortcode, 'attributes')) {
                            $attrs = (array) $shortcode->attributes;
                        } elseif (is_array($shortcode)) {
                            $attrs = $shortcode;
                        }
                    } catch (\Throwable $e) {
                        $attrs = [];
                    }

                    $productId = (int) ($attrs['product_id'] ?? ($attrs['id'] ?? 0));
                    // If product_id isn't provided, return empty string so the page builder
                    // doesn't show an error message (shortcodes are often inserted as blocks).
                    // The site owner can still pass product_id explicitly when needed.
                    if ($productId <= 0) {
                        return '';
                    }

                    return view('plugins/product-bundles::front.box', ['productId' => $productId])->render();
                };

                // Botble Shortcode register() signature may differ between versions, so we try safely.
                try {
                    $shortcodeFacade::register('product_bundles', trans('plugins/product-bundles::bundles.shortcode.name'), trans('plugins/product-bundles::bundles.shortcode.description'), $callback);
                } catch (\Throwable $e) {
                    try {
                        $shortcodeFacade::register('product_bundles', trans('plugins/product-bundles::bundles.shortcode.description'), $callback);
                    } catch (\Throwable $e2) {
                        try {
                            $shortcodeFacade::register('product_bundles', $callback);
                        } catch (\Throwable $e3) {
                            // ignore
                        }
                    }
                }

                try {
                    $shortcodeFacade::setAdminConfig('product_bundles', function ($attributes) {
                        return view('plugins/product-bundles::admin.shortcodes.product-bundles', compact('attributes'));
                    });
                } catch (\Throwable $e) {
                    // ignore
                }

                // Shortcode: [bundle_groups] - render bundle cards with images (treat bundles like products).
                try {
                    $groupsCb = function ($shortcode) {
                        $attrs = [];
                        try {
                            if (is_object($shortcode) && method_exists($shortcode, 'toArray')) {
                                $attrs = (array) $shortcode->toArray();
                            } elseif (is_object($shortcode) && property_exists($shortcode, 'attributes')) {
                                $attrs = (array) $shortcode->attributes;
                            } elseif (is_array($shortcode)) {
                                $attrs = $shortcode;
                            }
                        } catch (\Throwable $e) {
                            $attrs = [];
                        }

                        $limit = max(1, (int) ($attrs['limit'] ?? 8));
                        $layout = (string) ($attrs['layout'] ?? ($attrs['style'] ?? 'grid'));
                        $layout = in_array($layout, ['grid', 'tabs', 'columns'], true) ? $layout : 'grid';

                        $types = $attrs['types'] ?? ($attrs['type'] ?? []);
                        if (! is_array($types)) {
                            $types = array_filter(array_map('trim', explode(',', (string) $types)));
                        }
                        $types = array_values(array_filter($types));

                        $featuredOnly = (bool) (int) ($attrs['featured_only'] ?? 0);
                        if (in_array('featured', $types, true)) {
                            $featuredOnly = true;
                        }

                        $typeLabels = trans('plugins/product-bundles::bundles.shortcode_groups.types');
                        if (! is_array($typeLabels)) {
                            $typeLabels = [
                                'all' => __('All'),
                                'fixed' => __('Fixed bundles'),
                                'mix' => __('Mix & match'),
                            ];
                        }

                        $selectedTypes = $types ?: array_keys($typeLabels);

                        $fetchBundles = function (?array $typeFilter) use ($featuredOnly, $limit) {
                            $query = \Botble\ProductBundles\Models\Bundle::query()
                                ->where('is_active', true)
                                ->with(['items.product', 'groups.items.product'])
                                ->orderByDesc('is_featured')
                                ->orderByDesc('id');

                            if ($featuredOnly) {
                                $query->where('is_featured', true);
                            }

                            if (! empty($typeFilter)) {
                                $query->whereIn('type', $typeFilter);
                            }

                            $bundles = $query->get()->filter(fn ($b) => $b->isInDateRange())->take($limit);

                            // Ensure fixed bundles have a purchasable ecommerce product id so we can use the normal cart flow.
                            foreach ($bundles as $b) {
                                if ((string) $b->type === 'fixed' && ! $b->ecommerce_product_id) {
                                    try {
                                        app('product-bundles')->syncBundleProduct($b);
                                    } catch (\Throwable $e) {
                                        // ignore
                                    }
                                }
                            }

                            return $bundles;
                        };

                        if ($layout === 'columns') {
                            $groups = [];

                            foreach ($selectedTypes as $typeKey) {
                                if (! isset($typeLabels[$typeKey])) {
                                    continue;
                                }

                                $typeFilter = $typeKey === 'all' ? [] : [$typeKey];
                                $groupBundles = $fetchBundles($typeFilter);

                                if ($groupBundles->isEmpty()) {
                                    continue;
                                }

                                $groups[] = [
                                    'key' => $typeKey,
                                    'title' => $typeLabels[$typeKey],
                                    'bundles' => $groupBundles,
                                ];
                            }

                            if (empty($groups)) {
                                $groups[] = [
                                    'key' => 'all',
                                    'title' => $typeLabels['all'] ?? __('All'),
                                    'bundles' => $fetchBundles([]),
                                ];
                            }

                            return view('plugins/product-bundles::front.bundle-groups', [
                                'groups' => $groups,
                                'bundles' => $groups[0]['bundles'] ?? collect(),
                                'title' => (string) ($attrs['title'] ?? ''),
                                'subtitle' => (string) ($attrs['subtitle'] ?? ''),
                                'layout' => $layout,
                            ])->render();
                        }

                        $typeFilter = [];
                        if (! empty($types) && ! in_array('all', $types, true)) {
                            $typeFilter = array_values(array_intersect($types, ['fixed', 'mix']));
                        }

                        $bundles = $fetchBundles($typeFilter);

                        return view('plugins/product-bundles::front.bundle-groups', [
                            'bundles' => $bundles,
                            'title' => (string) ($attrs['title'] ?? ''),
                            'subtitle' => (string) ($attrs['subtitle'] ?? ''),
                            'layout' => $layout,
                        ])->render();
                    };

                    try {
                        $shortcodeFacade::register('bundle_groups', trans('plugins/product-bundles::bundles.shortcode_groups.name'), trans('plugins/product-bundles::bundles.shortcode_groups.description'), $groupsCb);
                    } catch (\Throwable $e) {
                        try {
                            $shortcodeFacade::register('bundle_groups', trans('plugins/product-bundles::bundles.shortcode_groups.description'), $groupsCb);
                        } catch (\Throwable $e2) {
                            $shortcodeFacade::register('bundle_groups', $groupsCb);
                        }
                    }

                    $previewTabs = '';
                    $previewColumns = '';
                    if (class_exists(Theme::class)) {
                        $previewTabs = Theme::asset()->url('images/shortcodes/ecommerce-product-groups/tabs.png');
                        $previewColumns = Theme::asset()->url('images/shortcodes/ecommerce-product-groups/columns.png');
                    }

                    $previewFallback = asset('vendor/core/core/base/images/ui-selector-placeholder.jpg');

                    try {
                        $shortcodeFacade::setPreviewImage('bundle_groups', $previewTabs ?: $previewFallback);
                    } catch (\Throwable $e) {
                        // ignore
                    }

                    try {
                        $shortcodeFacade::setAdminConfig('bundle_groups', function ($attributes) use ($previewTabs, $previewColumns, $previewFallback) {
                            $attributes = $attributes ?? [];
                            if (is_object($attributes) && method_exists($attributes, 'toArray')) {
                                $attributes = $attributes->toArray();
                            }

                            $layout = (string) Arr::get($attributes, 'layout', 'tabs');
                            if (! in_array($layout, ['tabs', 'columns'], true)) {
                                $layout = 'tabs';
                            }

                            $typeChoices = trans('plugins/product-bundles::bundles.shortcode_groups.types');
                            if (! is_array($typeChoices)) {
                                $typeChoices = [
                                    'all' => __('All'),
                                    'fixed' => __('Fixed bundles'),
                                    'mix' => __('Mix & match'),
                                ];
                            }

                            $selectedTypes = Arr::get($attributes, 'types', Arr::get($attributes, 'type', []));
                            if (! is_array($selectedTypes)) {
                                $selectedTypes = array_filter(array_map('trim', explode(',', (string) $selectedTypes)));
                            }
                            $selectedTypes = array_values(array_filter($selectedTypes));
                            if (empty($selectedTypes)) {
                                $selectedTypes = array_keys($typeChoices);
                            }

                            $tabsPreview = $previewTabs ?: $previewFallback;
                            $columnsPreview = $previewColumns ?: $previewFallback;

                            return ShortcodeForm::createFromArray($attributes)
                                ->withLazyLoading()
                                ->add(
                                    'layout',
                                    UiSelectorField::class,
                                    SelectFieldOption::make()
                                        ->choices([
                                            'tabs' => [
                                                'label' => __('Tabs'),
                                                'image' => $tabsPreview,
                                            ],
                                            'columns' => [
                                                'label' => __('Columns'),
                                                'image' => $columnsPreview,
                                            ],
                                        ])
                                        ->selected($layout)
                                        ->collapsible('layout')
                                )
                                ->add('title', TextField::class)
                                ->add('subtitle', TextField::class)
                                ->add(
                                    'limit',
                                    NumberField::class,
                                    NumberFieldOption::make()
                                        ->label(__('Limit'))
                                        ->placeholder(__('Number of products to show'))
                                        ->defaultValue(8)
                                )
                                ->add(
                                    'types[]',
                                    MultiCheckListField::class,
                                    [
                                        'label' => __('Groups'),
                                        'choices' => $typeChoices,
                                        'value' => $selectedTypes,
                                    ]
                                )
                                ->add(
                                    'featured_only',
                                    CheckboxField::class,
                                    CheckboxFieldOption::make()
                                        ->label(trans('plugins/product-bundles::bundles.shortcode_groups.featured_only'))
                                        ->defaultValue((int) Arr::get($attributes, 'featured_only', 0) === 1)
                                );
                        });
                    } catch (\Throwable $e) {
                        // ignore
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
}
