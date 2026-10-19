<?php
namespace SparroWave\AdvancedCod\Supports;

use Botble\Ecommerce\Models\{Order,Invoice,OrderHistory};
use Botble\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SparroWave\AdvancedCod\Models\SettlementEntry;

class Settlement
{
    public static function applies(?Order $order): bool
    {
        return $order && ((float)$order->cod_prepayment_amount > 0 || !empty(AdvancePayment::intent($order)['admin_full_cod']));
    }

    public static function paise($amount): int
    {
        if (!is_numeric($amount) || !is_finite((float)$amount)) self::fail('Invalid amount.');
        return (int) round((float)$amount * 100);
    }

    public static function summary(Order $order): array
    {
        $intent = AdvancePayment::intent($order);
        $currency = $intent['currency'] ?? $order->payment?->currency ?: get_application_currency()->title;
        $payments = Payment::where('order_id',$order->id)->whereIn('status',['completed','refunding','refunded'])->get();
        $online = 0; $manual = 0; $refund = 0; $review = false;
        foreach ($payments as $payment) {
            if ((string)$payment->payment_channel === 'cod') { $review = true; continue; }
            if ($payment->currency !== $currency) { $review = true; continue; }
            $refunded = (string)$payment->status === 'refunded' ? self::paise($payment->amount) : self::paise($payment->refunded_amount ?? 0);
            $review = $review || (string)$payment->status !== 'completed';
            $refund += $refunded;
            if ((string)$payment->payment_channel === ManualConfirmation::CHANNEL) $manual += max(0,self::paise($payment->amount)-$refunded);
            else $online += max(0,self::paise($payment->amount)-$refunded);
        }
        $entries = SettlementEntry::where('order_id',$order->id)->orderBy('id')->get();
        $reversed = $entries->where('kind','reversal')->pluck('reversal_of')->all();
        $active = $entries->reject(fn ($e) => $e->kind === 'reversal' || in_array($e->id,$reversed));
        $courier = $active->where('kind','collection')->where('source','courier')->sum('amount_paise');
        $direct = $active->where('kind','collection')->where('source','direct')->sum('amount_paise');
        $cashRefund = $active->where('kind','refund')->sum('amount_paise');
        $remitted = $active->where('kind','remittance')->sum('amount_paise');
        $deductions = $active->where('kind','remittance')->sum('fee_paise');
        $total = self::paise($order->amount);
        $required = self::paise($order->cod_prepayment_amount);
        $advance_received = $online + $manual;
        $received = $advance_received + $courier + $direct - $cashRefund;
        $outstanding = max(0,$total-$received);
        $review = $review || $received > $total || $received < 0 || $refund > 0 || $cashRefund > 0;
        $cancelled = (string)$order->status === 'canceled';
        $label = $cancelled ? 'Cancelled — reconciliation required' : ($review ? 'Refund / payment review required' : ($outstanding === 0 ? 'Fully paid' : ($advance_received < $required ? 'Advance pending' : ($received===0?'COD pending — unpaid':'Partially paid — COD pending'))));
        return compact('currency','total','required','online','manual','advance_received','courier','direct','cashRefund','remitted','deductions','received','outstanding','review','cancelled','label','entries','reversed') + [
            'fee'=>self::paise($order->payment_fee ?? 0),
            'planned_cod'=>max(0,$total-$required),
            'cod_pending'=>$advance_received >= $required ? $outstanding : null,
            'remittance_pending'=>max(0,$courier-$remitted-$deductions),
        ];
    }

