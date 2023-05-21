<?php

namespace Botble\Pickrr;

use EcommerceHelper;
use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Marketplace\Models\Store;
use Botble\Support\Services\Cache\Cache;
use Botble\Payment\Enums\PaymentStatusEnum;
use Auth;

class Pickrr
{
    /**
     * @var string
     */
    protected $liveApiToken;

    /**
     * @var Cache
     */
    protected $cache;

    public function __construct()
    {
        $this->liveApiToken = setting('shipping_pickrr_auth_token');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Pickrr';
    }

    /**
     * @param array $inParams
     * @return array
     */
    public function getPrepareParams($inParams): array
    {

        //if (!is_plugin_active('marketplace') && EcommerceHelper::isZipCodeEnabled()) {

        if (!is_plugin_active('marketplace')) {
            $params = array(
                'auth_token' => $this->liveApiToken,
                'item_name' => $inParams->product_name,
                'from_name' =>  get_ecommerce_setting('store_name'),
                'from_phone_number' => get_ecommerce_setting('store_phone'),
                'from_address' => get_ecommerce_setting('store_address'),
                'from_pincode' => EcommerceHelper::isZipCodeEnabled() ? get_ecommerce_setting('store_zip_code') : '313001',
                'pickup_gstin' => '',
                'to_name' => $inParams->order->address->zip_code,
                'to_phone_number' => $inParams->order->address->phone,
                'to_pincode' => $inParams->order->address->zip_code,
                'to_address' => $inParams->order->fullAddress,
                'quantity' => $inParams->qty,
                'invoice_value' => $inParams->price + $inParams->tax_amount,
                'cod_amount' => $inParams->order->payment->status != PaymentStatusEnum::COMPLETED ? $inParams->order->amount  : 0,
                'client_order_id' => $inParams->order->id . '/' . $inParams->id,
                'item_breadth' => $inParams->product->wide,
                'item_length' => $inParams->product->length,
                'item_height' => $inParams->product->height,
                'item_weight' => $inParams->product->weight,
                'item_tax_percentage' => $inParams->product->total_taxes_percentage,
                'is_reverse' => False
            );
        } else {
            $store = Store::find($inParams->order->store_id);
            
            $params = array(
                'auth_token' => $this->liveApiToken,
                'item_name' => $inParams->product_name,
                'from_name' =>  $store->name,
                'from_phone_number' => $store->phone,
                'from_address' => $store->full_address,
                'from_pincode' => $store->zip_code,
                'pickup_gstin' => '',
                'to_name' => $inParams->order->address->zip_code,
                'to_phone_number' => $inParams->order->address->phone,
                'to_pincode' => $inParams->order->address->zip_code,
                'to_address' => $inParams->order->fullAddress,
                'quantity' => $inParams->qty,
                'invoice_value' => $inParams->price + $inParams->tax_amount,
                'cod_amount' => $inParams->order->payment->status != PaymentStatusEnum::COMPLETED ? $inParams->order->amount : 0,
                'client_order_id' => $inParams->order->id . '/' . $inParams->id,
                'item_breadth' => $inParams->product->wide,
                'item_length' => $inParams->product->length,
                'item_height' => $inParams->product->height,
                'item_weight' => $inParams->product->weight,
                'item_tax_percentage' => $inParams->product->total_taxes_percentage,
                'is_reverse' => False
            );
        }

        return $params;
    }


    public function createShipment($orderProduct)
    {
        try {
            $json_params = json_encode($this->getPrepareParams($orderProduct));
            $url = 'https://www.pickrr.com/api/place-order/';
            //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //execute post
            $result = curl_exec($ch);
            $response = json_decode($result);
            //close connection
            curl_close($ch);
            if ($response->success == true) {
                $shipment['order_id']    = $orderProduct->order_id;
                $shipment['user_id']    = Auth::id();
                $shipment['weight']    = $orderProduct->product->weight;
                $shipment['shipment_id']    = $response->order_id;
                $shipment['cod_amount'] = $orderProduct->order->payment->status != PaymentStatusEnum::COMPLETED ? $orderProduct->order->amount : 0;
                $shipment['cod_status'] = $orderProduct->order->payment->status != PaymentStatusEnum::COMPLETED ? 'complete' : 'pending';
                $shipment['type'] = 'Pickrr';
                $shipment['status'] = ShippingStatusEnum::DELIVERING;
                $shipment['price']    = $orderProduct->order->shipping_amount;
                $shipment['store_id']    = $orderProduct->order->store_id;
                $shipment['tracking_id']    = $response->tracking_id;
                $shipment['shipping_company_name'] = $response->courier;
                $shipment['tracking_link']    = 'https://www.pickrr.com/tracking/#/?tracking_id=' . $response->tracking_id;
                $shipment['label_url'] =  'https://pickrr.com/order/generate-user-order-manifest-png/' . $this->liveApiToken . '/' . $response->order_id;
                $shipment['metadata']     = $result;
                return ['error' => false, 'message' => 'success', 'shipment' => $shipment];
            } else {
                return ['error' => true, 'message' => $response->err];
            }
        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }
    public function cancelShipment($shipment)
    {
        try {
            $json_params =  json_encode(array(
                'auth_token' => $this->liveApiToken,
                'tracking_id' => $shipment->tracking_id
            ));
            $url = 'https://www.pickrr.com/api/order-cancellation/';
            //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //execute post
            $result = curl_exec($ch);
            $response = json_decode($result);
            //close connection
            curl_close($ch);
            if ($response->success == true) {

                return ['error' => false, 'message' => 'success'];
            } else {
                return ['error' => true, 'message' => $response->err];
            }
        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }
    public function checkpincode($to,$from)
    {
        try {
            $url = 'https://www.pickrr.com/api/check-pincode-service/?from_pincode='.$from.'&to_pincode='.$to.'&auth_token='.$this->liveApiToken;
            //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //execute post
            $result = curl_exec($ch);
            return $response = json_decode($result)->has_cod;
            //close connection

        } catch (\Exception $e) {
            return false;
        }
    }
}
