<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Ashikul\IndiaSmsGateway\DTOs\SmsMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class EcommerceNotificationService
{
    public function __construct(
        private SettingsRepository $settings,
        private TemplateRenderer $templates,
        private SmsManager $sms,
        private PhoneNormalizer $phones,
        private OtpService $otp,
    ) {
    }

    public function orderCreated(mixed $payload): void
    {
        if (! $this->notificationsEnabled()) {
            return;
        }

        $order = $this->resolveOrder($payload);

        if (! $order) {
            return;
        }

        $order = $this->refreshOrder($order);
        $variables = $this->orderVariables($order);
        $customerPhone = $this->customerPhone($order);

        $this->finalizeCheckoutVerification($customerPhone, $order);

        if ($this->settings->bool('customer_new_order_sms', true)) {
            $message = $this->templates->render(
                'order_created_customer',
                $variables,
                'Hi {{ customer_name }}, your order {{ order_id }} has been received. Total: {{ order_total }}.',
            );
            $this->send($customerPhone, $message, 'order_created_customer', $order);
        }

        if ($this->settings->bool('admin_new_order_sms', true)) {
            $message = $this->templates->render(
                'order_created_admin',
                $variables,
                'New order {{ order_id }} from {{ customer_name }}. Total: {{ order_total }}. Phone: {{ customer_phone }}.',
            );

            foreach ($this->adminPhones() as $phone) {
                $this->send($phone, $message, 'order_created_admin', $order);
            }
        }
    }

    public function orderStatusChanged(Model $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $status = $this->enumValue($order->getAttribute('status'));
        $previousStatus = $this->enumValue($order->getOriginal('status'));

        $this->sendLifecycleStatus($order, $status, $previousStatus);
    }

    public function orderLifecycleChanged(mixed $payload, string $status): void
    {
        $order = $this->resolveOrder($payload);

        if (! $order) {
            return;
        }

        // A domain event carries the authoritative transition. Some custom
        // builds dispatch it before the model instance is refreshed, so use
        // the explicit event status instead of a possibly stale attribute.
        $this->sendLifecycleStatus($order, $status, '');
    }

    public function paymentConfirmed(mixed $payload): void
    {
        if (! $this->notificationsEnabled()
            || ! $this->settings->bool('customer_payment_sms', true)) {
            return;
        }

        $order = $this->resolveOrder($payload);

        if (! $order) {
            return;
        }

        $order = $this->refreshOrder($order);
        $variables = $this->orderVariables($order);
        $message = $this->templates->render(
            'order_payment_confirmed_customer',
            $variables,
            'Hi {{ customer_name }}, payment for order {{ order_id }} has been confirmed.',
        );

        $this->send($this->customerPhone($order), $message, 'order_payment_confirmed_customer', $order);
    }

    public function shippingStatusChanged(mixed $payload, mixed $shipment = null): void
    {
        if (! $this->notificationsEnabled()
            || ! $this->settings->bool('customer_shipping_sms', true)) {
            return;
        }

        $order = $this->resolveOrder($payload);

        if (! $order) {
            return;
        }

        $order = $this->refreshOrder($order);
        $shippingStatus = $this->enumValue(data_get($shipment, 'status'));
        $variables = array_merge($this->orderVariables($order), [
            'shipping_status' => $this->statusLabel($shippingStatus ?: 'updated'),
            'tracking_id' => (string) (data_get($shipment, 'tracking_id') ?: data_get($shipment, 'tracking_code') ?: ''),
        ]);
        $message = $this->templates->render(
            'shipping_status_changed_customer',
            $variables,
            'Hi {{ customer_name }}, shipping for order {{ order_id }} is now {{ shipping_status }}.',
        );

        $this->send($this->customerPhone($order), $message, 'shipping_status_changed_customer', $order);
    }

    private function sendLifecycleStatus(Model $order, string $status, string $previousStatus = ''): void
    {
        if (! $this->notificationsEnabled()
            || ! $this->settings->bool('customer_status_sms', true)) {
            return;
        }

        $order = $this->refreshOrder($order);
        $normalizedStatus = $this->normalizeStatusKey($status);
        $variables = array_merge($this->orderVariables($order), [
            'status' => $this->statusLabel($status),
            'status_key' => $normalizedStatus,
            'previous_status' => $this->statusLabel($previousStatus),
        ]);
        $fallback = $this->templates->render(
            'order_status_changed_customer',
            $variables,
            'Hi {{ customer_name }}, your order {{ order_id }} status is now {{ status }}.',
        );
        $message = $this->templates->render('order_status_' . $normalizedStatus, $variables, $fallback);

        // "cancelled" and "canceled" are both used by different Botble builds.
        if ($message === $fallback && $normalizedStatus === 'cancelled') {
            $message = $this->templates->render('order_status_canceled', $variables, $fallback);
        }

        $this->send(
            $this->customerPhone($order),
            $message,
            'order_status_' . $normalizedStatus,
            $order,
        );
    }

    private function notificationsEnabled(): bool
    {
        return $this->settings->bool('enabled')
            && $this->settings->bool('ecommerce_notifications_enabled', true);
    }

    private function resolveOrder(mixed $payload): ?Model
    {
        if ($payload instanceof Model && $this->looksLikeOrder($payload)) {
            return $payload;
        }

        if (is_object($payload)) {
            foreach (['order', 'model', 'data'] as $key) {
                $candidate = data_get($payload, $key);

                if ($candidate instanceof Model && $this->looksLikeOrder($candidate)) {
                    return $candidate;
                }
            }

            $shipmentOrder = data_get($payload, 'shipment.order');

            if ($shipmentOrder instanceof Model && $this->looksLikeOrder($shipmentOrder)) {
                return $shipmentOrder;
            }
        }

        if (is_array($payload)) {
            foreach (['order', 'model', 'data'] as $key) {
                $candidate = $payload[$key] ?? null;

                if ($candidate instanceof Model && $this->looksLikeOrder($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function looksLikeOrder(Model $model): bool
    {
        return str_ends_with(strtolower($model::class), '\\order');
    }

    private function orderVariables(Model $order): array
    {
        $customerName = (string) ($this->firstValue($order, [
            'address.name',
            'shippingAddress.name',
            'customer.name',
            'user.name',
        ]) ?: 'Customer');
        $phone = $this->customerPhone($order) ?: '';
        $total = $this->firstValue($order, ['amount', 'total', 'sub_total', 'price']);
        $orderId = $this->firstValue($order, ['code', 'order_code']) ?: $order->getKey();

        if (function_exists('get_order_code')) {
            try {
                $orderId = get_order_code($order->getKey());
            } catch (Throwable) {
            }
        }

        return [
            'order_id' => (string) $orderId,
            'order_code' => (string) $orderId,
            'order_total' => $this->formatAmount($total),
            'customer_name' => $customerName,
            'customer_phone' => $phone,
            'payment_method' => (string) ($this->firstValue($order, ['payment.payment_channel', 'payment.payment_method', 'payment_method']) ?: ''),
            'shipping_method' => (string) ($this->firstValue($order, ['shipping_method', 'shipment.shipping_method']) ?: ''),
            'status' => $this->statusLabel($this->enumValue($order->getAttribute('status'))),
        ];
    }

    private function customerPhone(Model $order): ?string
    {
        $value = $this->firstValue($order, [
            'address.phone',
            'shippingAddress.phone',
            'shipping_address.phone',
            'customer.phone',
            'user.phone',
            'phone',
        ]);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return $this->phones->normalize($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function adminPhones(): array
    {
        $raw = (string) $this->settings->get('admin_phone_numbers', '');
        $items = preg_split('/[\s,;]+/', $raw) ?: [];
        $phones = [];

        foreach ($items as $item) {
            if (trim($item) === '') {
                continue;
            }

            try {
                $phones[] = $this->phones->normalize($item);
            } catch (Throwable) {
            }
        }

        return array_values(array_unique($phones));
    }

    private function send(?string $phone, string $message, string $event, Model $order): void
    {
        if (! $phone || trim($message) === '') {
            return;
        }

        // Do not include the event name: Botble can emit both a model event and
        // a domain event for the same change. Same recipient/message must send once.
        $dedupeKey = 'india-sms:ecommerce:' . hash('sha256', implode('|', [
            (string) $order->getKey(),
            $phone,
            $message,
        ]));

        if (! Cache::add($dedupeKey, true, now()->addDay())) {
            return;
        }

        try {
            $result = $this->sms->dispatch(new SmsMessage(
                to: $phone,
                message: $message,
                type: 'transactional',
                metadata: [
                    'event' => $event,
                    'template_key' => $event,
                    'order_id' => (string) $order->getKey(),
                    'order_type' => $order::class,
                ],
            ));

            if (! $result->accepted) {
                Cache::forget($dedupeKey);
                Log::warning('India SMS ecommerce notification was rejected', [
                    'event' => $event,
                    'template_key' => $event,
                    'order_id' => $order->getKey(),
                    'error' => $result->errorMessage,
                ]);
            }
        } catch (Throwable $exception) {
            Cache::forget($dedupeKey);
            Log::error('India SMS ecommerce notification failed', [
                'event' => $event,
                'order_id' => $order->getKey(),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function finalizeCheckoutVerification(?string $phone, Model $order): void
    {
        if (! $phone || ! app()->bound('request') || ! request()->hasSession()) {
            return;
        }

        try {
            $verification = request()->session()->get('india_sms_verified.checkout', []);
            $token = is_array($verification) ? (string) ($verification['token'] ?? '') : '';
            $phoneHash = is_array($verification) ? (string) ($verification['phone_hash'] ?? '') : '';

            if ($phoneHash === hash('sha256', $phone)
                && $this->otp->validateToken($phone, $token, 'checkout', true)) {
                $subject = data_get($order, 'customer');
                $this->otp->rememberVerified($phone, 'checkout', $subject instanceof Model ? $subject : null);
                request()->session()->forget('india_sms_verified.checkout');
            }
        } catch (Throwable) {
        }
    }

    private function refreshOrder(Model $order): Model
    {
        try {
            if ($order->exists) {
                $order->refresh();
            }
        } catch (Throwable) {
        }

        foreach (['address', 'shippingAddress', 'customer', 'user', 'payment', 'shipment', 'shipments'] as $relation) {
            try {
                if (method_exists($order, $relation)) {
                    $order->loadMissing($relation);
                }
            } catch (Throwable) {
            }
        }

        return $order;
    }

    private function firstValue(Model $order, array $paths): mixed
    {
        foreach ($paths as $path) {
            try {
                $value = data_get($order, $path);
            } catch (Throwable) {
                $value = null;
            }

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function enumValue(mixed $value): string
    {
        if (is_object($value)) {
            if (method_exists($value, 'getValue')) {
                return (string) $value->getValue();
            }

            if (property_exists($value, 'value')) {
                return (string) $value->value;
            }

            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
        }

        return is_scalar($value) ? (string) $value : '';
    }

    private function normalizeStatusKey(string $status): string
    {
        $status = strtolower(trim($status));
        $status = (string) preg_replace('/[^a-z0-9]+/', '_', $status);

        return trim($status, '_') ?: 'updated';
    }

    private function statusLabel(string $status): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $status));
    }

    private function formatAmount(mixed $amount): string
    {
        if (! is_numeric($amount)) {
            return (string) $amount;
        }

        $currency = '৳';

        if (function_exists('get_application_currency')) {
            try {
                $activeCurrency = get_application_currency();
                $currency = (string) (data_get($activeCurrency, 'symbol') ?: data_get($activeCurrency, 'title') ?: $currency);
            } catch (Throwable) {
            }
        }

        return $currency . number_format((float) $amount, 2, '.', ',');
    }
}
