<?php

namespace SparroWave\Shipmozo\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Form;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\OrderReturnStatusEnum;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\Ecommerce\Events\OrderReturnedEvent;
use Botble\Ecommerce\Events\ProductVariationCreated;
use Botble\Ecommerce\Forms\ProductForm;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Tables\ProductTable;
use Botble\Marketplace\Forms\VendorProductForm;
use Botble\Marketplace\Models\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SparroWave\Shipmozo\Shipmozo;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter('handle_shipping_fee', [$this, 'handleShippingFee'], 130, 2);
        add_filter('ecommerce_product_has_shipmozo_shipping', [$this, 'checkProductShipmozoEligibility'], 10, 2);
        add_filter('base_filter_table_filters', [$this, 'addTableFilters'], 130, 2);

        // Product Form Extensions (Admin & Marketplace Vendor)
        if (class_exists(ProductForm::class)) {
            ProductForm::extend(function (ProductForm $form) {
                $this->injectProductShipmozoField($form);
            });
        }

        if (class_exists(VendorProductForm::class)) {
            VendorProductForm::extend(function (VendorProductForm $form) {
                $this->injectProductShipmozoField($form);
            });
        }

        Event::listen([CreatedContentEvent::class, UpdatedContentEvent::class], [$this, 'saveProductShipmozoField']);
        Event::listen(ProductVariationCreated::class, [$this, 'inheritVariationShipmozoShipping']);

        if ($this->supportsMarketplaceWarehouses()) {
            add_filter(BASE_FILTER_BEFORE_RENDER_FORM, [$this, 'addWarehouseToStoreForm'], 120, 2);
            add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [$this, 'saveStoreWarehouseId'], 120, 3);
            add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [$this, 'saveStoreWarehouseId'], 120, 3);
        }

        add_filter(SHIPPING_METHODS_SETTINGS_PAGE, [$this, 'addSettings'], 2);

        add_filter(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, [$this, 'addPincodeCheck'], 12, 2);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == ShippingMethodEnum::class) {
                $values['SHIPMOZO'] = SHIPMOZO_SHIPPING_METHOD_NAME;
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == ShippingMethodEnum::class && $value == SHIPMOZO_SHIPPING_METHOD_NAME) {
                return 'Shipmozo';
            }

            return $value;
        }, 2, 2);

        add_filter('shipment_buttons_detail_order', function (?string $content, Shipment $shipment) {
            Assets::addScriptsDirectly('vendor/core/plugins/shipmozo/js/shipmozo.js');

            return $content . view('plugins/shipmozo::buttons', compact('shipment'))->render();
        }, 1, 2);

        Event::listen(OrderCancelledEvent::class, function (OrderCancelledEvent $event) {
            $order = $event->order;
            $shipmozo = app(Shipmozo::class);

            if ($order && $shipmozo->isShipmozoOrder($order) && $order->shipment?->tracking_id) {
                try {
                    $result = $shipmozo->cancelOrder($shipmozo->getOrderId($order), $order->shipment->tracking_id);
                    if (Arr::get($result, 'result') != 1) {
                        $shipmozo->logError('Order cancellation was rejected by ShipMozo', [
                            'order_id' => $order->getKey(),
                            'message' => Arr::get($result, 'message', Arr::get($result, 'data.error')),
                        ]);
                    }
                } catch (\Throwable $exception) {
                    $shipmozo->logError('Order cancellation failed', [
                        'order_id' => $order->getKey(),
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        });

        Event::listen(OrderReturnedEvent::class, function (OrderReturnedEvent $event) {
            $orderReturn = $event->order;
            $shipmozo = app(Shipmozo::class);

            if ($orderReturn
                && $orderReturn->return_status == OrderReturnStatusEnum::COMPLETED
                && $orderReturn->order
                && $shipmozo->isShipmozoOrder($orderReturn->order)
            ) {
                $pushedKey = "shipmozo:return:{$orderReturn->getKey()}:pushed";
                if (Cache::has($pushedKey)) {
                    return;
                }

                $lock = Cache::lock("shipmozo:return:{$orderReturn->getKey()}", 60);
                if (! $lock->get()) {
                    return;
                }

                try {
                    $result = $shipmozo->pushReturnOrder($orderReturn);
                    if (Arr::get($result, 'result') == 1) {
                        Cache::put($pushedKey, true, now()->addDays(30));
                    } else {
                        $shipmozo->logError('Return order was rejected by ShipMozo', [
                            'order_return_id' => $orderReturn->getKey(),
                            'message' => Arr::get($result, 'message', Arr::get($result, 'data.error')),
                        ]);
                    }
                } catch (\Throwable $exception) {
                    $shipmozo->logError('Return order push failed', [
                        'order_return_id' => $orderReturn->getKey(),
                        'message' => $exception->getMessage(),
                    ]);
                } finally {
                    $lock->release();
                }
            }
        });

        if ($this->supportsMarketplaceWarehouses()) {
            Event::listen('eloquent.saved: Botble\Marketplace\Models\Store', function ($store) {
                if (setting('shipping_shipmozo_status') && $store && $store->zip_code && ! $store->warehouse_id) {
                    if (preg_match('/^[1-9][0-9]{5}$/', (string) $store->zip_code) && $store->address) {
                        try {
                            $result = app(Shipmozo::class)->syncWarehouse($store);
                            $warehouseId = Arr::get($result, 'data.warehouse_id', Arr::get($result, 'id'));

                            if ($warehouseId) {
                                $store->warehouse_id = $warehouseId;
                                $store->saveQuietly();
                            }
                        } catch (\Throwable $exception) {
                            app(Shipmozo::class)->logError('Warehouse synchronization failed', [
                                'store_id' => $store->getKey(),
                                'message' => $exception->getMessage(),
                            ]);
                        }
                    }
                }
            });
        }
    }

    public function handleShippingFee(array $result, array $data): array
    {
        if (setting('shipping_shipmozo_status') != 1 || empty($data['items'])) {
            return $result;
        }

        // Marketplace puts the store in extra; older callers used a top-level key.
        $storeId = Arr::get($data, 'extra.store_id', Arr::get($data, 'store_id'));
        if (is_plugin_active('marketplace') && $storeId) {
            $store = Store::find($storeId);
            // Only the current Vendor Managed Shipping setting controls self-fulfilment.
            if ($store && (bool) $store->vendor_managed_shipping) {
                return $result;
            }
        }

        $isIndividualScope = setting('shipping_shipmozo_scope', 'all') === 'individual';
        $nonShipmozoItems = [];
        $shipmozoEligibleItems = [];

        if ($isIndividualScope) {
            foreach ($data['items'] as $productId => $item) {
                $isItemEligible = $this->checkProductShipmozoEligibility(null, $item + ['id' => $productId]);
                if ($isItemEligible) {
                    $shipmozoEligibleItems[$productId] = $item;
                } else {
                    $nonShipmozoItems[$productId] = $item;
                }
            }

            // If NO items in this package are ShipMozo-enabled, return default core shipping rules
            if (empty($shipmozoEligibleItems)) {
                return $result;
            }

            // Only pass ShipMozo-eligible items for weight & rate calculation
            $data['items'] = $shipmozoEligibleItems;
        }

        // Run after Product Free Shipping: zero customer delivery cost still needs
        // a real courier. Keep the selected courier for booking the physical parcel.
        $freeDelivery = isset($result[ShippingMethodEnum::DEFAULT]['free_shipping']);
        $data['shipmozo_serviceability_only'] = $freeDelivery;
        $shipmozo = app(Shipmozo::class);
        try {
            $rates = Arr::get($shipmozo->getRates($data), 'shipment.rates', []);
            if (! $rates) {
                $rates = $shipmozo->unavailableRates('unavailable');
            }
        } catch (\Throwable $exception) {
            $shipmozo->logError('Rate calculation failed', ['message' => $exception->getMessage()]);
            $rates = $shipmozo->unavailableRates('api_error');
        }

        // Mixed Cart rate handling: If this package has non-ShipMozo items too,
        // extract the baseline standard shipping fee and add to courier price
        if ($isIndividualScope && ! empty($nonShipmozoItems) && ! $freeDelivery) {
            $defaultNonShipmozoFee = 0;
            $defaultOptions = Arr::get($result, ShippingMethodEnum::DEFAULT, []);
            foreach ($defaultOptions as $opt) {
                if (isset($opt['price']) && is_numeric($opt['price']) && empty($opt['disabled'])) {
                    $defaultNonShipmozoFee = (float) $opt['price'];
                    break;
                }
            }

            if ($defaultNonShipmozoFee > 0) {
                foreach ($rates as $key => &$rate) {
                    if (is_array($rate) && isset($rate['price']) && empty($rate['disabled'])) {
                        $rate['price'] += $defaultNonShipmozoFee;
                    }
                }
            }
        }

        if ($freeDelivery) {
            $hasActiveRates = collect($rates)->contains(fn ($rate) => is_array($rate) && empty($rate['disabled']));

            // If the destination pincode is unserviceable, block with no_service
            if (! $hasActiveRates) {
                unset($result[ShippingMethodEnum::DEFAULT]);
                $result[SHIPMOZO_SHIPPING_METHOD_NAME] = $rates;

                return $result;
            }

            // Pincode is serviceable! Check if admin configured to hide other methods
            $hideOtherMethods = (bool) get_ecommerce_setting('hide_other_shipping_options_if_it_has_free_shipping', false)
                || (bool) setting('product_free_shipping_hide_other_methods', true);

            if ($hideOtherMethods) {
                // Keep ONLY the clean, direct Free Delivery option
                return $result;
            }

            // Otherwise, keep Free Delivery as the primary default option, and include carrier rates with their actual prices
            $result[SHIPMOZO_SHIPPING_METHOD_NAME] = $rates;

            return $result;
        }

        unset($result[ShippingMethodEnum::DEFAULT]);
        $result[SHIPMOZO_SHIPPING_METHOD_NAME] = $rates;

        return $result;
    }

    public function checkProductShipmozoEligibility(?bool $enabled, array $item): bool
    {
        if (setting('shipping_shipmozo_scope', 'all') === 'all') {
            return true;
        }

        if (isset($item['enable_shipmozo_shipping'])) {
            return (bool) $item['enable_shipmozo_shipping'];
        }

        $productId = Arr::get($item, 'id') ?: Arr::get($item, 'product_id');

        if ($productId) {
            $product = Product::find($productId);
            if ($product) {
                return method_exists($product, 'isShipmozoShippingEnabled')
                    ? $product->isShipmozoShippingEnabled()
                    : (bool) $product->enable_shipmozo_shipping;
            }
        }

        return false;
    }

    public function injectProductShipmozoField(FormAbstract $form): void
    {
        if (setting('shipping_shipmozo_status') != 1) {
            return;
        }

        if (setting('shipping_shipmozo_scope', 'all') !== 'individual') {
            return;
        }

        $model = $form->getModel();
        $helperText = trans('plugins/shipmozo::shipmozo.enable_shipmozo_shipping_helper');
        $isDisabled = false;

        // Multi-vendor context checks
        if (is_plugin_active('marketplace')) {
            $store = null;

            if (class_exists(VendorProductForm::class) && $form instanceof VendorProductForm) {
                $store = auth('customer')->user()?->store;

                if ($store && (bool) $store->vendor_managed_shipping) {
                    // Vendor manages own shipping -> suppress ShipMozo toggle completely
                    return;
                }

                if ($store && empty($store->warehouse_id)) {
                    $helperText = '<span class="text-danger fw-medium d-block mt-1"><i class="ti ti-alert-triangle me-1"></i> '
                        . trans('plugins/shipmozo::shipmozo.vendor_warehouse_unmapped_warning') . '</span>';
                    $isDisabled = true;
                }
            } elseif ($model && ($model->store_id || $model->original_product?->store_id)) {
                $storeId = $model->store_id ?: $model->original_product?->store_id;
                $store = Store::find($storeId);

                if ($store && (bool) $store->vendor_managed_shipping) {
                    return;
                }

                if ($store && empty($store->warehouse_id)) {
                    $helperText .= ' <span class="text-warning fw-medium d-block mt-1"><i class="ti ti-alert-triangle me-1"></i> '
                        . trans('plugins/shipmozo::shipmozo.product_warehouse_unmapped_warning') . '</span>';
                }
            }
        }

        $form->addAfter(
            'is_featured',
            'enable_shipmozo_shipping',
            OnOffField::class,
            OnOffFieldOption::make()
                ->label(trans('plugins/shipmozo::shipmozo.enable_shipmozo_shipping_label'))
                ->helperText($helperText)
                ->defaultValue(false)
                ->value((bool) ($model ? $model->enable_shipmozo_shipping : false))
                ->disabled($isDisabled)
        );
    }

    public function saveProductShipmozoField(CreatedContentEvent|UpdatedContentEvent $event): void
    {
        if (setting('shipping_shipmozo_status') != 1) {
            return;
        }

        $model = $event->data;

        if (! $model instanceof Product) {
            return;
        }

        $request = $event->request;

        if ($request->has('enable_shipmozo_shipping')) {
            $model->enable_shipmozo_shipping = $request->boolean('enable_shipmozo_shipping');
            $model->saveQuietly();
        }
    }

    public function inheritVariationShipmozoShipping(ProductVariationCreated $event): void
    {
        if (setting('shipping_shipmozo_status') != 1) {
            return;
        }

        $product = $event->product;

        if (! $product->is_variation) {
            return;
        }

        $variation = ProductVariation::query()->where('product_id', $product->getKey())->first();

        if ($variation && $variation->configurable_product_id) {
            $parent = Product::find($variation->configurable_product_id);
            if ($parent) {
                $product->enable_shipmozo_shipping = (bool) $parent->enable_shipmozo_shipping;
                $product->saveQuietly();
            }
        }
    }

    public function addTableFilters(array $filters, mixed $table): array
    {
        if (setting('shipping_shipmozo_status') != 1) {
            return $filters;
        }

        if (
            $table instanceof ProductTable ||
            (is_plugin_active('marketplace') && class_exists(\Botble\Marketplace\Tables\ProductTable::class) && $table instanceof \Botble\Marketplace\Tables\ProductTable)
        ) {
            $filters['enable_shipmozo_shipping'] = [
                'title' => trans('plugins/shipmozo::shipmozo.filter_shipmozo_shipping'),
                'type' => 'customSelect',
                'choices' => [
                    1 => trans('core/base::base.yes'),
                    0 => trans('core/base::base.no'),
                ],
                'validate' => 'required|in:1,0',
            ];
        }

        return $filters;
    }

    public function addSettings(?string $settings): string
    {
        $logFiles = [];

        if (setting('shipping_shipmozo_logging')) {
            foreach (BaseHelper::scanFolder(storage_path('logs')) as $file) {
                if ($file === 'shipmozo.log' || (Str::startsWith($file, 'shipmozo-') && Str::endsWith($file, '.log'))) {
                    $logFiles[] = $file;
                }
            }
        }

        usort($logFiles, function ($a, $b) {
            return filemtime(storage_path('logs/' . $b)) <=> filemtime(storage_path('logs/' . $a));
        });

        return (string) $settings . view('plugins/shipmozo::settings', compact('logFiles'))->render();
    }

    public function addPincodeCheck(?string $html, mixed $product): string
    {
        if (! setting('shipping_shipmozo_status') || ! $product) {
            return (string) $html;
        }

        if (request()->routeIs('public.single') || request()->routeIs('public.products.show') || request()->is('products/*')) {
            $routeSlug = request()->route('slug');
            if ($routeSlug && $product->slug && $routeSlug !== $product->slug) {
                return (string) $html;
            }
        }

        if (request()->has('farmart_helper_card_context') || request()->has('farmart_child_card_context') || request()->has('is_product_card')) {
            return (string) $html;
        }

        if (is_plugin_active('marketplace')) {
            $store = $product->store;
            if ($store && $store->vendor_managed_shipping) {
                return (string) $html;
            }
        }

        $isValidContext = request()->is('products/*', 'product/*', '*/products/*', '*/product/*', '*quick-view*', 'ajax/quick-view/*')
            || (request()->ajax() && request()->is('*quick*'));

        if (! $isValidContext) {
            return (string) $html;
        }

        return $html . view('plugins/shipmozo::pincode-check', compact('product'))->render();
    }

    public function addWarehouseToStoreForm(FormAbstract $form, Model $data): FormAbstract
    {
        if (get_class($data) === Store::class) {
            $shipmozo = app(Shipmozo::class);
            try {
                $warehouses = $shipmozo->getWarehouses();
            } catch (\Throwable $exception) {
                $shipmozo->logError('Unable to load warehouses', ['message' => $exception->getMessage()]);
                $warehouses = [];
            }

            $options = ['' => trans('plugins/shipmozo::shipmozo.select_a_warehouse')];
            foreach (Arr::get($warehouses, 'data', []) as $warehouse) {
                $options[$warehouse['id']] = Arr::get($warehouse, 'address_title', Arr::get($warehouse, 'name', 'Warehouse'))
                    . ' (' . Arr::get($warehouse, 'pincode', '') . ')';
            }

            $warningHtml = '';
            if (setting('shipping_shipmozo_status') == 1) {
                if ($data->vendor_managed_shipping) {
                    $warningHtml = '<div class="alert alert-info py-2 px-3 mb-2 small rounded"><i class="ti ti-info-circle me-1"></i> '
                        . trans('plugins/shipmozo::shipmozo.store_vendor_managed_shipping_notice') . '</div>';
                } elseif (empty($data->warehouse_id)) {
                    $warningHtml = '<div class="alert alert-warning py-2 px-3 mb-2 small rounded"><i class="ti ti-alert-triangle me-1"></i> '
                        . trans('plugins/shipmozo::shipmozo.store_warehouse_unmapped_warning') . '</div>';
                }
            }

            $form
                ->add('shipmozo_warehouse_section', 'html', [
                    'colspan' => 6,
                    'html' => '<div class="card mb-3" style="background: linear-gradient(135deg, rgba(248, 250, 252, 0.9) 0%, rgba(241, 245, 249, 0.95) 100%); border: 1px solid #cbd5e1; border-radius: 8px;">
                        <div class="card-header py-2 px-3" style="background: transparent; border-bottom: 1px solid rgba(203, 213, 225, 0.6);">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h4 class="card-title text-dark fw-bold mb-0"><i class="ti ti-building-warehouse text-primary me-1"></i> ' . trans('plugins/shipmozo::shipmozo.shipmozo_warehouse') . '</h4>
                                <span class="badge bg-azure-lt text-azure px-2 py-1"><i class="ti ti-truck me-1"></i> ' . trans('plugins/shipmozo::shipmozo.logistics_origin') . '</span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            ' . $warningHtml . '
                            <div class="row align-items-center g-3">
                                <div class="col-md-6">
                                    ' . Form::label('warehouse_id', trans('plugins/shipmozo::shipmozo.shipmozo_warehouse'), ['class' => 'form-label fw-medium mb-1']) . '
                                    ' . Form::customSelect('warehouse_id', $options, $data->warehouse_id) . '
                                    <small class="form-hint text-muted mt-1 d-block" style="font-size: 12px; font-weight: normal; line-height: 1.4;">' . trans('plugins/shipmozo::shipmozo.shipmozo_warehouse_selector_hint') . '</small>
                                </div>
                                <div class="col-md-6">
                                    ' . ($data->getKey() ? '
                                    <button type="button" id="shipmozo-create-warehouse-btn" class="btn btn-dark w-100 py-2">
                                        <i class="ti ti-home-plus me-1"></i> ' . trans('plugins/shipmozo::shipmozo.create_warehouse_from_store') . '
                                    </button>
                                    <small class="form-hint text-muted mt-1 d-block text-center" style="font-size: 12px; font-weight: normal; line-height: 1.4;">' . trans('plugins/shipmozo::shipmozo.create_warehouse_from_store_hint') . '</small>' : '') . '
                                </div>
                            </div>
                        </div>
                    </div>
                    ' . ($data->getKey() ? '
                    <script>
                        document.getElementById("shipmozo-create-warehouse-btn")?.addEventListener("click", function(e) {
                            e.preventDefault();
                            if(confirm("' . trans('plugins/shipmozo::shipmozo.confirm_create_warehouse') . '")) {
                                fetch("' . route('ecommerce.shipments.shipmozo.warehouses.create-from-store', $data->id) . '", {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": "' . csrf_token() . '",
                                        "Accept": "application/json",
                                        "X-Requested-With": "XMLHttpRequest"
                                    }
                                }).then(response => response.json()).then(response => {
                                    if (response.error) {
                                        Botble.showError(response.message);
                                        return;
                                    }

                                    Botble.showSuccess(response.message);
                                    window.location.reload();
                                }).catch(() => Botble.showError("' . trans('plugins/shipmozo::shipmozo.warehouse_create_unable') . '"));
                            }
                        });
                    </script>' : ''),
                    'colspan' => 6,
                ]);
        }

        return $form;
    }

    public function saveStoreWarehouseId(string $screen, $request, $model): void
    {
        if ($model instanceof Store && $request->has('warehouse_id')) {
            $model->warehouse_id = $request->input('warehouse_id');
            $model->save();
        }
    }

    private function supportsMarketplaceWarehouses(): bool
    {
        return is_plugin_active('marketplace')
            && Schema::hasTable('mp_stores')
            && Schema::hasColumn('mp_stores', 'warehouse_id');
    }
}
