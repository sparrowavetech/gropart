<?php

namespace SparroWave\Shipmozo\Supports;

use Botble\Ecommerce\Models\Order;

class ShippingBreakdown
{
    public const META_KEY = 'shipmozo_shipping_breakdown_v1';

    private const REQUEST_KEY = 'shipmozo_selected_shipping_quotes';

    public static function fromRate(array $rate): ?array
    {
        if (empty($rate['is_mixed']) || ! isset($rate['price'], $rate['direct_shipping_fee'], $rate['customer_shipmozo_price'])
            || ! is_numeric($rate['price']) || ! is_numeric($rate['direct_shipping_fee'])
            || ! is_numeric($rate['customer_shipmozo_price']) || empty($rate['id'])) {
            return null;
        }

        $total = (float) $rate['price'];
        $direct = (float) $rate['direct_shipping_fee'];
        $courier = (float) $rate['customer_shipmozo_price'];

        if ($total < 0 || $direct < 0 || $courier < 0 || abs($total - $direct - $courier) > 0.011) {
            return null;
        }

        return [
            'version' => 1,
            'option' => (string) ($rate['id'] ?? ''),
            'total' => round($total, 2),
            'direct' => round($direct, 2),
            'courier' => round($courier, 2),
            'direct_rule_name' => (string) ($rate['direct_rule_name'] ?? ''),
            'shipmozo_weight' => max(0, (float) ($rate['shipmozo_weight'] ?? 0)),
            'shipmozo_count' => max(0, (int) ($rate['shipmozo_count'] ?? 0)),
            'direct_weight' => max(0, (float) ($rate['direct_weight'] ?? 0)),
            'direct_count' => max(0, (int) ($rate['direct_count'] ?? 0)),
            'weight_unit' => (string) ($rate['weight_unit'] ?? ''),
        ];
    }

    public static function remember(array $rate, array $data): void
    {
        $storeId = (string) (data_get($data, 'extra.store_id') ?? data_get($data, 'store_id') ?? 0);
        $quotes = (array) request()->attributes->get(self::REQUEST_KEY, []);
        $quotes[$storeId] = self::fromRate($rate);
        request()->attributes->set(self::REQUEST_KEY, $quotes);
    }

    public static function persistForOrder(Order $order): void
    {
        $quotes = (array) request()->attributes->get(self::REQUEST_KEY, []);
        $quote = $quotes[(string) ($order->store_id ?? 0)] ?? null;
        $isShipmozo = $order->shipping_method?->getValue() === SHIPMOZO_SHIPPING_METHOD_NAME;

        if ($isShipmozo && $quote && $quote['option'] === (string) $order->shipping_option
            && abs($quote['total'] - (float) $order->shipping_amount) <= 0.011) {
            $order->setOrderMetadata(self::META_KEY, json_encode($quote, JSON_THROW_ON_ERROR));

            return;
        }

        // An edited/retried order must not retain a breakdown from an older quote.
        if ($order->wasChanged(['shipping_method', 'shipping_option', 'shipping_amount'])) {
            $order->orderMetadata()->where('meta_key', self::META_KEY)->delete();
        }
    }

    public static function saveRate(Order $order, array $rate): void
    {
        $breakdown = self::fromRate($rate);
        if ($breakdown && $breakdown['option'] === (string) $order->shipping_option
            && abs($breakdown['total'] - (float) $order->shipping_amount) <= 0.011) {
            $order->setOrderMetadata(self::META_KEY, json_encode($breakdown, JSON_THROW_ON_ERROR));
        } else {
            $order->orderMetadata()->where('meta_key', self::META_KEY)->delete();
        }
    }

    public static function forOrder(Order $order): ?array
    {
        $value = $order->getOrderMetadata(self::META_KEY);
        $data = is_string($value) ? json_decode($value, true) : null;
        if (! is_array($data) || ($data['version'] ?? null) !== 1
            || ($data['option'] ?? null) !== (string) $order->shipping_option
            || abs((float) ($data['total'] ?? -1) - (float) $order->shipping_amount) > 0.011) {
            return null;
        }

        return $data;
    }
}
