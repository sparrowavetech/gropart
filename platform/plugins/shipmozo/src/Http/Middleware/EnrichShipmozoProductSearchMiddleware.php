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
                        $isShipmozo = false;
                        if ($p) {
                            try {
                                if (method_exists($p, 'hasMacro') && $p::hasMacro('isShipmozoShippingEnabled')) {
                                    $isShipmozo = (bool) $p->isShipmozoShippingEnabled();
                                } elseif (\Botble\Base\Supports\MacroableModels::modelHasMacro(Product::class, 'isShipmozoShippingEnabled')) {
                                    $isShipmozo = (bool) $p->isShipmozoShippingEnabled();
                                } else {
                                    $isShipmozo = (bool) ($p->enable_shipmozo_shipping ?? false);
                                }
                            } catch (\Throwable $e) {
                                $isShipmozo = (bool) ($p->enable_shipmozo_shipping ?? false);
                            }
                        }
                        $item['shipmozo'] = $isShipmozo;
                        if (! isset($item['flags'])) {
                            $item['flags'] = [];
                        }
                        $item['flags']['shipmozo'] = $isShipmozo;

                        if (! empty($item['variations']) && is_array($item['variations'])) {
                            foreach ($item['variations'] as &$v) {
                                if (is_array($v)) {
                                    $v['shipmozo'] = $isShipmozo;
                                    if (! isset($v['flags'])) {
                                        $v['flags'] = [];
                                    }
                                    $v['flags']['shipmozo'] = $isShipmozo;
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

