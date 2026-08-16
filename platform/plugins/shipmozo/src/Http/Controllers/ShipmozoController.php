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

        $content = view('plugins/shipmozo::info', compact('shipment', 'order'))->render();

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
            return $response->setError()->setMessage('This ShipMozo shipment is already being processed.');
        }

        try {
            $shipment->refresh();

            if (! $this->shipmozo->canCreateTransaction($shipment)) {
                return $response->setError()->setMessage('This shipment has already been pushed to ShipMozo.');
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
                        'description' => 'Successfully pushed order to ShipMozo. Tracking ID: '.$awb,
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
                    $message = 'Order pushed successfully, but no tracking ID was returned.';
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
            $message = 'Unable to create the ShipMozo shipment at this time.';
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

            return $response->setError()->setMessage('Unable to create the ShipMozo warehouse at this time.');
        }

        if (Arr::get($result, 'result') == 1) {
            $warehouseId = Arr::get($result, 'data.warehouse_id') ?: Arr::get($result, 'id');
            if ($warehouseId) {
                $store->update(['warehouse_id' => $warehouseId]);

                return $response
                    ->setNextUrl(route('marketplace.store.edit', $store->id))
                    ->setMessage('Warehouse created and linked successfully in ShipMozo.');
            }
        }

        $error = Arr::get($result, 'message') ?: Arr::get($result, 'data.error', 'Failed to create warehouse in ShipMozo.');

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
            $rates = Arr::get($this->shipmozo->getRates($this->getRateData($shipment->order)), 'shipment.rates', []);
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

            return $response->setError()->setMessage('Unable to refresh ShipMozo rates.');
        }
    }

    public function updateRate(int $id, Request $request, BaseHttpResponse $response)
    {
        $validated = $request->validate(['shipping_option' => ['required', 'string', 'max:100']]);
        $shipment = Shipment::query()->findOrFail($id);
        $this->check($shipment);

        try {
            $order = $shipment->order;
            $rates = Arr::get($this->shipmozo->getRates($this->getRateData($order)), 'shipment.rates', []);
            $rate = Arr::first($rates, fn (array $item): bool => ! Arr::get($item, 'disabled')
                && Arr::get($item, 'id') === $validated['shipping_option']);

            if (! $rate) {
                return $response->setError()->setMessage('The selected ShipMozo rate is no longer available.');
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

            return $response
                ->setData(['html' => view('plugins/shipmozo::info', compact('shipment', 'order'))->render()])
                ->setMessage(trans('plugins/shipmozo::shipmozo.updated_rate_success'));
        } catch (Throwable $exception) {
            $this->shipmozo->logError('Shipment rate update failed', [
                'shipment_id' => $shipment->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return $response->setError()->setMessage('Unable to update the ShipMozo rate.');
        }
    }

    private function getRateData(Order $order): array
    {
        $address = $order->shippingAddress;
        $data = [
            'address_to' => ['zip_code' => $address->zip_code],
            'origin' => EcommerceHelper::getOriginAddress(),
            'store_id' => $order->store_id,
            'payment_method' => is_plugin_active('payment')
                ? $order->payment?->payment_channel?->getValue()
                : null,
            'items' => $order->products->map(function ($orderProduct): array {
                $product = $orderProduct->product;

                return [
                    'qty' => $orderProduct->qty,
                    'price' => $orderProduct->price,
                    'weight' => $orderProduct->weight ?: $product?->weight,
                    'length' => $product?->length ?: 10,
                    'wide' => $product?->wide ?: 10,
                    'height' => $product?->height ?: 10,
                ];
            })->all(),
        ];

        if (is_plugin_active('marketplace') && $order->store_id && $order->store) {
            $data['origin_warehouse_id'] = $order->store->warehouse_id;
        }

        return $data;
    }
}
