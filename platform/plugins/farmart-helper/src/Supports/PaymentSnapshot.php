<?php
namespace SparroWave\FarmartHelper\Supports;

use Botble\Ecommerce\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymentSnapshot
{
    public const KEY = 'farmart_payment_snapshot';
    public const FIELDS = ['amount','sub_total','tax_amount','shipping_amount','shipping_tax_amount','payment_fee','discount_amount','shipping_method','shipping_option','coupon_code'];

    public function read(Order $order): ?array
    {
        $value = $order->getOrderMetadata(self::KEY);
        $snapshot = $value ? json_decode($value, true) : null;
        // Legacy snapshots were created while merely rendering payment options.
        if ($snapshot && ($snapshot['version'] ?? 1) < 2 && !\Botble\Payment\Models\Payment::where('order_id', $order->id)->where('status', 'completed')->exists()) return null;
        return $snapshot;
    }

    public function token(array $data = []): ?string
    {
        return $data['notes']['order_token'] ?? request()->route('token')
            ?? (request()->hasSession() ? session('tracked_start_checkout') : null);
    }

    public function synchronized(string $token, callable $callback): mixed
    {
        // Connection-scoped MySQL lock has no lease that can expire during gateway I/O.
        $key = 'fh-pay-' . sha1($token);
        $db = DB::connection();
        if ((int) ($db->selectOne('SELECT GET_LOCK(?, 30) AS acquired', [$key])->acquired ?? 0) !== 1) {
            Log::warning('checkout_integrity.lock_timeout', ['token_hash'=>sha1($token)]);
            abort(503, 'Payment processing is busy. Please retry.');
        }
        try { return $callback(); }
        finally { $db->selectOne('SELECT RELEASE_LOCK(?) AS released', [$key]); }
    }

    public function before(string $method, array $request): void
    {
        if ($method !== 'razorpay' || !isset($request['receipt'], $request['amount'])) return;
        $token = $this->token($request);
        $orders = Order::where('token', $token)->get();
        if (!$token || $orders->isEmpty()) throw new RuntimeException('Please refresh checkout before starting payment.');
        foreach ($orders as $order) {
            if ($order->products()->where('product_type', 'physical')->exists() && (!$order->address->name || !$order->address->zip_code || !$order->shipping_option)) {
                throw new RuntimeException('Complete delivery details and select shipping before starting payment.');
            }
        }
        $expected = (float) apply_filters('ecommerce_expected_online_amount', (float)$orders->sum('amount'), $orders);
        if (abs(round($expected * 100) - $request['amount']) > 1) {
            Log::warning('checkout_integrity.creation_amount_rejected', ['order_ids'=>$orders->modelKeys(), 'requested_paise'=>$request['amount'], 'expected_paise'=>(int)round($expected*100)]);
            throw new RuntimeException('Checkout total changed. Please refresh checkout before paying.');
        }
    }

    public function after(string $method, array $request, array $response): void
    {
        if ($method !== 'razorpay' || !isset($request['receipt'], $response['id'], $response['amount'])) return;
        $token = $this->token($request);
        if (!$token) return;
        foreach (Order::where('token', $token)->get() as $order) {
            if (!$this->read($order)) {
                $fields = array_intersect_key($order->getAttributes(), array_flip(self::FIELDS));
                $order->setOrderMetadata(self::KEY, json_encode(['version'=>2,'checkout_method'=>request()->attributes->get('checkout_payment_method', request()->input('payment_method', $method)), 'gateway_order'=>$response['id'],'payable_paise'=>$response['amount'],'fields'=>$fields]));
            }
            app(CheckoutOrderHelper::class)->captureFootprints($order);
            Log::info('checkout_integrity.payment_created', ['order_id'=>$order->id,'gateway_order'=>$response['id'],'amount'=>$order->amount,'shipping'=>$order->shipping_amount,'payable_paise'=>$response['amount']]);
        }
        if (request()->hasSession()) session()->save();
    }

    public function protect(Order $order): void
    {
        if (!$snapshot = $this->read($order)) return;
        $changed = array_intersect(array_keys($order->getDirty()), self::FIELDS);
        if (!$changed) return;
        foreach ($snapshot['fields'] as $key=>$value) $order->setAttribute($key,$value);
        Log::warning('checkout_integrity.locked_totals_preserved', ['order_id'=>$order->id,'attempted_fields'=>array_values($changed)]);
    }
}
