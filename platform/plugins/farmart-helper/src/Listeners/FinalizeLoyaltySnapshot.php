<?php
namespace SparroWave\FarmartHelper\Listeners;

use Botble\LoyaltyPoints\Listeners\SaveOrderLoyaltyPoints;
use Botble\LoyaltyPoints\Models\{OrderLoyaltyPoints,PointTransaction};
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Models\Invoice;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinalizeLoyaltySnapshot extends SaveOrderLoyaltyPoints
{
    public function handle(OrderPlacedEvent $event): void
    {
        $order = $event->order;
        $marker = $order->getOrderMetadata('farmart_loyalty_finalized');
        if (!$marker) { parent::handle($event); return; }
        if (!$this->loyaltyHelper->isEnabled()) return;
        $pending = OrderLoyaltyPoints::where('order_id',$order->id)->first();
        if (!$pending || !$order->user_id) { parent::handle($event); return; }
        DB::transaction(function () use ($order,$pending) {
            // Lock the order so retrying a gateway event cannot redeem twice.
            DB::table('ec_orders')->where('id',$order->id)->lockForUpdate()->first();
            if (PointTransaction::where('order_id',$order->id)->where('type',PointTransaction::TYPE_REDEEM)->exists()) return;
            if ($pending->points_redeemed > 0 && !$this->loyaltyPointService->redeemPoints($order->user_id,(int)$pending->points_redeemed,$order->id,$order)) {
                throw new RuntimeException('Loyalty points could not be redeemed; order requires review.');
            }
            $pending->points_to_earn = $this->loyaltyHelper->calculatePointsFromAmount($order->amount + $pending->discount_amount);
            $pending->save();
            Invoice::where('reference_id',$order->id)->where('reference_type',get_class($order))->update([
                'amount'=>$order->amount,'discount_amount'=>$order->discount_amount,
            ]);
        });
        session()->forget(['loyalty_points_to_redeem', 'applied_loyalty_points', 'loyalty_points_discount', 'loyalty_guest_member_id', 'loyalty_guest_customer_id', 'loyalty_guest_customer']);
    }
}
