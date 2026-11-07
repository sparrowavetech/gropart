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

        $isTargetRoute = $request->routeIs('products.get-all-products-and-variations')
            || (method_exists($request, 'is') && $request->is('*products/get-all-products-and-variations*'));

        if ($request->isMethod('GET') && $isTargetRoute && $response instanceof JsonResponse) {
            $data = $response->getData(true);
            $isWrapped = isset($data['data']['data']) && is_array($data['data']['data']);
            $items = $isWrapped ? $data['data']['data'] : (isset($data['data']) && is_array($data['data']) ? $data['data'] : []);

            if (! empty($items) && ProductFreeShippingHelper::isEnabled()) {
                $productIds = array_filter(array_column($items, 'id'));
                if (! empty($productIds)) {
                    $products = Product::query()
                        ->whereIn('id', $productIds)
                        ->get()
                        ->keyBy('id');

                    foreach ($items as &$item) {
                        if (! is_array($item)) {
                            continue;
                        }
                        $pid = $item['id'] ?? 0;
                        $p = $products->get($pid);
                        $isFree = $p ? (ProductFreeShippingHelper::isProductFreeShipping($p) || ($p->original_product && ProductFreeShippingHelper::isProductFreeShipping($p->original_product))) : false;
                        $item['free_shipping'] = $isFree;

                        if (! isset($item['flags'])) {
                            $item['flags'] = [];
                        }
                        $item['flags']['free_shipping'] = $isFree;

                        if (! empty($item['variations']) && is_array($item['variations'])) {
                            foreach ($item['variations'] as &$v) {
                                if (is_array($v)) {
                                    $v['free_shipping'] = $isFree;
                                    if (! isset($v['flags'])) {
                                        $v['flags'] = [];
                                    }
                                    $v['flags']['free_shipping'] = $isFree;
                                }
                            }
                            unset($v);
                        }
                    }
                    unset($item);

                    if ($isWrapped) {
                        $data['data']['data'] = array_values($items);
                    } else {
                        $data['data'] = array_values($items);
                    }

                    $response->setData($data);
                }
            }
        }

        return $response;
    }
}

