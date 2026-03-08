<?php

namespace SparroWave\Shipmozo\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Facades\EcommerceHelper;
use SparroWave\Shipmozo\Shipmozo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;

class ShipmozoPublicController extends BaseController
{
    public function __construct(protected Shipmozo $shipmozo) {}

    public function tracking(Request $request, BaseHttpResponse $response)
    {
        $code = $request->input('order_code');

        if (!$code) {
            return $response->setError()->setMessage('No order code provided.');
        }

        $order = Order::query()
            ->where('code', $code)
            ->with(['shipment'])
            ->first();

        if (!$order || !$order->shipment || !$order->shipment->tracking_id) {
            return $response->setError()->setMessage('Tracking not found.');
        }

        $awb = $order->shipment->tracking_id;
        $trackingData = $this->shipmozo->trackOrder($awb);

        if (empty($trackingData)) {
            return $response->setError()->setMessage('No tracking update available.');
        }

        $html = view('plugins/shipmozo::tracking-timeline', compact('trackingData'))->render();

        return $response->setData($html);
    }

    public function checkPincode(Request $request, BaseHttpResponse $response)
    {
        $deliveryPincode = $request->input('pincode');

        if (!$deliveryPincode) {
            return $response->setError(true)->setMessage('Please enter a valid pincode.');
        }

        $origin = EcommerceHelper::getOriginAddress();
        $pickupPincode = Arr::get($origin, 'zip_code', '');

        $warehouseId = null;

        if ($request->has('product_id')) {
            $product = \Botble\Ecommerce\Models\Product::find($request->input('product_id'));
            if ($product && is_plugin_active('marketplace')) {
                $store = $product->store;
                if ($store && $store->id) {
                    $warehouseId = $store->warehouse_id;
                    if ($store->zip_code) {
                        $pickupPincode = $store->zip_code;
                    }
                }
            }
        }

        try {
            $mockData = [
                'address_to' => ['zip' => $deliveryPincode],
                'origin' => ['zip_code' => $pickupPincode],
                'origin_warehouse_id' => $warehouseId,
                'items' => [
                    ['qty' => 1, 'weight' => 500, 'price' => 100, 'length' => 10, 'wide' => 10, 'height' => 10]
                ],
                'payment_method' => 'bank_transfer'
            ];

            $ratesResponse = $this->shipmozo->getRates($mockData);

            $rates = \Illuminate\Support\Arr::get($ratesResponse, 'shipment.rates', []);

            if (!empty($rates)) {
                return $response->setMessage('Delivery is available for this pincode!')
                    ->setData(['pickup_pincode' => $pickupPincode, 'delivery_pincode' => $deliveryPincode]);
            }

            return $response->setError(true)->setMessage('Delivery is not available for this pincode.')
                ->setData(['pickup_pincode' => $pickupPincode, 'delivery_pincode' => $deliveryPincode]);
        } catch (Throwable $e) {
            return $response->setError(true)->setMessage('Unable to verify pincode at this time.');
        }
    }
}
