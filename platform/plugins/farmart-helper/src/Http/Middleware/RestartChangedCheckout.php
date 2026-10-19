<?php
namespace SparroWave\FarmartHelper\Http\Middleware;

use Botble\Ecommerce\Models\Order;
use Closure;
use SparroWave\FarmartHelper\Supports\CheckoutOrderHelper;

class RestartChangedCheckout
{
    public function handle($request, Closure $next)
    {
        if ($request->routeIs('public.cart.add-by-url', 'public.cart.add-to-cart', 'public.cart.update', 'public.cart.remove', 'public.cart.destroy')) {
            $token = session('tracked_start_checkout');
            if ($token && Order::where('token', $token)->get()->contains(fn ($order) => app(CheckoutOrderHelper::class)->isOrderLocked($order))) {
                // Keep the cart. A changed basket needs a new payment intent, not changes to the old one.
                $data = session(md5('checkout_address_information_' . $token), []);
                $newToken = md5(\Illuminate\Support\Str::random(40));
                session()->put('tracked_start_checkout', $newToken);
                session()->put(md5('checkout_address_information_' . $newToken), array_intersect_key($data, array_flip([
                    'name','email','phone','address','country','state','city','zip_code','address_id',
                ])));
                session()->forget('order_id');
                do_action('ecommerce_checkout_restarted', $token, $newToken);
            }
        }
        return $next($request);
    }
}
