<?php

namespace SparroWave\Shipmozo\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Marketplace\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use SparroWave\Shipmozo\Providers\HookServiceProvider;
use SparroWave\Shipmozo\Shipmozo;
use Throwable;

class ShipmozoController extends BaseController
{
    protected string|int|null $userId = 0;

    public function __construct(protected Shipmozo $shipmozo)
    {
        if (is_in_admin(true) && Auth::check()) {
            $this->userId = Auth::id();
        }
    }

    public function show(int $id, BaseHttpResponse $response)
    {
        /**
         * @var Shipment $shipment
         */
        $shipment = Shipment::query()->findOrFail($id);
        $this->check($shipment);

        $order = $shipment->order;
        $rateData = $this->getRateData($order);
        $rates = $this->resolveRatesWithNonShipmozo($rateData);
        $rate = Arr::first($rates, fn (array $item): bool => Arr::get($item, 'id') == $order->shipping_option);
        if (! $rate && ! empty($rates)) {
            $rate = Arr::first($rates);
        }

        $content = view('plugins/shipmozo::info', compact('shipment', 'order', 'rate', 'rates'))->render();

        return $response->setData([
            'html' => $content,
        ]);
    }

    public function createTransaction(int $id, BaseHttpResponse $response)
    {
        /**
         * @var Shipment $shipment
         */
        $shipment = Shipment::query()->findOrFail($id);

        $this->check($shipment);

        $message = trans('plugins/shipmozo::shipmozo.transaction.created_success');

        $errors = [];
        $responseData = [];
        $lock = Cache::lock("shipmozo:order:{$shipment->order_id}", 60);

        if (! $lock->get()) {
            return $response->setError()->setMessage(trans('plugins/shipmozo::shipmozo.shipment_processing'));
        }

        try {
            $shipment->refresh();

            if (! $this->shipmozo->canCreateTransaction($shipment)) {
                return $response->setError()->setMessage(trans('plugins/shipmozo::shipmozo.shipment_already_pushed'));
            }

            $order = $shipment->order;
            $existingOrderId = Arr::get($shipment->metadata, 'workflow.push_order.data.order_id');
            $transaction = $this->shipmozo->createShipment(
                $order,
                is_scalar($existingOrderId) ? (string) $existingOrderId : null
            );

            if (Arr::get($transaction, 'result') == 1) {
                $awb = Arr::get($transaction, 'data.awb_number');

                if ($awb) {
                    $labelUrl = $this->shipmozo->getOrderLabelUrl($awb);

                    $shipment->tracking_link = 'https://panel.shipmozo.com/track-order/'.$awb;
                    $shipment->label_url = $labelUrl;
                    $shipment->tracking_id = $awb;
                    $shipment->metadata = $transaction;
                    $shipment->status = ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT;
                    $shipment->save();

                    ShipmentHistory::query()->create([
                        'action' => 'create_transaction',
                        'description' => trans('plugins/shipmozo::shipmozo.pushed_success_with_awb', ['awb' => $awb]),
                        'order_id' => $shipment->order_id,
                        'user_id' => $this->userId,
                        'shipment_id' => $shipment->id,
                    ]);

                    ShipmentHistory::query()->create([
                        'action' => 'update_status',
                        'description' => trans('plugins/ecommerce::shipping.changed_shipping_status', [
                            'status' => ShippingStatusEnum::getLabel(ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT),
                        ]),
                        'order_id' => $shipment->order_id,
                        'user_id' => $this->userId,
                        'shipment_id' => $shipment->id,
                    ]);

                    $message = trans('plugins/shipmozo::shipmozo.transaction.created_success');
                } else {
                    $message = trans('plugins/shipmozo::shipmozo.push_success_no_tracking');
                    $errors[] = $message;
                }
            } else {
                $shipment->metadata = $transaction;
                $shipment->save();

                $message = Arr::get($transaction, 'data.error', Arr::get($transaction, 'message', 'Failed to generate ShipMozo AWB.'));
                $errors[] = $message;
            }
        } catch (Throwable $exception) {
            $this->shipmozo->logError('Manual order push failed', [
                'shipment_id' => $shipment->getKey(),
                'message' => $exception->getMessage(),
            ]);
            $message = trans('plugins/shipmozo::shipmozo.create_shipment_unable');
            $errors[] = $message;
        } finally {
            $lock->release();
        }

        $responseData['errors'] = (array) $errors;

        return $response->setError((bool) count($errors))
            ->setMessage($message)
            ->setData($responseData);
    }

