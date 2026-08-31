<?php

namespace SparroWave\VendorVerifiedBadge\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\Html;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Forms\CustomerForm;
use Botble\Ecommerce\Forms\Fronts\Auth\RegisterForm;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Tables\CustomerTable;
use Botble\Ecommerce\Tables\ProductTable as EcommerceProductTable;
use Botble\Marketplace\Forms\Fronts\BecomeVendorForm;
use Botble\Marketplace\Forms\StoreForm;
use Botble\Marketplace\Forms\VendorStoreForm;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Tables\ProductTable as MarketplaceProductTable;
use Botble\Marketplace\Tables\StoreTable;
use Botble\Marketplace\Tables\UnverifiedVendorTable;
use Botble\Marketplace\Tables\VendorTable;
use Botble\Marketplace\Tables\WithdrawalTable;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\FormattedColumn;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use SparroWave\VendorVerifiedBadge\Enums\ShopTypeEnum;
use SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->extendCustomerForm();
        $this->extendVendorStoreForm();
        $this->extendBecomeVendorForm();
        $this->extendRegisterForm();
        $this->hookDashboardMenuGating();
        $this->hookAdminDataTables();
        $this->hookAdminStoreViewPage();
        $this->hookVendorDashboardContent();
        $this->hookOrderEditStoreBadges();
        $this->listenToEvents();
    }

    protected function extendCustomerForm(): void
    {
        CustomerForm::extend(function (CustomerForm $form): void {
            $customer = $form->getModel();
            if ($customer && $customer->id && $customer->is_vendor && $customer->store) {
                $store = $customer->store;

                $statusBadge = $store->is_verified
                    ? '<span class="badge bg-success text-success-fg"><i class="ti ti-check me-1"></i>' . trans('plugins/vendor-verified-badge::vendor-badge.verified') . '</span>'
                    : '<span class="badge bg-secondary text-secondary-fg">' . trans('plugins/vendor-verified-badge::vendor-badge.unverified') . '</span>';

                $form->addMetaBoxes([
                    'vendor_verification_badge' => [
                        'title' => trans('plugins/vendor-verified-badge::vendor-badge.name'),
                        'content' => view('plugins/vendor-verified-badge::customers.vendor-meta-box', compact('customer', 'store'))->render(),
                        'header_actions' => $statusBadge,
                        'wrap' => true,
                        'priority' => 1,
                    ],
                ]);
            }
        });
    }

    protected function extendVendorStoreForm(): void
    {
        // Vendor Dashboard Store Settings Form
        VendorStoreForm::extend(function (VendorStoreForm $form): void {
            if (! $form->has('shop_category')) {
                $options = SelectFieldOption::make()
                    ->label(trans('plugins/vendor-verified-badge::vendor-badge.shop_type'))
                    ->choices(['' => __('Select Shop Type')] + ShopTypeEnum::labels())
                    ->colspan(3);

                if ($form->getModel()?->shop_category) {
                    $options->selected($form->getModel()->shop_category);
                }

                if ($form->has('phone')) {
                    $form->addAfter('phone', 'shop_category', SelectField::class, $options);
                } else {
                    $form->add('shop_category', SelectField::class, $options);
                }
            }
        });
    }

    protected function extendBecomeVendorForm(): void
    {
        BecomeVendorForm::beforeRendering(function (BecomeVendorForm $form): BecomeVendorForm {
            if (! $form->has('shop_category')) {
                $options = SelectFieldOption::make()
                    ->label(trans('plugins/vendor-verified-badge::vendor-badge.shop_type'))
                    ->choices(['' => __('Select Shop Type')] + ShopTypeEnum::labels())
                    ->required()
                    ->colspan(6);

                if ($form->has('shop_url')) {
                    $form->addAfter('shop_url', 'shop_category', SelectField::class, $options);
                } else {
                    $form->add('shop_category', SelectField::class, $options);
                }
            }

            return $form;
        });
    }

    protected function extendRegisterForm(): void
    {
        RegisterForm::beforeRendering(function (RegisterForm $form): RegisterForm {
            if (! $form->has('shop_category')) {
                $options = SelectFieldOption::make()
                    ->label(trans('plugins/vendor-verified-badge::vendor-badge.shop_type'))
                    ->choices(['' => __('Select Shop Type')] + ShopTypeEnum::labels())
                    ->required()
                    ->colspan(6);

                if ($form->has('shop_url')) {
                    $form->addAfter('shop_url', 'shop_category', SelectField::class, $options);
                } else {
                    $form->add('shop_category', SelectField::class, $options);
                }
            }

            return $form;
        });
    }

    protected function hookDashboardMenuGating(): void
    {
        DashboardMenu::for('vendor')->beforeRetrieving(function (): void {
            if (! VendorBadgeHelper::isMenuGatingEnabled()) {
                return;
            }

            $customer = auth('customer')->user();
            if (! $customer || ! $customer->is_vendor) {
                return;
            }

            $scoreData = VendorBadgeHelper::isVendorProfileComplete($customer->id);

            // If profile is incomplete (< threshold), hide operational menus
            if (! $scoreData['status']) {
                $lockedMenus = [
                    'cms-marketplace-vendor-products',
                    'cms-marketplace-vendor-orders',
                    'cms-marketplace-vendor-enquiries',
                    'cms-marketplace-vendor-discounts',
                    'cms-marketplace-vendor-withdrawals',
                    'cms-marketplace-vendor-revenues',
                    'cms-marketplace-vendor-messages',
                    'cms-marketplace-vendor-reviews',
                ];

                foreach ($lockedMenus as $menuId) {
                    DashboardMenu::removeItem($menuId, 'vendor');
                }
            }
        });
    }

    protected function hookAdminDataTables(): void
    {
        // 1. Add mp_stores.shop_category to query
        add_filter(BASE_FILTER_TABLE_QUERY, function ($query, $table) {
            if ($table instanceof StoreTable) {
                $query->addSelect('mp_stores.shop_category');
            }

            return $query;
        }, 130, 2);

        // 2. Add columns to StoreTable headings
        add_filter(BASE_FILTER_TABLE_HEADINGS, function (array $headings, Model|string|null $model, TableAbstract $table): array {
            if ($table instanceof StoreTable) {
                $shopCategoryColumn = FormattedColumn::make('shop_category')
                    ->title(trans('plugins/vendor-verified-badge::vendor-badge.shop_type'))
                    ->alignCenter()
                    ->orderable(false)
                    ->searchable(false)
                    ->renderUsing(function (FormattedColumn $column) {
                        return VendorBadgeHelper::renderShopTypeBadge($column->getItem());
                    });

                $isVerifiedColumn = FormattedColumn::make('is_verified')
                    ->title(trans('plugins/vendor-verified-badge::vendor-badge.verified'))
                    ->alignCenter()
                    ->orderable(false)
                    ->searchable(false)
                    ->renderUsing(function (FormattedColumn $column) {
                        $item = $column->getItem();
                        if ($item && $item->is_verified) {
                            return Html::tag('span', trans('plugins/vendor-verified-badge::vendor-badge.verified'), ['class' => 'badge bg-success text-success-fg'])->toHtml();
                        }

                        return Html::tag('span', trans('plugins/vendor-verified-badge::vendor-badge.unverified'), ['class' => 'badge bg-secondary text-secondary-fg'])->toHtml();
                    });

                // Insert after Name column (position 4)
                array_splice($headings, 4, 0, [$shopCategoryColumn, $isVerifiedColumn]);
            }

            return $headings;
        }, 130, 3);

        // 3. Format DataTables row values
        add_filter(BASE_FILTER_GET_LIST_DATA, function ($data, $model, $table) {
            // 1. StoreTable (/admin/marketplaces/stores)
            if ($table instanceof StoreTable) {
                $data->editColumn('name', function (Store $store) {
                    $nameHtml = Html::link(route('marketplace.store.edit', $store->id), BaseHelper::clean($store->name));
                    $verifiedBadge = VendorBadgeHelper::renderVerifiedIcon($store);

                    return $nameHtml . ' ' . $verifiedBadge;
                });
            }

            // 2. VendorTable (/admin/marketplaces/vendors)
            if ($table instanceof VendorTable) {
                $data->editColumn('store_name', function ($item) {
                    if (! $item->store) {
                        return '&mdash;';
                    }
                    $storeLink = Html::link(route('marketplace.store.edit', $item->store->id), BaseHelper::clean($item->store->name), ['target' => '_blank']);
                    $badges = VendorBadgeHelper::renderBadges($item->store);

                    return $storeLink . ' ' . $badges;
                });
            }

            // 3. UnverifiedVendorTable (/admin/marketplaces/unverified-vendors)
            if ($table instanceof UnverifiedVendorTable) {
                $data->editColumn('store_name', function ($item) {
                    if (! $item->store) {
                        return '&mdash;';
                    }
                    $badges = VendorBadgeHelper::renderBadges($item->store);

                    return BaseHelper::clean($item->store->name) . ' ' . $badges;
                });
            }

            // 4. WithdrawalTable (/admin/marketplaces/withdrawals)
            if ($table instanceof WithdrawalTable) {
                $data->editColumn('customer_id', function ($item) {
                    if (! $item->customer || ! $item->customer->id) {
                        return '&mdash;';
                    }
                    $name = Html::link(route('customers.edit', $item->customer->id), BaseHelper::clean($item->customer->name));
                    $badges = $item->customer->store ? VendorBadgeHelper::renderBadges($item->customer->store) : '';

                    return $name . ' ' . $badges;
                });
            }

            // 5. CustomerTable (/admin/customers)
            if ($table instanceof CustomerTable) {
                $data->editColumn('name', function ($item) {
                    $name = Html::link(route('customers.edit', $item->id), BaseHelper::clean($item->name));
                    if ($item->is_vendor && $item->store) {
                        $badges = VendorBadgeHelper::renderBadges($item->store);
                        $name .= ' ' . $badges;
                    }

                    return $name;
                });
            }

            // 6. Product Tables (/admin/ecommerce/products & /vendor/products)
            if ($table instanceof EcommerceProductTable || $table instanceof MarketplaceProductTable) {
                if (method_exists($data, 'editColumn')) {
                    $data->editColumn('store_id', function ($item) {
                        $store = $item->original_product && $item->original_product->store->name ? $item->original_product->store : $item->store;

                        if (! $store || ! $store->name) {
                            return '&mdash;';
                        }

                        $storeLink = Html::link($store->url, BaseHelper::clean($store->name), ['target' => '_blank']);
                        $badges = VendorBadgeHelper::renderBadges($store);

                        return $storeLink . ' ' . $badges;
                    });
                }
            }

            return $data;
        }, 130, 3);
    }

    protected function hookAdminStoreViewPage(): void
    {
        view()->composer('plugins/marketplace::stores.index', function ($view): void {
            $store = $view->getData()['store'] ?? null;
            if ($store) {
                $badgesHtml = (string) VendorBadgeHelper::renderBadges($store);
                add_filter('core_layout_end_body', function ($footerHtml) use ($badgesHtml) {
                    return $footerHtml . '<script>window.adminStoreBadgesHtml = ' . json_encode($badgesHtml) . ';</script>';
                }, 120);
            }
        });
    }

    protected function hookOrderEditStoreBadges(): void
    {
        add_filter('ecommerce_order_detail_extra_info', function (string $html, $order): string {
            if ($order && $order->store && $order->store->name) {
                $badges = VendorBadgeHelper::renderBadges($order->store);
                $storeName = preg_quote(e($order->store->name), '/');
                $pattern = '/(<a[^>]*>' . $storeName . '<\/a>)/i';
                if (preg_match($pattern, $html)) {
                    $html = preg_replace($pattern, '$1 ' . $badges, $html, 1);
                }
            }

            return $html;
        }, 130, 2);

        add_filter('ecommerce_order_product_item_extra_info_after', function (string $html, $orderProduct, $order): string {
            $store = $order?->store;
            if (! $store && $orderProduct?->product?->original_product) {
                $store = $orderProduct->product->original_product->store;
            }

            if ($store && $store->name) {
                $badges = VendorBadgeHelper::renderBadges($store);
                $storeName = preg_quote(e($store->name), '/');
                $pattern = '/(<a[^>]*>' . $storeName . '<\/a>)/i';
                if (preg_match($pattern, $html)) {
                    $html = preg_replace($pattern, '$1 ' . $badges, $html, 1);
                }
            }

            return $html;
        }, 130, 3);
    }

    protected function hookVendorDashboardContent(): void
    {
        add_filter('marketplace_vendor_dashboard_before_content', function ($content, $customer) {
            return $content . view('plugins/vendor-verified-badge::dashboard.progress-bar')->render();
        }, 120, 2);
    }

    protected function listenToEvents(): void
    {
        $this->app['events']->listen(CreatedContentEvent::class, function (CreatedContentEvent $event): void {
            if ($event->data instanceof Store) {
                $request = request();
                if ($request->has('shop_category') && $request->input('shop_category')) {
                    $category = $request->input('shop_category');
                    if (ShopTypeEnum::isValid($category)) {
                        $event->data->shop_category = $category;
                        $event->data->save();
                    }
                }
            }
        });

        $this->app['events']->listen(Registered::class, function (Registered $event): void {
            $request = request();
            if ($request->has('shop_category') && $request->input('shop_category')) {
                $category = $request->input('shop_category');
                if (ShopTypeEnum::isValid($category)) {
                    $customer = $event->user;
                    if ($customer instanceof Customer) {
                        $store = Store::where('customer_id', $customer->id)->first();
                        if ($store) {
                            $store->shop_category = $category;
                            $store->save();
                        }
                    }
                }
            }
        });

        $this->app['events']->listen(\Botble\Base\Events\UpdatedContentEvent::class, function (\Botble\Base\Events\UpdatedContentEvent $event): void {
            $request = request();

            // When Admin updates Customer (Vendor Owner)
            if ($event->data instanceof Customer && $event->data->is_vendor && $event->data->store) {
                $store = $event->data->store;
                $changed = false;

                if ($request->has('shop_category')) {
                    $category = $request->input('shop_category');
                    if (ShopTypeEnum::isValid($category) || empty($category)) {
                        $store->shop_category = $category ?: null;
                        $changed = true;
                    }
                }

                if ($request->has('is_verified')) {
                    $store->is_verified = (bool) $request->input('is_verified');
                    if ($store->is_verified && ! $store->verified_at) {
                        $store->verified_at = now();
                        $store->verified_by = auth()->id();
                    } elseif (! $store->is_verified) {
                        $store->verified_at = null;
                        $store->verified_by = null;
                    }
                    $changed = true;
                }

                if ($changed) {
                    $store->save();
                }
            }

            // When Vendor updates store in vendor dashboard
            if ($event->data instanceof Store) {
                if ($request->has('shop_category')) {
                    $category = $request->input('shop_category');
                    if (ShopTypeEnum::isValid($category) || empty($category)) {
                        $event->data->shop_category = $category ?: null;
                        $event->data->save();
                    }
                }
            }
        });
    }
}
