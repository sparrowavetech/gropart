<?php

namespace Botble\Sms\Listeners;

use Botble\Ecommerce\Events\OrderConfirmedEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Events\OrderCancelledEvent;
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

    /**
     * Common method to send transactional order SMS.
     */
    protected function sendSmsNotification(Order $order, string $template): void
    {
        try {
            $phone = $order->user->phone ?: ($order->address ? $order->address->phone : null);
            if (!$phone) {
                return;
            }

            $sms = new SmsHandler();
            $sms->setModule(ECOMMERCE_MODULE_SCREEN_NAME);

            if ($sms->templateEnabled($template)) {
                $sms->setVariableValues([
                    'store_address'   => get_ecommerce_setting('store_address'),
                    'store_phone'     => get_ecommerce_setting('store_phone'),
                    'order_id'        => $order->code,
                    'order_token'     => $order->token,
                    'customer_name'   => BaseHelper::clean($order->user->name ?: ($order->address ? $order->address->name : '')),
                    'customer_email'  => $order->user->email ?: ($order->address ? $order->address->email : ''),
                    'customer_phone'  => $order->user->phone ?: ($order->address ? $order->address->phone : ''),
                    'customer_address'=> $order->full_address,
                    'shipping_method' => $order->shipping_method_name,
                    'payment_method'  => $order->payment && $order->payment->payment_channel ? $order->payment->payment_channel->label() : '',
                ]);

                $sms->sendUsingTemplate($template, $phone);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Order SMS template ' . $template . ': ' . $e->getMessage());
        }
    }
}