    protected function check(Shipment $shipment): bool
    {
        $order = $shipment->order;

        if (! is_in_admin(true) && is_plugin_active('marketplace')) {
            $vendor = auth('customer')->user();
            abort_unless($vendor && $vendor->store, 403);
            $store = $vendor->store;

            abort_if($store->id != $order->store_id, 403);
        }

        abort_unless($order && $order->getKey(), 404);

        abort_unless($this->shipmozo->isShipmozoOrder($order), 404);

        return true;
    }

    public function viewLog(string $logFile)
    {
        abort_unless(
            preg_match('/\Ashipmozo(?:-\d{4}-\d{2}-\d{2})?\.log\z/', $logFile) === 1,
            404
        );

        $logPath = storage_path('logs/'.basename($logFile));

        abort_unless(File::isFile($logPath), 404);

        return response('<pre>'.e(File::get($logPath)).'</pre>');
    }

    public function createWarehouseFromStore(int|string $storeId, BaseHttpResponse $response)
    {
        abort_unless(
            is_plugin_active('marketplace')
            && Schema::hasTable('mp_stores')
            && Schema::hasColumn('mp_stores', 'warehouse_id'),
            404
        );

        $store = Store::query()->findOrFail($storeId);

        $data = [
            'name' => $store->name,
            'address_title' => $store->name,
            'contact_name' => $store->name,
            'contact_email' => $store->email,
            'contact_phone' => $store->phone,
            'pincode' => $store->zip_code,
            'city' => $store->city_name ?? $store->city,
            'state' => $store->state_name ?? $store->state,
            'address' => $store->address,
            'phone' => $store->phone,
        ];

        try {
            $result = $this->shipmozo->createWarehouse($data);
        } catch (Throwable $exception) {
            $this->shipmozo->logError('Warehouse creation failed', [
                'store_id' => $store->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return $response->setError()->setMessage(trans('plugins/shipmozo::shipmozo.warehouse_create_unable'));
        }

        if (Arr::get($result, 'result') == 1) {
            $warehouseId = Arr::get($result, 'data.warehouse_id') ?: Arr::get($result, 'id');
            if ($warehouseId) {
                $store->update(['warehouse_id' => $warehouseId]);

                return $response
                    ->setNextUrl(route('marketplace.store.edit', $store->id))
                    ->setMessage(trans('plugins/shipmozo::shipmozo.warehouse_created_success'));
            }
        }

        $error = Arr::get($result, 'message') ?: Arr::get($result, 'data.error', trans('plugins/shipmozo::shipmozo.warehouse_created_failed'));

        return $response
            ->setError()
            ->setNextUrl(route('marketplace.store.edit', $store->id))
            ->setMessage($error);
    }

    public function getRates(int $id, BaseHttpResponse $response)
    {
        $shipment = Shipment::query()->findOrFail($id);
        $this->check($shipment);

        try {
            $rates = $this->resolveRatesWithNonShipmozo($this->getRateData($shipment->order));
            $rate = Arr::first($rates, fn (array $item): bool => Arr::get($item, 'id') == $shipment->order->shipping_option);

            if ($rate) {
                $rates = Arr::where($rates, fn (array $item): bool => Arr::get($item, 'id') != Arr::get($rate, 'id'));
            }

            $content = view('plugins/shipmozo::rates', [
                'rates' => $rates,
                'shipment' => $shipment,
                'order' => $shipment->order,
                'rate' => $rate,
            ])->render();

            return $response->setData(['html' => $content, 'errors' => []]);
        } catch (Throwable $exception) {
            $this->shipmozo->logError('Shipment rate refresh failed', [
                'shipment_id' => $shipment->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return $response->setError()->setMessage(trans('plugins/shipmozo::shipmozo.rates_refresh_unable'));
        }
    }

    public function updateRate(int $id, Request $request, BaseHttpResponse $response)
    {
        $validated = $request->validate(['shipping_option' => ['required', 'string', 'max:100']]);
        $shipment = Shipment::query()->findOrFail($id);
        $this->check($shipment);

        try {
            $order = $shipment->order;
            $rates = $this->resolveRatesWithNonShipmozo($this->getRateData($order));
            $rate = Arr::first($rates, fn (array $item): bool => ! Arr::get($item, 'disabled')
                && Arr::get($item, 'id') === $validated['shipping_option']);

            if (! $rate) {
                return $response->setError()->setMessage(trans('plugins/shipmozo::shipmozo.rate_not_available'));
            }

            $oldShippingAmount = (float) $order->shipping_amount;
            $newShippingAmount = (float) Arr::get($rate, 'price', 0);

            $order->shipping_method = SHIPMOZO_SHIPPING_METHOD_NAME;
            $order->shipping_option = Arr::get($rate, 'id');
            $order->shipping_amount = $newShippingAmount;
            $order->amount = max(0, (float) $order->amount - $oldShippingAmount + $newShippingAmount);
            $order->save();

            $shipment->rate_id = Arr::get($rate, 'id');
            $shipment->price = $newShippingAmount;
            $shipment->save();

            $content = view('plugins/shipmozo::info', compact('shipment', 'order', 'rate', 'rates'))->render();

            return $response
                ->setData(['html' => $content])
                ->setMessage(trans('plugins/shipmozo::shipmozo.updated_rate_success'));
        } catch (Throwable $exception) {
            $this->shipmozo->logError('Shipment rate update failed', [
                'shipment_id' => $shipment->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return $response->setError()->setMessage(trans('plugins/shipmozo::shipmozo.rate_update_unable'));
        }
    }

    public function resolveRatesWithNonShipmozo(array $rateData): array
    {
        $rates = Arr::get($this->shipmozo->getRates($rateData), 'shipment.rates', []);
        $nonShipmozoItems = Arr::get($rateData, 'non_shipmozo_items', []);

        if (! empty($nonShipmozoItems)) {
            $hookProvider = new HookServiceProvider(app());
            $directDetails = $hookProvider->resolveNonShipmozoShippingDetails($rateData, $nonShipmozoItems);
            $nonShipmozoFee = (float) Arr::get($directDetails, 'price', 0.0);
            $ruleName = Arr::get($directDetails, 'rule_name');
            $ruleId = Arr::get($directDetails, 'rule_id');

            if ($nonShipmozoFee > 0) {
                foreach ($rates as &$r) {
                    if (is_array($r) && isset($r['price']) && empty($r['disabled'])) {
                        $customerShipmozoFee = (float) $r['price'];
                        $r['customer_shipmozo_price'] = $customerShipmozoFee;
                        $r['direct_shipping_fee'] = $nonShipmozoFee;
                        $r['direct_rule_name'] = $ruleName;
                        $r['direct_rule_id'] = $ruleId;
                        $r['is_mixed'] = true;
                        $r['price'] = $customerShipmozoFee + $nonShipmozoFee;

                        $combinedSuffix = trans('plugins/shipmozo::shipmozo.combined_delivery_suffix');
                        if (! str_contains($r['name'], 'Combined:') && ! str_contains($r['name'], 'संयुक्त:')) {
                            $r['name'] .= $combinedSuffix;
                        }
                    }
                }
            }
        }

        return $rates;
    }

    public function getRateData(Order $order): array
    {
        $address = $order->shippingAddress;
        $isIndividualScope = setting('shipping_shipmozo_scope', 'all') === 'individual';
        $shipmozoItems = [];
        $nonShipmozoItems = [];

        foreach ($order->products as $orderProduct) {
            $product = $orderProduct->product;
            $isEligible = true;
            if ($isIndividualScope) {
                $isEligible = $product && (is_callable([$product, 'isShipmozoShippingEnabled'])
                    ? $product->isShipmozoShippingEnabled()
                    : (bool) $product->enable_shipmozo_shipping);
            }

            $itemData = [
                'id' => $orderProduct->product_id,
                'qty' => $orderProduct->qty,
                'price' => $orderProduct->price,
                'weight' => $orderProduct->weight ?: ($product ? $product->weight : 0),
                'length' => $product?->length ?: 10,
                'wide' => $product?->wide ?: 10,
                'height' => $product?->height ?: 10,
            ];

            if ($isEligible) {
                $shipmozoItems[$orderProduct->product_id] = $itemData;
            } else {
                $nonShipmozoItems[$orderProduct->product_id] = $itemData;
            }
        }

        $items = ! empty($shipmozoItems) ? $shipmozoItems : $nonShipmozoItems;

        $data = [
            'address_to' => [
                'zip_code' => $address ? $address->zip_code : '',
                'city' => $address ? $address->city : '',
                'state' => $address ? $address->state : '',
                'country' => $address ? $address->country : '',
            ],
            'origin' => EcommerceHelper::getOriginAddress(),
            'store_id' => $order->store_id,
            'payment_method' => is_plugin_active('payment')
                ? $order->payment?->payment_channel?->getValue()
                : null,
            'items' => $items,
            'non_shipmozo_items' => $nonShipmozoItems,
        ];

        if (is_plugin_active('marketplace') && $order->store_id && $order->store) {
            $data['origin_warehouse_id'] = $order->store->warehouse_id;
        }

        return $data;
    }
}
