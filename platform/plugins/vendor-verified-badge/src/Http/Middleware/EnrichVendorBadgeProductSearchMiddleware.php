<?php

namespace SparroWave\VendorVerifiedBadge\Http\Middleware;

use Botble\Ecommerce\Models\Product;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper;
use Symfony\Component\HttpFoundation\Response;

class EnrichVendorBadgeProductSearchMiddleware
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

            if (! empty($items) && is_plugin_active('marketplace')) {
                $productIds = array_filter(array_column($items, 'id'));
                if (! empty($productIds)) {
                    $products = Product::query()
                        ->whereIn('id', $productIds)
                        ->with(['store'])
                        ->get()
                        ->keyBy('id');

                    foreach ($items as &$item) {
                        if (! is_array($item)) {
                            continue;
                        }
                        $pid = $item['id'] ?? 0;
                        $p = $products->get($pid);
                        $store = ($p?->store && $p->store->exists) ? $p->store : (($p?->original_product?->store && $p->original_product->store->exists) ? $p->original_product->store : null);

                        $storeName = $store?->name;
                        $isVerified = (bool) ($store?->is_verified ?? false);
                        $shopType = null;
                        if ($store) {
                            $shopTypeEnum = VendorBadgeHelper::getShopType($store);
                            $shopType = $shopTypeEnum ? $shopTypeEnum->label() : ($store->shop_category ? ucfirst($store->shop_category) : null);
                        }

                        $item['store_name'] = $storeName;
                        $item['store_type'] = $shopType;
                        $item['is_verified'] = $isVerified;

                        if (! isset($item['flags'])) {
                            $item['flags'] = [];
                        }
                        $item['flags']['store_name'] = $storeName;
                        $item['flags']['store_type'] = $shopType;
                        $item['flags']['is_verified'] = $isVerified;

                        if (! empty($item['variations']) && is_array($item['variations'])) {
                            foreach ($item['variations'] as &$v) {
                                if (is_array($v)) {
                                    $v['store_name'] = $storeName;
                                    $v['store_type'] = $shopType;
                                    $v['is_verified'] = $isVerified;
                                    if (! isset($v['flags'])) {
                                        $v['flags'] = [];
                                    }
                                    $v['flags']['store_name'] = $storeName;
                                    $v['flags']['store_type'] = $shopType;
                                    $v['flags']['is_verified'] = $isVerified;
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

