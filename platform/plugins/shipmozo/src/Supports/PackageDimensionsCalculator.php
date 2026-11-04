<?php

namespace SparroWave\Shipmozo\Supports;

use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderReturn;
use Illuminate\Support\Arr;

class PackageDimensionsCalculator
{
    /**
     * Standard courier volumetric divisor in India / logistics industry.
     * Divisor = 5000 cm3/kg
     */
    public const DEFAULT_VOLUMETRIC_DIVISOR = 5000;

    /**
     * Minimum package dimensions (cm) to satisfy courier standards
     */
    public const MIN_DIMENSION = 10;

    /**
     * Minimum weight in grams
     */
    public const MIN_WEIGHT_GRAMS = 200;

    /**
     * Calculate package dimensions, dead weight, volumetric weight, and billable chargeable weight.
     *
     * @param array $items Array of items with 'length', 'wide'/'width', 'height', 'weight', 'qty'
     * @return array{
     *     length: int,
     *     width: int,
     *     height: int,
     *     dead_weight_grams: int,
     *     volumetric_weight_grams: int,
     *     chargeable_weight_grams: int,
     *     total_items_volume_cm3: float,
     *     package_volume_cm3: float,
     *     divisor: int
     * }
     */
    public static function calculate(array $items): array
    {
        $divisor = self::DEFAULT_VOLUMETRIC_DIVISOR;
        if (function_exists('setting')) {
            $configuredDivisor = (int) setting('ecommerce_volumetric_weight_divisor');
            if ($configuredDivisor > 0) {
                $divisor = $configuredDivisor;
            }
        }

        $totalDeadWeightGrams = 0;
        $totalItemsVolume = 0.0;
        $normalizedItems = [];

        foreach ($items as $item) {
            $qty = max(1, (int) Arr::get($item, 'qty', 1));

            // Weight in grams
            $rawWeight = (float) Arr::get($item, 'weight', 0);
            $weightInGrams = self::normalizeWeightInGrams($rawWeight);
            $totalDeadWeightGrams += $weightInGrams * $qty;

            // Dimensions in cm
            $l = (float) Arr::get($item, 'length', self::MIN_DIMENSION);
            $w = (float) (Arr::get($item, 'wide') ?? Arr::get($item, 'width', self::MIN_DIMENSION));
            $h = (float) Arr::get($item, 'height', self::MIN_DIMENSION);

            // Standardize item orientation so: l >= w >= h
            $dims = [max($l, 1.0), max($w, 1.0), max($h, 1.0)];
            rsort($dims, SORT_NUMERIC);
            [$itemL, $itemW, $itemH] = $dims;

            $itemVolume = $itemL * $itemW * $itemH;
            $totalItemsVolume += $itemVolume * $qty;

            $normalizedItems[] = [
                'length' => $itemL,
                'width' => $itemW,
                'height' => $itemH,
                'qty' => $qty,
                'volume' => $itemVolume,
            ];
        }

        if (empty($normalizedItems)) {
            return [
                'length' => self::MIN_DIMENSION,
                'width' => self::MIN_DIMENSION,
                'height' => self::MIN_DIMENSION,
                'dead_weight_grams' => self::MIN_WEIGHT_GRAMS,
                'volumetric_weight_grams' => (int) round((pow(self::MIN_DIMENSION, 3) / $divisor) * 1000),
                'chargeable_weight_grams' => self::MIN_WEIGHT_GRAMS,
                'total_items_volume_cm3' => 0.0,
                'package_volume_cm3' => (float) pow(self::MIN_DIMENSION, 3),
                'divisor' => $divisor,
            ];
        }

        // Single item with qty = 1
        if (count($normalizedItems) === 1 && $normalizedItems[0]['qty'] === 1) {
            $pkgL = (int) max(round($normalizedItems[0]['length']), self::MIN_DIMENSION);
            $pkgW = (int) max(round($normalizedItems[0]['width']), self::MIN_DIMENSION);
            $pkgH = (int) max(round($normalizedItems[0]['height']), self::MIN_DIMENSION);
        } else {
            // Multi-item / multi-quantity cart:
            // 1. Base footprint: box length >= max item length, box width >= max item width
            $maxL = 0.0;
            $maxW = 0.0;
            $sumH = 0.0;

            foreach ($normalizedItems as $it) {
                $maxL = max($maxL, $it['length']);
                $maxW = max($maxW, $it['width']);
                $sumH += $it['height'] * $it['qty'];
            }

            // 2. Volume-based height with 10% packaging buffer for cardboard & dunnage
            $bufferedVolume = $totalItemsVolume * 1.10;
            $baseArea = max($maxL * $maxW, 1.0);
            $volumeBasedHeight = (float) ceil($bufferedVolume / $baseArea);

            // Stacking height vs volume-required height:
            // The box height must be at least the volume-required height, but does not need to exceed sum of heights
            $effectiveHeight = max($sumH, $volumeBasedHeight);

            // Ensure dimensions satisfy minimum courier dimension
            $pkgL = (int) max(round($maxL), self::MIN_DIMENSION);
            $pkgW = (int) max(round($maxW), self::MIN_DIMENSION);
            $pkgH = (int) max(round($effectiveHeight), self::MIN_DIMENSION);

            // Keep Length as the longest side if height grew larger than length
            $finalDims = [$pkgL, $pkgW, $pkgH];
            rsort($finalDims, SORT_NUMERIC);
            [$pkgL, $pkgW, $pkgH] = $finalDims;
        }

        // Calculate package volumetric weight (in grams): (L * W * H / divisor) * 1000
        $packageVolume = (float) ($pkgL * $pkgW * $pkgH);
        $volumetricWeightGrams = (int) round(($packageVolume / $divisor) * 1000);

        $deadWeightGrams = (int) max($totalDeadWeightGrams, self::MIN_WEIGHT_GRAMS);
        $chargeableWeightGrams = max($deadWeightGrams, $volumetricWeightGrams);

        return [
            'length' => $pkgL,
            'width' => $pkgW,
            'height' => $pkgH,
            'dead_weight_grams' => $deadWeightGrams,
            'volumetric_weight_grams' => $volumetricWeightGrams,
            'chargeable_weight_grams' => $chargeableWeightGrams,
            'total_items_volume_cm3' => $totalItemsVolume,
            'package_volume_cm3' => $packageVolume,
            'divisor' => $divisor,
        ];
    }

