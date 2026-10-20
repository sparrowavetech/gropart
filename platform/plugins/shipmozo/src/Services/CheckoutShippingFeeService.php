<?php

namespace SparroWave\Shipmozo\Services;

use Botble\Ecommerce\Enums\ShippingMethodEnum;
use Botble\Ecommerce\Services\HandleShippingFeeService;
use Illuminate\Support\Arr;

// Keep the upstream calculation/cache methods; only exclude disabled quotes from free-rate detection.
class CheckoutShippingFeeService extends HandleShippingFeeService
{
    public function execute(array $data, ?string $method = null, ?string $option = null): array
    {
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
