<?php

namespace SparroWave\AdvancedCod\Http\Middleware;

use Botble\Ecommerce\Models\Product;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnrichCodProductSearchMiddleware
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

            if (! empty($items)) {
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
                        $isCod = (bool) ($p?->is_cod_eligible ?? $p?->original_product?->is_cod_eligible ?? false);
                        $item['cod'] = $isCod;
                        $item['is_smart_cod_eligible'] = $isCod;

                        if (! isset($item['flags'])) {
                            $item['flags'] = [];
                        }
                        $item['flags']['cod'] = $isCod;
                        $item['flags']['is_smart_cod_eligible'] = $isCod;

                        if (! empty($item['variations']) && is_array($item['variations'])) {
                            foreach ($item['variations'] as &$v) {
                                if (is_array($v)) {
                                    $v['cod'] = $isCod;
                                    $v['is_smart_cod_eligible'] = $isCod;
                                    if (! isset($v['flags'])) {
                                        $v['flags'] = [];
                                    }
                                    $v['flags']['cod'] = $isCod;
                                    $v['flags']['is_smart_cod_eligible'] = $isCod;
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

