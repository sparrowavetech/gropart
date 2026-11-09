<?php

namespace SparroWave\FarmartHelper\Supports;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\EmailHandler as EmailHandlerSupport;
use Botble\Ecommerce\Enums\OrderHistoryActionEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderHistory;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderNotificationGuard
{
    public const META_ADMIN_SENT = 'admin_order_notification_sent_at';
    public const META_CUSTOMER_SENT = 'customer_order_notification_sent_at';
    public const META_VENDOR_SENT = 'vendor_order_notification_sent_at';

    public function handle(Collection $orders): Collection
    {
        if ($orders->isEmpty()) {
            return $orders;
        }

        /** @var Order $firstOrder */
        $firstOrder = $orders->first();

        // 1. Payment Confirmation Gate:
        // For online gateways (e.g. Razorpay), ensure payment is confirmed (COMPLETED)
        // before dispatching order notifications.
        if (! $this->isPaymentConfirmedForNotification($firstOrder)) {
            Log::info('order_notification_guard.skipped_unconfirmed_payment', [
                'order_id' => $firstOrder->id,
                'channel' => $firstOrder->payment?->payment_channel,
                'status' => $firstOrder->payment?->status,
            ]);

            return $orders;
        }

        // 2. Lock per checkout token or primary order to prevent concurrent race conditions
        $token = $firstOrder->token ?: 'order-' . $firstOrder->id;
        $lockKey = 'order-notify-lock-' . sha1($token);

        try {
            Cache::lock($lockKey, 15)->block(10, function () use ($orders, $firstOrder) {
                $this->sendAdminNotificationOnce($firstOrder);
                $this->sendCustomerConfirmationOnce($orders);
                $this->sendVendorNotificationsOnce($orders);
            });
        } catch (Throwable $e) {
            Log::warning('order_notification_guard.lock_fallback: ' . $e->getMessage());
            $this->sendAdminNotificationOnce($firstOrder);
            $this->sendCustomerConfirmationOnce($orders);
            $this->sendVendorNotificationsOnce($orders);
        }

        return $orders;
    }

    public function isPaymentConfirmedForNotification(Order $order): bool
    {
        $payment = $order->payment;
        if (! $payment && is_plugin_active('payment')) {
            $payment = Payment::query()
                ->where('order_id', $order->getKey())
                ->latest('id')
                ->first();
        }

        if ($payment) {
            $channel = is_object($payment->payment_channel)
                ? $payment->payment_channel->getValue()
                : (string) $payment->payment_channel;

            // Online payment gateways: strictly require COMPLETED status
            if (in_array(strtolower($channel), ['razorpay', 'stripe', 'paypal', 'mollie', 'paystack'])) {
                $status = is_object($payment->status)
                    ? $payment->status->getValue()
                    : (string) $payment->status;

                return $status === PaymentStatusEnum::COMPLETED;
            }

            // Offline payment methods (COD, Bank Transfer):
            // Order is placed, notification is valid
            return true;
        }

        // If order amount is 0 (e.g. 100% discount / free order), allow notification
        if ((float) $order->amount <= 0) {
            return true;
        }

        return true;
    }

    public function sendAdminNotificationOnce(Order $order): bool
    {
        // Persistent idempotency check
        if ($order->getOrderMetadata(self::META_ADMIN_SENT)) {
            Log::info('order_notification_guard.admin_email_already_sent', [
                'order_id' => $order->id,
                'sent_at' => $order->getOrderMetadata(self::META_ADMIN_SENT),
            ]);

            return false;
        }

        // Mark immediately before dispatch to guard against concurrent attempts
        $order->setOrderMetadata(self::META_ADMIN_SENT, Carbon::now()->toIso8601String());

        try {
            $mailer = $this->setEmailVariables($order);
            $recipients = EcommerceHelper::getAdminNotificationEmails();

            if (! empty($recipients)) {
                $mailer->sendUsingTemplate('admin_new_order', $recipients);
                Log::info('order_notification_guard.admin_email_dispatched', [
                    'order_id' => $order->id,
                    'recipients' => $recipients,
                ]);
            }

            return true;
        } catch (Throwable $e) {
            Log::error('order_notification_guard.admin_email_failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);

            return false;
        }
    }

    public function sendCustomerConfirmationOnce(Collection $orders): bool
    {
        try {
            $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);

            if (! $mailer->templateEnabled('customer_new_order')) {
                return false;
            }

            foreach ($orders as $order) {
                if ($order->getOrderMetadata(self::META_CUSTOMER_SENT)) {
                    Log::info('order_notification_guard.customer_email_already_sent', [
                        'order_id' => $order->id,
                        'sent_at' => $order->getOrderMetadata(self::META_CUSTOMER_SENT),
                    ]);

                    continue;
                }

                $order->setOrderMetadata(self::META_CUSTOMER_SENT, Carbon::now()->toIso8601String());

                $customerEmail = $order->user->email ?: $order->address?->email;
                if (! $customerEmail) {
                    continue;
                }

                $mailer = $this->setEmailVariables($order);
                $mailer->sendUsingTemplate('customer_new_order', $customerEmail);

                OrderHistory::query()->create([
                    'action' => OrderHistoryActionEnum::SEND_ORDER_CONFIRMATION_EMAIL,
                    'description' => trans('plugins/ecommerce::order.confirmation_email_was_sent_to_customer'),
                    'order_id' => $order->id,
                ]);

                Log::info('order_notification_guard.customer_email_dispatched', [
                    'order_id' => $order->id,
                    'email' => $customerEmail,
                ]);
            }

            return true;
        } catch (Throwable $e) {
            Log::error('order_notification_guard.customer_email_failed: ' . $e->getMessage());

            return false;
        }
    }

    public function sendVendorNotificationsOnce(Collection $orders): bool
    {
        if (! is_plugin_active('marketplace')) {
            return false;
        }

        try {
            $mailer = EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME);

            if (! $mailer->templateEnabled('store_new_order')) {
                return false;
            }

            $orders->loadMissing(['store']);

            foreach ($orders as $order) {
                if (! $order->store || ! $order->store->email) {
                    continue;
                }

                if ($order->getOrderMetadata(self::META_VENDOR_SENT)) {
                    Log::info('order_notification_guard.vendor_email_already_sent', [
                        'order_id' => $order->id,
                        'store_id' => $order->store_id,
                        'sent_at' => $order->getOrderMetadata(self::META_VENDOR_SENT),
                    ]);

                    continue;
                }

                $order->setOrderMetadata(self::META_VENDOR_SENT, Carbon::now()->toIso8601String());

                MarketplaceHelper::setEmailVendorVariables($order);
                $mailer->sendUsingTemplate('store_new_order', $order->store->email);

                Log::info('order_notification_guard.vendor_email_dispatched', [
                    'order_id' => $order->id,
                    'store_id' => $order->store_id,
                    'vendor_email' => $order->store->email,
                ]);
            }

            return true;
        } catch (Throwable $e) {
            Log::error('order_notification_guard.vendor_email_failed: ' . $e->getMessage());

            return false;
        }
    }

    public function setEmailVariables(Order $order): EmailHandlerSupport
    {
        $variables = OrderHelper::getEmailVariables($order);

        if (is_plugin_active('marketplace') && $order->store) {
            $variables = array_merge($variables, [
                'store_address' => $order->store->full_address ?: get_ecommerce_setting('store_address'),
                'store_name' => $order->store->name ?: get_ecommerce_setting('store_name'),
                'store_phone' => $order->store->phone ?: get_ecommerce_setting('store_phone'),
                'store_link' => $order->store->url,
                'store' => $order->store->toArray(),
                'product_list' => view('plugins/marketplace::emails.partials.order-detail', compact('order'))->render(),
            ]);
        }

        return EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME)
            ->setVariableValues($variables);
    }
}

