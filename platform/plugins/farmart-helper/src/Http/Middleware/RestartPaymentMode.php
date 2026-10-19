<?php
namespace SparroWave\FarmartHelper\Http\Middleware;

use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Facades\OrderHelper;
use SparroWave\FarmartHelper\Supports\PaymentSnapshot;

/** A submitted payment cannot be reused for a different checkout method/amount. */
class RestartPaymentMode
{
    public function handle($request, \Closure $next)
    {
        if (!$request->routeIs('public.checkout.information', 'public.checkout.process')) return $next($request);
        $request->attributes->set('checkout_payment_method', $request->input('payment_method', session('selected_payment_method')));
        $token = $request->route('token');
        if (!$token || session('tracked_start_checkout') !== $token) return $next($request);
        $orders = Order::where('token', $token)->get();
        if ($orders->isEmpty() || $orders->contains(fn ($o) => $o->is_finished)) return $next($request);
        if (\Botble\Payment\Models\Payment::whereIn('order_id', $orders->modelKeys())->whereIn('status', ['completed', 'refunding', 'refunded'])->exists()) return $next($request);
        $snapshots = $orders->map(fn ($o) => app(PaymentSnapshot::class)->read($o))->filter();
        if ($snapshots->isEmpty()) return $next($request);
        $expected = (int) round(apply_filters('ecommerce_expected_online_amount', (float)$orders->sum('amount'), $orders) * 100);
        $method = $request->attributes->get('checkout_payment_method');
        $restart = $snapshots->contains(fn ($s) => (int)$s['payable_paise'] !== $expected || ($method && $method !== ($s['checkout_method'] ?? 'razorpay')));
        if (!$restart) return $next($request);
        $old = OrderHelper::getOrderSessionData($token);
        $fresh = md5(\Illuminate\Support\Str::random(40));
        // Keep customer/address, not vendor order IDs, prices, or gateway state.
        OrderHelper::setOrderSessionData($fresh, array_intersect_key($old, array_flip(['name','email','phone','address','country','state','city','zip_code','address_id'])));
        session(['tracked_start_checkout' => $fresh, 'selected_payment_method' => $method]);
        session()->forget('order_id');
        do_action('ecommerce_checkout_restarted', $token, $fresh);
        \Illuminate\Support\Facades\Log::info('checkout_integrity.payment_mode_restarted', ['order_ids'=>$orders->modelKeys(),'method'=>$method]);
        return redirect()->route('public.checkout.information', $fresh)->with('error_msg', __('Please review the refreshed checkout total before paying. Your previous payment attempt has not been changed.'));
    }
}
