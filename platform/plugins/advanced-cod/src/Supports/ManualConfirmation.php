<?php
namespace SparroWave\AdvancedCod\Supports;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentFeeHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualConfirmation
{
    public const CHANNEL='advanced_cod_manual';
    public const KEY='advanced_cod_admin_confirmation';

    public static function available(Order $order): bool
    {
        if ($order->is_finished || (string) $order->status === 'canceled') return false;
        return (bool) AdvancePayment::intent($order)
            || Payment::where('order_id', $order->id)->where('payment_channel', 'cod')->exists();
    }

    public static function preview(Order $order): array
    {
        $intent=AdvancePayment::intent($order);
        $flags=OrderEligibility::read($order);
        $base=round((float)$order->amount-(float)$order->payment_fee,2);
        $fee=$intent?(float)$order->payment_fee:round(PaymentFeeHelper::calculateFee('cod',$base),2);
        $total=$intent?(float)$order->amount:round($base+$fee,2);
        $advance=$intent?(float)$intent['advance']:CodAmounts::split($total,(float)get_payment_setting('prepayment_percentage','cod',30))['advance'];
        $locked=(bool) apply_filters('ecommerce_payment_attempt_locked', (bool)$order->getOrderMetadata('advanced_cod_gateway_attempt'), $order) || Payment::where('order_id',$order->id)->whereIn('status',['pending','completed','refunding','refunded'])->exists();
        $retry_required=(bool) apply_filters('ecommerce_payment_attempt_inconsistent', false, $order);
        $fingerprint=hash('sha256',json_encode([$order->getRawOriginal(),$flags,$fee,$total,$advance]));
        return compact('fee','total','advance','locked','fingerprint','flags','retry_required')+['remaining'=>round($total-$advance,2),'frozen'=>(bool)$intent];
    }

