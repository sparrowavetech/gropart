<?php

namespace SparroWave\Shipmozo\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Models\Shipment;
use Botble\Payment\Enums\PaymentMethodEnum;
use SparroWave\Shipmozo\Shipmozo;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter('handle_shipping_fee', [$this, 'handleShippingFee'], 11, 2);

        if (is_plugin_active('marketplace')) {
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

        \Illuminate\Support\Facades\Event::listen(\Botble\Ecommerce\Events\OrderCancelledEvent::class, function (\Botble\Ecommerce\Events\OrderCancelledEvent $event) {
            $order = $event->order;
            if ($order && $order->shipment && $order->shipment->tracking_id) {
                app(Shipmozo::class)->cancelOrder($order->shipment->tracking_id);
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Botble\Ecommerce\Events\OrderReturnedEvent::class, function (\Botble\Ecommerce\Events\OrderReturnedEvent $event) {
            $orderReturn = $event->order;
            if ($orderReturn && $orderReturn->return_status == \Botble\Ecommerce\Enums\OrderReturnStatusEnum::COMPLETED) {
                app(Shipmozo::class)->pushReturnOrder($orderReturn);
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Botble\Ecommerce\Events\OrderConfirmedEvent::class, function (\Botble\Ecommerce\Events\OrderConfirmedEvent $event) {
            $order = $event->order;

            if (!$order || !$order->id) {
                return;
            }

            $method = $order->shipping_method->getValue();
            $isShipmozo = ($method === SHIPMOZO_SHIPPING_METHOD_NAME) ||
                ($method === \Botble\Ecommerce\Enums\ShippingMethodEnum::DEFAULT && is_numeric($order->shipping_option));

            if ($isShipmozo) {
                // Data Healing: Standardize method name if it's currently 'default'
                if ($method === \Botble\Ecommerce\Enums\ShippingMethodEnum::DEFAULT) {
                    $order->update(['shipping_method' => SHIPMOZO_SHIPPING_METHOD_NAME]);
                }
                // Ensure a shipment exists for this order
                $shipment = $order->shipment;

                if (!$shipment || !$shipment->id) {
                    $shipment = \Botble\Ecommerce\Models\Shipment::query()->create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'weight' => $order->products_weight,
                        'cod_amount' => $order->payment && $order->payment->status != \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED ? $order->amount : 0,
                        'cod_status' => 'pending',
                        'status' => \Botble\Ecommerce\Enums\ShippingStatusEnum::DELIVERING,
                        'price' => $order->shipping_amount,
                        'store_id' => $order->store_id,
                    ]);

                    \Botble\Ecommerce\Models\ShipmentHistory::query()->create([
                        'action' => 'create_from_order',
                        'description' => trans('plugins/ecommerce::order.shipping_was_created_from'),
                        'shipment_id' => $shipment->id,
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                    ]);
                }

                // If no tracking ID exists yet, push the order to ShipMozo
                if (!$shipment->tracking_id) {
                    try {
                        $shipmozo = app(Shipmozo::class);
                        if ($shipmozo->canCreateTransaction($shipment)) {
                            $transaction = $shipmozo->pushOrder($order);

                            if (\Illuminate\Support\Arr::get($transaction, 'result') == 1) {
                                $awb = \Illuminate\Support\Arr::get($transaction, 'awb_number') ?: \Illuminate\Support\Arr::get($transaction, 'data.order_id');

                                if ($awb) {
                                    $labelUrl = $shipmozo->getOrderLabel($awb);

                                    $shipment->tracking_link = 'https://panel.shipmozo.com/track-order/' . $awb;
                                    $shipment->label_url = $labelUrl;
                                    $shipment->tracking_id = $awb;
                                    $shipment->metadata = json_encode($transaction);
                                    $shipment->status = \Botble\Ecommerce\Enums\ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT;
                                    $shipment->save();

                                    \Botble\Ecommerce\Models\ShipmentHistory::query()->create([
                                        'action' => 'create_transaction',
                                        'description' => 'Automatically pushed order to ShipMozo. Tracking ID: ' . $awb,
                                        'order_id' => $shipment->order_id,
                                        'user_id' => 0,
                                        'shipment_id' => $shipment->id,
                                    ]);

                                    \Botble\Ecommerce\Models\ShipmentHistory::query()->create([
                                        'action' => 'update_status',
                                        'description' => trans('plugins/ecommerce::shipping.changed_shipping_status', [
                                            'status' => \Botble\Ecommerce\Enums\ShippingStatusEnum::getLabel(\Botble\Ecommerce\Enums\ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT),
                                        ]),
                                        'order_id' => $shipment->order_id,
                                        'user_id' => 0,
                                        'shipment_id' => $shipment->id,
                                    ]);
                                }
                            } else {
                                $errorMsg = \Illuminate\Support\Arr::get($transaction, 'data.error', \Illuminate\Support\Arr::get($transaction, 'message', 'Failed to generate ShipMozo AWB.'));
                                \Illuminate\Support\Facades\Log::error('ShipMozo Auto-Push Validation Failed for Order ' . $order->id . ': ' . $errorMsg);
                            }
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('ShipMozo Auto-Push Failed: ' . $e->getMessage());
                    }
                }
            }
        });

        if (is_plugin_active('marketplace')) {
            \Illuminate\Support\Facades\Event::listen('eloquent.saved: Botble\Marketplace\Models\Store', function ($store) {
                if ($store && $store->zip_code) {
                    app(Shipmozo::class)->syncWarehouse($store);
                }
            });
        }
    }

    public function handleShippingFee(array $result, array $data): array
    {
        \Illuminate\Support\Facades\Log::info('Shipmozo hook fired. Status is: ' . setting('shipping_shipmozo_status'));

        if (setting('shipping_shipmozo_status') == 1) {

            // Strip out Botble's default internal ecommerce "Default" shipping rules
            $result = [];

            $addressTo = Arr::get($data, 'address_to', []);
            $deliveryPincode = Arr::get($addressTo, 'zip_code') ?: Arr::get($addressTo, 'zip') ?: Arr::get($data, 'zip_code') ?: Arr::get($data, 'zip');

            if (!empty($deliveryPincode)) {
                // Point 4: If store has a warehouse_id, we should pass it or its details to getRates
                if (is_plugin_active('marketplace')) {
                    $storeId = Arr::get($data, 'store_id');
                    if ($storeId) {
                        $store = \Botble\Marketplace\Models\Store::find($storeId);
                        if ($store && $store->warehouse_id) {
                            $data['origin_warehouse_id'] = $store->warehouse_id;
                        }
                    }
                }

                $results = app(Shipmozo::class)->getRates($data);
                if (!empty(Arr::get($results, 'shipment.rates'))) {
                    $result['shipmozo'] = Arr::get($results, 'shipment.rates');
                }
            }
        }

        return $result;
    }

    public function addSettings(?string $settings): string
    {
        $logFiles = [];

        if (setting('shipping_shipmozo_logging')) {
            foreach (BaseHelper::scanFolder(storage_path('logs')) as $file) {
                if (Str::startsWith($file, 'shipmozo-')) {
                    $logFiles[] = $file;
                }
            }
        }

        return $settings . view('plugins/shipmozo::settings', compact('logFiles'))->render();
    }

    public function addPincodeCheck(?string $html, $product): string
    {
        if (setting('shipping_shipmozo_status') != 1 || !$product) {
            return $html;
        }

        return $html . view('plugins/shipmozo::pincode-check', compact('product'))->render();
    }

    public function addWarehouseToStoreForm(\Botble\Base\Forms\FormAbstract $form, \Illuminate\Database\Eloquent\Model $data): \Botble\Base\Forms\FormAbstract
    {
        if (get_class($data) === \Botble\Marketplace\Models\Store::class) {
            $shipmozo = app(Shipmozo::class);
            $warehouses = $shipmozo->getWarehouses();

            $options = ['' => 'Select a Warehouse'];
            foreach (\Illuminate\Support\Arr::get($warehouses, 'data', []) as $warehouse) {
                $options[$warehouse['id']] = $warehouse['name'] . ' (' . $warehouse['pincode'] . ')';
            }

            $form
                ->add('shipmozo_warehouse_section', 'html', [
                    'html' => '<div class="card widget meta-boxes mb-3">
                        <div class="card-header">
                            <h4 class="card-title">' . trans('plugins/shipmozo::shipmozo.shipmozo_warehouse') . '</h4>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    ' . \Botble\Base\Facades\Form::label('warehouse_id', trans('plugins/shipmozo::shipmozo.shipmozo_warehouse'), ['class' => 'control-label']) . '
                                    ' . \Botble\Base\Facades\Form::customSelect('warehouse_id', $options, $data->warehouse_id) . '
                                    ' . \Botble\Base\Facades\Form::helper(trans('plugins/shipmozo::shipmozo.shipmozo_warehouse_selector_hint')) . '
                                </div>
                                <div class="col-md-6">
                                    <a href="#" id="shipmozo-create-warehouse-btn" class="btn btn-secondary w-100">
                                        <i class="ti ti-home-plus"></i> ' . trans('plugins/shipmozo::shipmozo.create_warehouse_from_store') . '
                                    </a>
                                    ' . \Botble\Base\Facades\Form::helper(trans('plugins/shipmozo::shipmozo.create_warehouse_from_store_hint')) . '
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.getElementById("shipmozo-create-warehouse-btn")?.addEventListener("click", function(e) {
                            e.preventDefault();
                            if(confirm("' . trans('plugins/shipmozo::shipmozo.confirm_create_warehouse') . '")) {
                                window.location.href = "' . route('ecommerce.shipments.shipmozo.warehouses.create-from-store', $data->id) . '";
                            }
                        });
                    </script>',
                    'colspan' => 6,
                ]);
        }

        return $form;
    }

    public function saveStoreWarehouseId(string $screen, $request, $model): void
    {
        if ($model instanceof \Botble\Marketplace\Models\Store && $request->has('warehouse_id')) {
            $model->warehouse_id = $request->input('warehouse_id');
            $model->save();
        }
    }
}
