<?php

namespace Botble\Sms\Listeners;

use Botble\Ecommerce\Events\OrderConfirmedEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\Ecommerce\Events\OrderCreated;
use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;
use Botble\Ecommerce\Events\OrderReturnedEvent;
use Botble\Ecommerce\Events\ShippingStatusChanged;
use Botble\Ecommerce\Models\Order;
use Botble\Base\Facades\BaseHelper;
use Botble\Sms\Supports\SmsHandler;
use Botble\Sms\Enums\SmsEnum;
use Illuminate\Support\Facades\Log;

class OrderSmsListener
{
    /**
     * Handle order confirmed event.
     */
    public function handleOrderConfirmed(OrderConfirmedEvent $event): void
    {
        $order = $event->order;
        if ($order && is_plugin_active('sms')) {
            $this->sendSmsNotification($order, SmsEnum::ORDER_CONFIRMATION());
        }
    }

    /**
     * Handle order completed event.
     */
    public function handleOrderCompleted(OrderCompletedEvent $event): void
    {
        $order = $event->order;
        if ($order && is_plugin_active('sms')) {
            $this->sendSmsNotification($order, SmsEnum::DELIVERING_CONFIRMATION());
        }
    }

    /**
     * Handle order cancelled event.
     */
    public function handleOrderCancelled(OrderCancelledEvent $event): void
    {
        $order = $event->order;
        if ($order && is_plugin_active('sms')) {
            $this->sendSmsNotification($order, SmsEnum::ORDER_CANCELLATION());
        }
    }

    public function handleOrderCreated(OrderCreated $event): void
    {
        if ($event->order && is_plugin_active('sms')) {
            $this->sendSmsNotification($event->order, SmsEnum::ORDER_CREATED_CUSTOMER());
            $this->sendAdminSmsNotification($event->order, SmsEnum::ORDER_CREATED_ADMIN());
            $this->sendVendorSmsNotification($event->order, SmsEnum::VENDOR_NEW_ORDER());
        }
    }

    public function handleOrderPaymentConfirmed(OrderPaymentConfirmedEvent $event): void
    {
        if ($event->order && is_plugin_active('sms')) {
            $this->sendSmsNotification($event->order, SmsEnum::ORDER_PAYMENT_CONFIRMED_CUSTOMER());
        }
    }

    public function handleOrderReturned(OrderReturnedEvent $event): void
    {
        if ($event->order && $event->order->order && is_plugin_active('sms')) {
            $this->sendSmsNotification($event->order->order, SmsEnum::ORDER_STATUS_RETURNED(), [
                'return_reason' => $event->order->reason ? $event->order->reason->label() : '',
            ]);
        }
    }

    public function handleShippingStatusChanged(ShippingStatusChanged $event): void
    {
        if ($event->shipment && $event->shipment->order && is_plugin_active('sms')) {
            $this->sendSmsNotification($event->shipment->order, SmsEnum::SHIPPING_STATUS_CHANGED_CUSTOMER());
        }
    }

    /**
     * Common method to send transactional order SMS.
     */
    protected function sendSmsNotification(Order $order, string $template, array $extra = [], ?string $to = null): void
    {
        try {
            $phone = $to ?: ($order->user->phone ?: ($order->address ? $order->address->phone : null));
            if (!$phone) {
                return;
            }

            $sms = new SmsHandler();
            $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);

            if ($sms->templateEnabled($template)) {
                $sms->setVariableValues(array_merge([
                    'store_name'      => get_ecommerce_setting('store_name') ?: (setting('admin_title') ?: config('app.name')),
                    'store_address'   => get_ecommerce_setting('store_address'),
                    'store_phone'     => get_ecommerce_setting('store_phone'),
                    'store_link'      => url(''),
                    'order_id'        => $order->code,
                    'order_token'     => $order->token,
                    'customer_name'   => BaseHelper::clean($order->user->name ?: ($order->address ? $order->address->name : '')),
                    'customer_email'  => $order->user->email ?: ($order->address ? $order->address->email : ''),
                    'customer_phone'  => $order->user->phone ?: ($order->address ? $order->address->phone : ''),
                    'customer_address'=> $order->full_address,
                    'shipping_method' => $order->shipping_method_name,
                    'payment_method'  => $order->payment && $order->payment->payment_channel ? $order->payment->payment_channel->label() : '',
                ], $extra));

                $sms->sendUsingTemplate($template, $phone);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Order SMS template ' . $template . ': ' . $e->getMessage());
        }
    }

    protected function sendAdminSmsNotification(Order $order, string $template): void
    {
        $phone = preg_replace('/\D+/', '', (string) get_ecommerce_setting('store_phone'));

        if (! $phone) {
            return;
        }

        $this->sendSmsNotification($order, $template, [], $phone);
    }

    protected function sendVendorSmsNotification(Order $order, string $template): void
    {
        $store = $order->store ?? null;

        if (! $store || ! $store->phone) {
            return;
        }

        $this->sendSmsNotification($order, $template, [
            'store_name' => (string) $store->name,
            'store_phone' => (string) $store->phone,
            'store_link' => (string) ($store->url ?? ''),
        ], $store->phone);
    }
}
