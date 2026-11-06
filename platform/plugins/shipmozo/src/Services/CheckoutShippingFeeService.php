<?php

namespace SparroWave\Shipmozo\Services;

use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Illuminate\Support\Arr;
use SparroWave\Shipmozo\Supports\ShippingBreakdown;

// Keep the upstream calculation/cache methods; only exclude disabled quotes from free-rate detection.
class CheckoutShippingFeeService extends HandleShippingFeeService
{
    public function execute(array $data, ?string $method = null, ?string $option = null): array
    {
        // Enrich items missing product ID or store ID (e.g. from Botble Admin Order Create)
        if (! empty($data['items']) && is_array($data['items'])) {
            $firstStoreId = 0;
            foreach ($data['items'] as $key => $item) {
                if (empty($item['id']) && ! empty($item['name'])) {
                    $product = \Botble\Ecommerce\Models\Product::query()->where('name', $item['name'])->first();
                    if ($product) {
                        $data['items'][$key]['id'] = $product->id;
                        $data['items'][$key]['product_id'] = $product->id;
                        $storeId = $product->original_product->store_id ?? 0;
                        $data['items'][$key]['store_id'] = $storeId;
                        if (! $firstStoreId && $storeId) {
                            $firstStoreId = $storeId;
                        }
                    }
                } elseif (! empty($item['id'])) {
                    $productId = $item['id'];
                    $product = \Botble\Ecommerce\Models\Product::query()->find($productId);
                    if ($product) {
                        $storeId = $product->original_product->store_id ?? 0;
                        $data['items'][$key]['product_id'] = $productId;
                        $data['items'][$key]['store_id'] = $storeId;
                        if (! $firstStoreId && $storeId) {
                            $firstStoreId = $storeId;
                        }
                    }
                }
            }

            if ($firstStoreId) {
                if (empty($data['extra'])) {
                    $data['extra'] = [];
                }
                if (empty($data['extra']['store_id'])) {
                    $data['extra']['store_id'] = $firstStoreId;
                }
                if (empty($data['store_id'])) {
                    $data['store_id'] = $firstStoreId;
                }
            }
        }

        $result = [];

        $cacheKey = $this->getCacheKey($data);
        $cacheValue = $this->getCacheValue($cacheKey);
        if ($cacheValue) {
            $result[ShippingMethodEnum::DEFAULT] = $cacheValue;
        } else {
            $default = $this->getShippingFee($data, ShippingMethodEnum::DEFAULT, $option);

            if ($default) {
                $result[ShippingMethodEnum::DEFAULT] = $default;
            }

            $this->setCacheValue($cacheKey, $default);
        }

        $result = apply_filters('handle_shipping_fee', $result, $data, $option);

        if ($method) {
            $options = Arr::get($result, $method, []);

            if (! is_array($options) || ! count($options)) {
                return [];
            }

            $filtered = Arr::where($options, function ($rate) {
                return ! Arr::get($rate, 'disabled');
            });

            $response = Arr::get($filtered, $option);

            if ($method === SHIPMOZO_SHIPPING_METHOD_NAME && is_array($response)) {
                ShippingBreakdown::remember($response, $data);
            }

            return $response ? [$response] : [];
        }

        if (get_ecommerce_setting('hide_other_shipping_options_if_it_has_free_shipping', false)) {
            $hasFreeShipping = false;

            foreach ($result as $item) {
                foreach ($item as $option) {
                    if (empty($option['disabled']) && (float) $option['price'] == 0) {
                        $hasFreeShipping = true;

                        break;
                    }
                }
            }

            if ($hasFreeShipping) {
                foreach ($result as $itemKey => $item) {
                    foreach ($item as $optionKey => $option) {
                        if ((float) $option['price'] > 0) {
                            Arr::forget($result, $itemKey . '.' . $optionKey);
                        }
                    }
                }
            }
        }

        return $result;
    }

}