    public static function record(Order $order, array $data, int $actor): SettlementEntry
    {
        return DB::transaction(function () use ($order,$data,$actor) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (!self::applies($order)) self::fail('This is not an Advanced COD order.');
            $kind = $data['kind'];
            $source = $kind === 'remittance' ? 'courier' : ($kind === 'refund' ? 'direct' : ($data['source'] ?? 'direct'));
            $amount = self::paise($data['amount'] ?? 0);
            $fee = self::paise($data['fee'] ?? 0);
            $existing = SettlementEntry::where('idempotency_key',$data['idempotency_key'])->first();
            if ($existing) {
                if ($existing->order_id != $order->id || $existing->kind !== $kind || $existing->reference !== $data['reference']
                    || ($kind !== 'reversal' && ($existing->amount_paise !== $amount || $existing->fee_paise !== $fee || $existing->source !== $source))
                    || ($kind === 'reversal' && $existing->reversal_of != ($data['reversal_of'] ?? null))) self::fail('This submission was already used for different settlement data.');
                return $existing;
            }
            if (SettlementEntry::where('order_id',$order->id)->where('kind',$kind)->where('reference',$data['reference'])->exists()) self::fail('This reference has already been recorded.');
            $s = self::summary($order);
            $reversal = null;
            if ($kind === 'reversal') {
                $original = SettlementEntry::where('order_id',$order->id)->whereKey($data['reversal_of'] ?? 0)->first();
                if (!$original || $original->kind === 'reversal' || in_array($original->id,$s['reversed'])) self::fail('Entry cannot be reversed.');
                if ($original->kind === 'collection') {
                    $otherCash = $s['courier']+$s['direct']+$s['manual']-$original->amount_paise;
                    if ($otherCash < $s['cashRefund'] || ($original->source === 'courier' && $s['courier']-$original->amount_paise < $s['remitted']+$s['deductions'])) self::fail('Reverse dependent refunds/remittances first.');
                }
                if ($original->kind === 'refund' && $s['received']+$original->amount_paise > $s['total']) self::fail('Reversal would overstate customer receipts.');
                $amount=$original->amount_paise; $fee=$original->fee_paise; $source=$original->source; $reversal=$original->id;
            } elseif ($kind === 'collection') {
                if (!in_array($source,['courier','direct']) || $fee !== 0 || !$order->is_finished || $s['cancelled'] || $s['review'] || $s['advance_received'] < $s['required']) self::fail('Collect COD only after a verified advance on a placed order with no reconciliation issues.');
                if ($amount <= 0 || $amount > $s['outstanding']) self::fail('Collection exceeds the customer balance.');
            } elseif ($kind === 'remittance') {
                $source='courier';
                if ($amount < 0 || $fee < 0 || $amount+$fee <= 0 || $amount+$fee > $s['remittance_pending']) self::fail('Net remittance plus deductions exceeds courier cash pending.');
            } elseif ($kind === 'refund') {
                $source='direct';
                if ($amount <= 0 || $fee !== 0 || $amount > $s['courier']+$s['direct']+$s['manual']-$s['cashRefund']) self::fail('Refund exceeds recorded cash/manual receipts. Refund online advances through the gateway.');
            } else self::fail('Unknown settlement operation.');
            $entry = SettlementEntry::create([
                'order_id'=>$order->id,'kind'=>$kind,'source'=>$source,'amount_paise'=>$amount,'fee_paise'=>$fee,
                'currency'=>$s['currency'],'reference'=>$data['reference'],'idempotency_key'=>$data['idempotency_key'],
                'reversal_of'=>$reversal,'user_id'=>$actor,'note'=>$data['note'],'occurred_at'=>$data['occurred_at'],
            ]);
            OrderHistory::create(['order_id'=>$order->id,'user_id'=>$actor,'action'=>'advanced_cod_settlement',
                'description'=>'Advanced COD '.$kind.': '.number_format($amount/100,2,'.','').' '.$s['currency'].'; reference '.$data['reference'].'; entry #'.$entry->id]);
            self::sync($order);
            return $entry;
        });
    }

    public static function sync(Order $order): void
    {
        if (!self::applies($order)) return;
        $s=self::summary($order);
        // Keep the planned balance while advance is unpaid. Display actual outstanding separately.
        if ($s['advance_received'] >= $s['required'] || $s['review'] || $s['entries']->isNotEmpty()) {
            $order->cod_remaining_amount=$s['outstanding']/100;
            $order->saveQuietly();
        }
        foreach (\Botble\Ecommerce\Models\Shipment::where('order_id',$order->id)->get() as $shipment) {
            $shipment->cod_amount=($s['cod_pending']??$s['planned_cod'])/100;
            $shipment->cod_status=$s['outstanding']===0 && !$s['review'] && !$s['cancelled']?'completed':'pending';
            $shipment->saveQuietly();
        }
        foreach (Invoice::where('reference_type',Order::class)->where('reference_id',$order->id)->get() as $invoice) {
            self::invoiceState($invoice,$s);
            $invoice->saveQuietly();
            \Illuminate\Support\Facades\Storage::disk('local')->delete(sprintf('ecommerce/invoices/invoice-%s.pdf',$invoice->code));
        }
    }

    public static function invoiceState(Invoice $invoice, ?array $s=null): void
    {
        if ($invoice->reference_type !== Order::class || !self::applies($order=Order::find($invoice->reference_id))) return;
        $s ??= self::summary($order);
        if ((string)$invoice->status === 'canceled' || $s['cancelled']) { $invoice->status='canceled'; $invoice->paid_at=null; return; }
        $full=$s['outstanding']===0 && !$s['review'];
        $invoice->status=$full?'completed':($s['received']>0?'processing':'pending');
        $invoice->paid_at=$full?($invoice->paid_at ?: now()):null;
    }

    private static function fail(string $message): never
    {
        throw ValidationException::withMessages(['settlement'=>$message]);
    }
}
