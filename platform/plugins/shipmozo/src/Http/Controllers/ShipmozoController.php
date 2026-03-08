<?php

namespace SparroWave\Shipmozo\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use Botble\Payment\Enums\PaymentMethodEnum;
use SparroWave\Shipmozo\Shipmozo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Throwable;
use Botble\Ecommerce\Facades\EcommerceHelper;

class ShipmozoController extends BaseController
{
    protected string|int|null $userId = 0;

    public function __construct(protected Shipmozo $shipmozo)
    {
        if (is_in_admin(true) && Auth::check()) {
            $this->userId = Auth::id();
        }
    }

    public function checkPincode(Request $request, BaseHttpResponse $response)
    {
        $deliveryPincode = $request->input('pincode');

        if (!$deliveryPincode) {
            return $response->setError(true)->setMessage('Please enter a valid pincode.');
        }

        $origin = EcommerceHelper::getOriginAddress();
        $pickupPincode = Arr::get($origin, 'zip_code', '');

        try {
            $apiResponse = $this->shipmozo->checkPincodeServiceability($pickupPincode, $deliveryPincode);

            if (Arr::get($apiResponse, 'result') === 1) {
                return $response->setMessage('Delivery is available for this pincode!');
            }

            return $response->setError(true)->setMessage(Arr::get($apiResponse, 'message', 'Delivery is not available for this pincode.'));
        } catch (Throwable $e) {
            return $response->setError(true)->setMessage('Unable to verify pincode at this time.');
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

        abort_unless($this->shipmozo->canCreateTransaction($shipment), 404);

        $message = trans('plugins/shipmozo::shipmozo.transaction.created_success');

        $errors = [];
        $responseData = [];

        try {
            $order = $shipment->order;
            $transaction = $this->shipmozo->pushOrder($order);

            if (Arr::get($transaction, 'result') == 1) {
                // Determine AWB from top level or data.order_id as fallback
                $awb = Arr::get($transaction, 'awb_number') ?: Arr::get($transaction, 'data.order_id');

                if ($awb) {
                    $labelUrl = $this->shipmozo->getOrderLabel($awb);

                    $shipment->tracking_link = 'https://panel.shipmozo.com/track-order/' . $awb;
                    $shipment->label_url = $labelUrl;
                    $shipment->tracking_id = $awb;
                    $shipment->metadata = json_encode($transaction);
                    $shipment->status = ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT;
                    $shipment->save();

                    ShipmentHistory::query()->create([
                        'action' => 'create_transaction',
                        'description' => 'Successfully pushed order to ShipMozo. Tracking ID: ' . $awb,
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
                $message = Arr::get($transaction, 'data.error', Arr::get($transaction, 'message', 'Failed to generate ShipMozo AWB.'));
                $errors[] = $message;
            }
        } catch (Exception $ex) {
            $errors[] = $ex->getMessage();
            $message = $ex->getMessage();
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
            $store = $vendor->store;

            abort_if($store->id != $order->store_id, 403);
        }

        $method = $order->shipping_method->getValue();
        $isShipmozo = ($method === SHIPMOZO_SHIPPING_METHOD_NAME) ||
            ($method === \Botble\Ecommerce\Enums\ShippingMethodEnum::DEFAULT && is_numeric($order->shipping_option));

        abort_if(! $order || ! $order->id || !$isShipmozo, 404);

        // Data Healing: If it was saved as 'default', standardize it to 'shipmozo' now
        if ($method === \Botble\Ecommerce\Enums\ShippingMethodEnum::DEFAULT) {
            $order->update(['shipping_method' => SHIPMOZO_SHIPPING_METHOD_NAME]);
        }

        return true;
    }

    public function viewLog(string $logFile)
    {
        $logPath = storage_path('logs/' . $logFile);

        abort_unless(File::exists($logPath), 404);

        return nl2br(File::get($logPath));
    }

    public function createWarehouseFromStore(int|string $storeId, BaseHttpResponse $response)
    {
        $store = \Botble\Marketplace\Models\Store::query()->findOrFail($storeId);

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

        $result = $this->shipmozo->createWarehouse($data);

        if (Arr::get($result, 'result') == 1) {
            $warehouseId = Arr::get($result, 'id') ?: Arr::get($result, 'data.id');
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
}
