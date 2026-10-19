<?php
namespace SparroWave\FarmartHelper\Supports;

use Botble\Ecommerce\Models\Order;
use Botble\LoyaltyPoints\Models\OrderLoyaltyPoints;
use RuntimeException;

class FinalizeCheckoutDiscount
{
    public function handle($products, $request, $token, $sessionData): void
    {
        if (!is_plugin_active('loyalty-points') || !auth('customer')->check()) return;
        $points = (int)session('applied_loyalty_points',0);
        $discount = round((float)session('loyalty_points_discount',0),2);
        if ($points <= 0 || $discount <= 0) return;
        $orders = Order::whereIn('id',(array)$request->input('order_id',[]))->orderBy('id')->get();
        $shares=[];
        foreach($orders as $order) {
            if ($marker = $order->getOrderMetadata('farmart_loyalty_finalized')) {
                $shares[$order->id] = (float)$marker;
            } else {
                $before=$order->sub_total+$order->tax_amount+$order->shipping_amount+$order->shipping_tax_amount+$order->payment_fee-$order->discount_amount;
                $shares[$order->id]=round($before-$order->amount,2);
            }
        }
        if (abs(array_sum($shares)-$discount)>.011 || count(array_filter($shares,fn($v)=>$v<0))) {
            throw new RuntimeException('Loyalty discount does not match the checkout total. Please retry checkout.');
        }
        $remaining=$points;$last=$orders->last()?->id;
        foreach($orders as $order) {
            $share=$shares[$order->id];
            $allocation=$order->id===$last?$remaining:(int)floor($points*$share/$discount);
            $remaining-=$allocation;
            if (!$order->getOrderMetadata('farmart_loyalty_finalized')) {
                $order->discount_amount += $share;
                $order->save();
                $order->setOrderMetadata('farmart_loyalty_finalized',(string)$share);
            }
            OrderLoyaltyPoints::updateOrCreate(['order_id'=>$order->id],[
                'customer_id'=>$order->user_id,'points_redeemed'=>$allocation,'discount_amount'=>$share,
            ]);
        }
    }
}