    public static function confirm(Order $order,array $data,$actor): Order
    {
        abort_unless($actor->hasPermission('orders.edit') && $actor->hasPermission('advanced-cod.manual-confirm'),403);
        $lock='cod-admin-'.sha1($data['mode']==='manual' ? strtoupper(trim($data['reference']??'')) : (string)$order->id);
        if((int)DB::selectOne('SELECT GET_LOCK(?, 15) AS ok',[$lock])->ok!==1)self::fail('Confirmation is busy. Please retry.');
        try { return DB::transaction(function()use($order,$data,$actor){
            $order=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $previous=json_decode($order->getOrderMetadata(self::KEY) ?: 'null',true);
            if($previous && ($previous['idempotency_key']??null)===$data['idempotency_key']){
                foreach(['mode','reference','amount','method','note','occurred_at'] as $field){if((string)($previous[$field]??'')!==(string)($data[$field]??''))self::fail('Submission already used for different confirmation details.');}
                return $order;
            }
            if($order->is_finished || (string)$order->status==='canceled')self::fail('This order is already placed or cancelled.');
            if (!self::available($order)) self::fail('Manual Advanced COD is only available for incomplete COD attempts.');
            $p=self::preview($order);
            if($p['retry_required'])self::fail('The previous payment attempts have inconsistent totals. Recover a fresh checkout or reconcile the existing payments first.');
            if(!hash_equals($p['fingerprint'],$data['fingerprint']))self::fail('Order/settings changed. Reload and review the updated calculation.');
            if(!$p['flags']['cod'] || get_payment_setting('status','cod')!=1)self::fail('COD is not enabled for every product or the payment method is disabled.');
            $mode=$data['mode'];
            if($mode==='verified'){
                if(!AdvancePayment::intent($order) || !AdvancePayment::mayFinalize($order))self::fail('No matching verified advance receipt exists for this order. A typed transaction ID is not proof of payment.');
                $s=Settlement::summary($order);
                if($s['review'] || $s['advance_received']!==$s['required'])self::fail('Receipts require reconciliation before placing this order.');
                $intent=AdvancePayment::intent($order);
                $payment=Payment::where('order_id',$order->id)->where('status','completed')->where('amount',$intent['advance'])->where('currency',$intent['currency'])
                    ->where(fn($q)=>$q->where('refunded_amount',0)->orWhereNull('refunded_amount'))
                    ->where(fn($q)=>$q->where('payment_channel',$intent['gateway'])->orWhere('id',(int)$order->getOrderMetadata('advanced_cod_manual_receipt')))->firstOrFail();
            }else{
                if($p['locked'])self::fail('A payment attempt/receipt is already locked. Reconcile it before recording manual money or changing payment mode.');
                if($mode==='manual'){
                    if(!$actor->hasPermission('advanced-cod.manual-receipt'))abort(403);
                    if(Payment::where('charge_id','manual-cod-'.hash('sha256',strtoupper(trim($data['reference']))))->exists())self::fail('This receipt reference has already been used.');
                    if(Settlement::paise($data['amount'])!==Settlement::paise($p['advance']))self::fail('Manual receipt must equal the required advance shown in the preview.');
                    if(!in_array($data['method'],['cash','bank','upi'],true))self::fail('Choose the actual receipt method.');
                }elseif($mode==='full_cod'){
                    if(!$actor->hasPermission('advanced-cod.override') || !get_payment_setting('admin_full_cod_override','cod',0))abort(403);
                    if($p['frozen'])self::fail('An existing advance intent cannot be converted to full COD.');
                }else self::fail('Invalid confirmation operation.');
                if(!$p['frozen']){
                    $order->amount=$p['total'];$order->payment_fee=$p['fee'];$order->save();
                }
                if($mode==='manual'){
                    $gateway=AdvancePayment::intent($order)['gateway']??self::CHANNEL;
                    AdvancePayment::prepare([$order->id],$gateway);
                    $order->refresh();
                    // Preserve gateway evidence: manual receipts use their own payment channel.
                    $payment=Payment::create(['order_id'=>$order->id,'amount'=>$p['advance'],'currency'=>AdvancePayment::intent($order)['currency'],
                        'payment_channel'=>self::CHANNEL,'charge_id'=>'manual-cod-'.hash('sha256',strtoupper(trim($data['reference']))),'status'=>'completed','payment_fee'=>0,'user_id'=>$order->user_id ?? 0]);
                    $order->setOrderMetadata('advanced_cod_manual_receipt',(string)$payment->id);
                }else{
                    $order->cod_prepayment_amount=0;$order->cod_remaining_amount=$p['total'];$order->save();
                    $order->setOrderMetadata(AdvancePayment::KEY,json_encode(['admin_full_cod'=>true,'gateway'=>'cod','currency'=>get_application_currency()->title,
                        'total'=>$p['total'],'advance'=>0,'remaining'=>$p['total'],'percentage'=>0,'order_ids'=>[$order->id],
                        'fields'=>array_intersect_key($order->getAttributes(),array_flip(['amount','sub_total','tax_amount','shipping_amount','shipping_tax_amount','payment_fee','discount_amount','shipping_method','shipping_option','coupon_code']))]));
                    $payment=Payment::create(['order_id'=>$order->id,'amount'=>$p['total'],'currency'=>get_application_currency()->title,
                        'payment_channel'=>'cod','charge_id'=>'manual-cod-'.$data['idempotency_key'],'status'=>'pending','payment_fee'=>0,'user_id'=>$order->user_id ?? 0]);
                }
            }
            $record=$data+['actor_id'=>$actor->id,'payment_id'=>$payment->id];
            $order->setOrderMetadata(self::KEY,json_encode($record));
            OrderHelper::processOrder([$order->id],$payment->charge_id);
            $order->refresh();
            if(!$order->is_finished)self::fail('Order could not be placed. No manual receipt was saved.');
            $order->histories()->create(['user_id'=>$actor->id,'action'=>'advanced_cod_admin_confirmation',
                'description'=>'Advanced COD '.$mode.'; received '.($mode==='full_cod'?'0':$payment->amount).' '.$payment->currency.'; method '.($data['method']??$payment->payment_channel).'; reference '.($data['reference']??$payment->charge_id).'; occurred '.($data['occurred_at']??now()).'; note '.$data['note']]);
            Settlement::sync($order);
            return $order;
        },3); } finally {DB::select('SELECT RELEASE_LOCK(?)',[$lock]);}
    }
    private static function fail(string $message): never {throw ValidationException::withMessages(['confirmation'=>$message]);}
}
