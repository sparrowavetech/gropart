<?php

namespace SparroWave\FarmartHelper\Http\Middleware;

use Botble\Ecommerce\Models\Order;
use Botble\Payment\Supports\PaymentHelper;
use Closure;
use Illuminate\Http\Request;
use SparroWave\FarmartHelper\Supports\CheckoutPaymentPolicy;

class GuardCheckoutSuccess
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('public.checkout.success')) {
            $token = (string) $request->route('token');
            $policy = app(CheckoutPaymentPolicy::class);
            $orders = Order::query()->where('token', $token)->where('is_finished', false)->get();

            foreach ($orders as $order) {
                if ($policy->blocksFinalization($order)) {
                    // Run before controller analytics and marketplace's direct finalizer.
                    return redirect()->to(PaymentHelper::getCancelURL($token));
                }
            }
        }

        return $next($request);
    }
}
