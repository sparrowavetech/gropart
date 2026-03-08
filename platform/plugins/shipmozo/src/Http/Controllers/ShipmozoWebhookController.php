<?php

namespace SparroWave\Shipmozo\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\ShipmentHistory;
use SparroWave\Shipmozo\Shipmozo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ShipmozoWebhookController extends BaseController
{
    public function __construct(protected Shipmozo $shipmozo) {}

    public function index(Request $request, BaseHttpResponse $response)
    {
        $data = $request->all();

        $this->shipmozo->log('Received Webhook', $data);

        $awbNumber = Arr::get($data, 'awb_number', Arr::get($data, 'awb'));
        $status = Str::upper(Arr::get($data, 'status', Arr::get($data, 'current_status', '')));

        if (! $awbNumber || ! $status) {
            return $response;
        }

        /**
         * @var Shipment $shipment
         */
        $shipment = Shipment::query()->where('tracking_id', $awbNumber)->first();

        if (! $shipment) {
            $this->shipmozo->log('Webhook Error: Shipment not found for AWB', ['awb' => $awbNumber]);
            return $response;
        }

        $this->updateShipmentStatus($shipment, $status);

        return $response;
    }

    protected function updateShipmentStatus(Shipment $shipment, string $status)
    {
        $botbleStatus = null;

        if (Str::contains($status, ['DELIVERED', 'COMPLETED', 'SUCCESS'])) {
            $botbleStatus = ShippingStatusEnum::DELIVERED;
            $shipment->date_shipped = Carbon::now();
        } elseif (Str::contains($status, ['RETURN', 'RTO', 'CANCELLED', 'FAILED'])) {
            $botbleStatus = ShippingStatusEnum::CANCELED;
        } elseif (Str::contains($status, ['IN TRANSIT', 'SHIPPED', 'DISPATCHED', 'OUT FOR DELIVERY', 'PICKED UP'])) {
            $botbleStatus = ShippingStatusEnum::DELIVERING;
        }

        if ($botbleStatus && $shipment->status != $botbleStatus) {
            $shipment->status = $botbleStatus;
            $shipment->save();

            if ($botbleStatus === ShippingStatusEnum::DELIVERED) {
                OrderHelper::shippingStatusDelivered($shipment, request());
            }
        }

        ShipmentHistory::query()->create([
            'action' => 'track_updated',
            'description' => "ShipMozo tracking updated to: {$status}",
            'order_id' => $shipment->order_id,
            'user_id' => 0,
            'shipment_id' => $shipment->id,
        ]);
    }
}
