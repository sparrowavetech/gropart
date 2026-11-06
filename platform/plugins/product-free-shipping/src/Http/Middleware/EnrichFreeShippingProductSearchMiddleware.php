<?php

namespace SparroWave\ProductFreeShipping\Http\Middleware;

use Botble\Ecommerce\Models\Product;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SparroWave\ProductFreeShipping\Supports\ProductFreeShippingHelper;
use Symfony\Component\HttpFoundation\Response;

class EnrichFreeShippingProductSearchMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && $request->routeIs('products.get-all-products-and-variations') && $response instanceof JsonResponse) {
            $data = $response->getData(true);
            $items = $data['data'] ?? [];

            if (! empty($items) && is_array($items) && ProductFreeShippingHelper::isEnabled()) {
                $productIds = array_column($items, 'id');
                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->get()
                    ->keyBy('id');

                foreach ($items as &$item) {
                    $pid = $item['id'] ?? 0;
                    $p = $products->get($pid);
                    $isFree = $p ? ProductFreeShippingHelper::isProductFreeShipping($p) : false;
                    $item['free_shipping'] = $isFree;
                    // Also support nested flags object for backward compatibility
                    if (! isset($item['flags'])) {
                        $item['flags'] = [];
                    }
                    $item['flags']['free_shipping'] = $isFree;
                }
                unset($item);

                $data['data'] = $items;
                $response->setData($data);
            }
        }

        return $response;
    }
}

