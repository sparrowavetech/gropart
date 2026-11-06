<?php

namespace SparroWave\FarmartHelper\Http\Middleware;

use Botble\Ecommerce\Models\Order;
use Closure;
use Illuminate\Http\Request;

class GuardIncompleteOrderEditMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET') && ($request->routeIs('orders.edit') || ($request->is('admin/ecommerce/orders/edit/*') && ! $request->ajax()))) {
            $orderParam = $request->route('order');
            if (! $orderParam && preg_match('#admin/ecommerce/orders/edit/(\d+)#', $request->path(), $matches)) {
                $orderParam = $matches[1];
            }

            if ($orderParam) {
                $order = $orderParam instanceof Order ? $orderParam : Order::query()->find($orderParam);

                if ($order && ! (bool) $order->is_finished) {
                    return redirect()->route('orders.view-incomplete-order', $order->getKey());
                }
            }
        }

        return $next($request);
    }
}

