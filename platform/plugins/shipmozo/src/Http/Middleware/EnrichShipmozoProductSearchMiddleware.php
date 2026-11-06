<?php

namespace SparroWave\Shipmozo\Http\Middleware;

use Botble\Ecommerce\Models\Product;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnrichShipmozoProductSearchMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && $request->routeIs('products.get-all-products-and-variations') && $response instanceof JsonResponse) {
            $data = $response->getData(true);
            $items = $data['data'] ?? [];

            if (! empty($items) && is_array($items)) {
                $productIds = array_column($items, 'id');
                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->get()
                    ->keyBy('id');

                foreach ($items as &$item) {
                    $pid = $item['id'] ?? 0;
                    $p = $products->get($pid);
                    $isShipmozo = false;
                    if ($p) {
                        $isShipmozo = is_callable([$p, 'isShipmozoShippingEnabled'])
                            ? (bool) $p->isShipmozoShippingEnabled()
                            : (bool) ($p->enable_shipmozo_shipping ?? false);
                    }
                    $item['shipmozo'] = $isShipmozo;
                    if (! isset($item['flags'])) {
                        $item['flags'] = [];
                    }
                    $item['flags']['shipmozo'] = $isShipmozo;
                }
                unset($item);

                $data['data'] = $items;
                $response->setData($data);
            }
        }

        return $response;
    }
}

