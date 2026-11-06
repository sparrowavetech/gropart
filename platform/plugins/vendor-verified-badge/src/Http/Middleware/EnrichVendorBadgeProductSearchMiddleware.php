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

        if ($request->isMethod('GET') && $request->routeIs('products.get-all-products-and-variations') && $response instanceof JsonResponse) {
            $data = $response->getData(true);
            $items = $data['data'] ?? [];

            if (! empty($items) && is_array($items) && is_plugin_active('marketplace')) {
                $productIds = array_column($items, 'id');
                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->with(['store'])
                    ->get()
                    ->keyBy('id');

                foreach ($items as &$item) {
                    $pid = $item['id'] ?? 0;
                    $p = $products->get($pid);
                    $store = $p?->original_product?->store ?? $p?->store ?? null;

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
                }
                unset($item);

                $data['data'] = $items;
                $response->setData($data);
            }
        }

        return $response;
    }
}

