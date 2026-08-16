<?php

namespace SparroWave\Shipmozo\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use SparroWave\Shipmozo\Shipmozo;
use Throwable;

class ShipmozoPublicController extends BaseController
{
    public function __construct(protected Shipmozo $shipmozo) {}

    public function label(Request $request, string $awbNumber)
    {
        $signature = (string) $request->query('signature');
        abort_unless($signature !== '' && hash_equals($this->shipmozo->labelSignature($awbNumber), $signature), 403);

        try {
            $label = $this->shipmozo->getOrderLabel($awbNumber);
        } catch (Throwable $exception) {
            $this->shipmozo->logError('Label request failed', ['message' => $exception->getMessage()]);
            abort(502, 'Unable to load the ShipMozo label.');
        }

        abort_unless($label, 404);

        return response($label['contents'], 200, [
            'Content-Type' => $label['mime_type'],
            'Content-Disposition' => 'inline; filename="shipmozo-label.png"',
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function tracking(Request $request, BaseHttpResponse $response)
    {
        $customerOrderId = $request->input('customer_order_id');
        $query = Order::query()->with('shipment');

        if ($customerOrderId) {
            $customer = auth('customer')->user();
            abort_unless($customer, 401);
            $query->whereKey($customerOrderId)->where('user_id', $customer->getKey());
        } else {
            $validated = $request->validate([
                'order_code' => ['required', 'string', 'max:60'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
            ]);

            $code = ltrim($validated['order_code'], '#');
            $query->where(fn (Builder $query): Builder => $query
                ->where('code', $code)
                ->orWhere('code', '#'.$code));

            if (EcommerceHelper::isOrderTrackingUsingPhone()) {
                abort_unless($request->filled('phone'), 422);
                $query->where(fn (Builder $query): Builder => $query
                    ->whereHas('address', fn (Builder $query): Builder => $query->where('phone', $validated['phone']))
                    ->orWhereHas('user', fn (Builder $query): Builder => $query->where('phone', $validated['phone'])));
            } else {
                abort_unless($request->filled('email'), 422);
                $query->where(fn (Builder $query): Builder => $query
                    ->whereHas('address', fn (Builder $query): Builder => $query->where('email', $validated['email']))
                    ->orWhereHas('user', fn (Builder $query): Builder => $query->where('email', $validated['email'])));
            }
        }

        $order = $query->first();

        if (! $order || ! $order->shipment || ! $order->shipment->tracking_id) {
            return $response->setError()->setMessage('Tracking not found.');
        }

        $awb = $order->shipment->tracking_id;

        try {
            $trackingData = $this->shipmozo->trackOrder($awb);
        } catch (Throwable $exception) {
            $this->shipmozo->logError('Public tracking request failed', [
                'order_id' => $order->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return $response->setError()->setMessage('Unable to load tracking at this time.');
        }

        if (empty($trackingData)) {
            return $response->setError()->setMessage('No tracking update available.');
        }

        $html = view('plugins/shipmozo::tracking-timeline', compact('trackingData'))->render();

        return $response->setData($html);
    }

    public function checkPincode(Request $request, BaseHttpResponse $response)
    {
        $validated = $request->validate([
            'pincode' => ['required', 'regex:/^[1-9][0-9]{5}$/'],
            'product_id' => ['nullable', 'integer', 'exists:ec_products,id'],
        ]);
        $deliveryPincode = $validated['pincode'];

        $origin = EcommerceHelper::getOriginAddress();
        $pickupPincode = Arr::get($origin, 'zip_code', '');

        $warehouseId = null;

        $product = null;

        if (! empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
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
                    [
                        'qty' => 1,
                        'weight' => $product?->weight ?: 0.2,
                        'price' => $product?->front_sale_price ?: $product?->price ?: 100,
                        'length' => $product?->length ?: 10,
                        'wide' => $product?->wide ?: 10,
                        'height' => $product?->height ?: 10,
                    ],
                ],
                'payment_method' => 'bank_transfer',
            ];

            $ratesResponse = $this->shipmozo->getRates($mockData);

            $rates = Arr::get($ratesResponse, 'shipment.rates', []);

            $serviceableRate = Arr::first($rates, fn (array $rate): bool => ! Arr::get($rate, 'disabled', false)
                && Arr::get($rate, 'id') !== 'no_service');

            if ($serviceableRate) {
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
