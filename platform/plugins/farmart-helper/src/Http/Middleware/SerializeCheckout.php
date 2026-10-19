<?php
namespace SparroWave\FarmartHelper\Http\Middleware;

use Closure;
use SparroWave\FarmartHelper\Supports\PaymentSnapshot;

class SerializeCheckout
{
    public function handle($request, Closure $next)
    {
        $token = $request->route('token') ?? $request->input('payload.payment.entity.notes.order_token');
        if (!$token && $request->routeIs('payments.razorpay.webhook')) {
            $charge = $request->input('payload.payment.entity.id');
            if ($charge) {
                $id = \Botble\Payment\Models\Payment::where('charge_id', $charge)->value('order_id');
                $token = $id ? \Botble\Ecommerce\Models\Order::whereKey($id)->value('token') : null;
            }
        }
        if (!$token || !is_string($token)) return $next($request);
        return app(PaymentSnapshot::class)->synchronized($token, fn () => $next($request));
    }
}