    /**
     * Calculate for an existing Order model.
     */
    public static function calculateForOrder(Order $order, bool $isIndividualScope = false): array
    {
        $items = [];

        foreach ($order->products as $orderProduct) {
            $product = $orderProduct->product;

            if ($isIndividualScope) {
                if (! $product) {
                    continue;
                }

                $isEligible = is_callable([$product, 'isShipmozoShippingEnabled'])
                    ? $product->isShipmozoShippingEnabled()
                    : (bool) $product->enable_shipmozo_shipping;

                if (! $isEligible) {
                    continue;
                }
            }

            $rawWeight = (float) ($orderProduct->weight ?: ($product ? $product->weight : 0));

            $items[] = [
                'id' => $orderProduct->product_id,
                'name' => $orderProduct->product_name,
                'qty' => $orderProduct->qty,
                'price' => $orderProduct->price,
                'weight' => $rawWeight,
                'length' => (float) ($product?->length ?: self::MIN_DIMENSION),
                'wide' => (float) ($product?->wide ?: self::MIN_DIMENSION),
                'height' => (float) ($product?->height ?: self::MIN_DIMENSION),
            ];
        }

        return self::calculate($items);
    }

    /**
     * Calculate for an existing OrderReturn model.
     */
    public static function calculateForReturn(OrderReturn $orderReturn): array
    {
        $items = [];

        foreach ($orderReturn->items as $returnItem) {
            $product = $returnItem->product;
            $rawWeight = (float) ($product ? $product->weight : 0);

            $items[] = [
                'id' => $returnItem->product_id,
                'name' => $returnItem->product_name,
                'qty' => $returnItem->qty,
                'price' => $returnItem->price,
                'weight' => $rawWeight,
                'length' => (float) ($product?->length ?: self::MIN_DIMENSION),
                'wide' => (float) ($product?->wide ?: self::MIN_DIMENSION),
                'height' => (float) ($product?->height ?: self::MIN_DIMENSION),
            ];
        }

        return self::calculate($items);
    }

    /**
     * Convert raw product weight to grams safely.
     */
    public static function normalizeWeightInGrams(float $rawWeight): int
    {
        $storeWeightUnit = function_exists('ecommerce_weight_unit') ? ecommerce_weight_unit() : 'g';

        if ($storeWeightUnit === 'kg') {
            $weightInGrams = (int) round($rawWeight * 1000);
        } elseif ($storeWeightUnit === 'g') {
            if ($rawWeight > 0 && $rawWeight < 10) {
                // If product weight was saved in kg as a decimal (e.g. 0.15 kg, 1.08 kg)
                $weightInGrams = (int) round($rawWeight * 1000);
            } else {
                $weightInGrams = (int) round($rawWeight);
            }
        } else {
            $weightInGrams = (int) round($rawWeight < 10 ? ($rawWeight * 1000) : $rawWeight);
        }

        return $weightInGrams > 0 ? $weightInGrams : self::MIN_WEIGHT_GRAMS;
    }
}
