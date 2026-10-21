<?php
namespace SparroWave\AdvancedCod\Supports;

use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Payment\Models\Payment;
use Botble\Payment\Supports\PaymentHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AdvancePayment
{
    public const KEY = 'advanced_cod_intent';

    public static function intent(Order $order): ?array
    {
        $value = $order->getOrderMetadata(self::KEY);
        return $value ? json_decode($value, true) : null;
    }

    public static function finalizeFees(array $ids): void
    {
        DB::transaction(function () use ($ids) {
        $orders = Order::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        foreach ($orders as $index => $order) {
            if (self::intent($order)) continue;
            if (apply_filters('ecommerce_payment_attempt_locked', false, $order)) {
                throw new RuntimeException('This checkout already has an online payment attempt. Recover the order to start a fresh COD checkout.');
            }
            $base = round((float)$order->amount - (float)$order->payment_fee, 2);
            $fee = \Botble\Payment\Supports\PaymentFeeHelper::calculateFee('cod', $base);
            if ($index > 0) $fee -= \Botble\Payment\Supports\PaymentFeeHelper::calculateFee('cod', 0);
            $order->payment_fee = round(max(0, $fee), 2);
            $order->amount = round($base + $order->payment_fee, 2);
            $order->save();
        }
        });
    }

    public static function prepare(array $ids, string $gateway): void
    {
        $orders = Order::whereIn('id', $ids)->orderBy('id')->get();
        if ($orders->isEmpty() || $orders->count() !== count(array_unique($ids))) throw new RuntimeException('Invalid COD order group.');
        $percentage = CodAmounts::getPrepaymentPercentage();
        $parts = CodAmounts::allocate($orders->pluck('amount', 'id')->all(), $percentage);
        foreach ($orders as $order) {
            $old = self::intent($order);
            if ($old && ($old['gateway'] !== $gateway || (int) round($order->amount * 100) !== (int) round($old['total'] * 100))) {
                throw new RuntimeException('This payment attempt already has a fixed total. Return to the cart to start a new checkout.');
            }
            $part = $parts[$order->id];
            if ($old) $part = $old;
            $order->cod_prepayment_amount = $part['advance'];
            $order->cod_remaining_amount = $part['remaining'];
            $order->save();
            $order->setOrderMetadata(self::KEY, json_encode($old ?: ($part + [
                'gateway' => $gateway, 'percentage' => $percentage,
                'currency' => get_application_currency()->title, 'order_ids' => $orders->modelKeys(),
                'fields' => array_intersect_key($order->getAttributes(), array_flip(['amount','sub_total','tax_amount','shipping_amount','shipping_tax_amount','payment_fee','discount_amount','shipping_method','shipping_option','coupon_code'])),
            ])));
        }
    }


    public static function mayFinalize(Order $order): bool
    {
        $intent = self::intent($order);
        if (!$intent) return true;
        if (!empty($intent['admin_full_cod'])) return (bool)$order->getOrderMetadata(ManualConfirmation::KEY);
        $manual=$order->getOrderMetadata('advanced_cod_manual_receipt');
        if ($manual && Payment::whereKey($manual)->where('order_id',$order->id)->where('payment_channel',ManualConfirmation::CHANNEL)->where('status','completed')->where('currency',$intent['currency'])->where('amount',$intent['advance'])->where(fn($q)=>$q->where('refunded_amount',0)->orWhereNull('refunded_amount'))->exists()) return true;
        return Payment::where('order_id',$order->id)->where('status','completed')
            ->where('payment_channel',$intent['gateway'])->where('currency',$intent['currency'])
            ->where('amount',$intent['advance'])->where(fn($q)=>$q->where('refunded_amount',0)->orWhereNull('refunded_amount'))->exists();
    }

    public static function protect(Order $order): void
    {
        $intent = self::intent($order);
        if (!$intent) return;
        if ($order->isDirty('is_finished') && $order->is_finished && !self::mayFinalize($order)) {
            throw new RuntimeException('COD advance must be verified before placing this order.');
        }
        foreach ($intent['fields'] ?? [] as $field => $value) {
            if ($order->isDirty($field)) $order->setAttribute($field,$value);
        }
    }

    public static function protectItem($item): void
    {
        $order = Order::find($item->order_id);
        if (!$order || !self::intent($order)) return;
        if ($item->exists && !$item->isDirty(['price','qty','product_id','tax_amount','options'])) return;
        throw new RuntimeException('An advance payment has started; return to the cart to change the items.');
    }

    /** Returns true only when the verified gateway event belongs to this plugin. */
    public static function handle(array $data): bool
    {
        $rawIds = $data['order_id'] ?? [];
        if (is_string($rawIds)) {
            $rawIds = explode(',', $rawIds);
        }
        $ids = array_values(array_filter(array_unique(array_map('intval', (array) $rawIds))));
        $orders = Order::whereIn('id', $ids)->orderBy('id')->get();
        if ($orders->isEmpty() || !$orders->contains(fn ($o) => self::intent($o))) return false;
        if ($orders->count() !== count($ids) || $orders->contains(fn ($o) => !self::intent($o))) {
            throw new RuntimeException('Inconsistent COD payment order group.');
        }
        $key = 'advanced-cod-'.sha1(implode(',', $orders->modelKeys()));
        if ((int) DB::selectOne('SELECT GET_LOCK(?, 30) AS ok', [$key])->ok !== 1) throw new RuntimeException('COD payment processing is busy.');
        try {
            return DB::transaction(function () use ($orders, $ids, $data) {
            $expected = 0;
            foreach ($orders as $order) {
                $intent = self::intent($order);
                $group = array_map('strval', $intent['order_ids']); sort($group); $actual = array_map('strval', $ids); sort($actual);
                if ($group !== $actual || $intent['gateway'] !== ($data['payment_channel'] ?? '') || $intent['currency'] !== ($data['currency'] ?? '') || abs($order->amount - $intent['total']) > .001) {
                    throw new RuntimeException('COD payment does not match its checkout.');
                }
                $expected += (int) round($intent['advance'] * 100);
            }
            $completed = (string) ($data['status'] ?? '') === 'completed';
            if ($completed && ((int) round(($data['amount'] ?? 0) * 100) !== $expected || empty($data['charge_id']))) {
                Log::warning('advanced_cod.payment_mismatch', ['order_ids'=>$ids,'expected_paise'=>$expected,'received_paise'=>(int)round(($data['amount']??0)*100)]);
                throw new RuntimeException('COD advance payment amount does not match.');
            }
            if (empty($data['charge_id'])) return true;
            foreach ($orders as $order) {
                $intent = self::intent($order);
                $existing = Payment::where('order_id',$order->id)->where('charge_id',$data['charge_id'])->first();
                // A repeated capture must not erase an existing refund/reconciliation state.
                if ($existing && in_array((string)$existing->status,['refunding','refunded'])) continue;
                // Late failures must never downgrade a captured payment.
                if ($existing && (string)$existing->status === 'completed' && !$completed) continue;
                $paid = Payment::where('order_id',$order->id)->where('status','completed')->where('charge_id','!=',$data['charge_id'])->exists();
                if ($paid && $completed) {
                    Log::warning('advanced_cod.additional_payment_review', ['order_id'=>$order->id,'charge_id'=>$data['charge_id']]);
                    throw new RuntimeException('An additional captured payment requires reconciliation before this order can be processed.');
                }
                PaymentHelper::storeLocalPayment(array_merge($data, ['order_id'=>$order->id,'amount'=>$intent['advance'],'payment_fee'=>0]));
                if ($completed) {
                    $order->cod_prepayment_amount = $intent['advance'];
                    $order->cod_remaining_amount = round($intent['total']-$intent['advance'],2);
                    $order->save();
                }
            }
            if ($completed) {
                $unfinished = Order::whereIn('id', $ids)->where('is_finished', false)->pluck('id')->all();
                if ($unfinished) OrderHelper::processOrder($unfinished, $data['charge_id']);
                foreach (Order::whereIn('id',$ids)->get() as $settledOrder) Settlement::sync($settledOrder);
                Log::info('advanced_cod.advance_received', ['order_ids'=>$ids,'received_paise'=>$expected,'charge_id'=>$data['charge_id']]);
            }
            return true;
            });
        } finally { DB::select('SELECT RELEASE_LOCK(?)', [$key]); }
    }
}
