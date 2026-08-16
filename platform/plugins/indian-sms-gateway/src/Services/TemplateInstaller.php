<?php

namespace Ashikul\IndiaSmsGateway\Services;

use Ashikul\IndiaSmsGateway\Models\SmsTemplate;
use Illuminate\Support\Facades\Schema;

class TemplateInstaller
{
    public function ensureDefaults(): void
    {
        if (! Schema::hasTable('india_sms_templates')) {
            return;
        }

        foreach ($this->defaults() as $key => $template) {
            SmsTemplate::query()->firstOrCreate(
                ['key' => $key],
                [
                    'name' => $template['name'],
                    'language' => 'en',
                    'content' => $template['content'],
                    'is_active' => true,
                    'variables' => $template['variables'],
                ],
            );
        }
    }

    private function defaults(): array
    {
        return [
            'checkout_otp' => [
                'name' => 'Checkout OTP',
                'content' => 'Your {{ site_name }} checkout verification code is {{ code }}. It expires in {{ expires_in }} minutes.',
                'variables' => ['site_name', 'code', 'expires_in'],
            ],
            'order_created_customer' => [
                'name' => 'Customer: New Order Received',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} has been received. Total: {{ order_total }}. We will contact you shortly.',
                'variables' => ['customer_name', 'order_id', 'order_total'],
            ],
            'order_created_admin' => [
                'name' => 'Admin: New Order Alert',
                'content' => 'New order {{ order_id }} from {{ customer_name }}. Total: {{ order_total }}. Phone: {{ customer_phone }}.',
                'variables' => ['order_id', 'customer_name', 'order_total', 'customer_phone'],
            ],
            'order_status_changed_customer' => [
                'name' => 'Customer: Order Status Changed',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} status is now {{ status }}.',
                'variables' => ['customer_name', 'order_id', 'status', 'previous_status'],
            ],
            'order_status_pending' => [
                'name' => 'Customer: Order Pending',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} is pending confirmation.',
                'variables' => ['customer_name', 'order_id', 'status'],
            ],
            'order_status_processing' => [
                'name' => 'Customer: Order Processing',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} is now being processed.',
                'variables' => ['customer_name', 'order_id', 'status'],
            ],
            'order_status_completed' => [
                'name' => 'Customer: Order Completed',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} has been completed. Thank you for shopping with us.',
                'variables' => ['customer_name', 'order_id', 'status'],
            ],
            'order_status_canceled' => [
                'name' => 'Customer: Order Canceled',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} has been canceled. Please contact us if you need help.',
                'variables' => ['customer_name', 'order_id', 'status'],
            ],

            'order_status_confirmed' => [
                'name' => 'Customer: Order Confirmed',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} has been confirmed.',
                'variables' => ['customer_name', 'order_id', 'status'],
            ],
            'order_status_delivered' => [
                'name' => 'Customer: Order Delivered',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} has been delivered. Thank you for shopping with us.',
                'variables' => ['customer_name', 'order_id', 'status'],
            ],
            'order_status_cancelled' => [
                'name' => 'Customer: Order Cancelled',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} has been cancelled. Please contact us if you need help.',
                'variables' => ['customer_name', 'order_id', 'status'],
            ],
            'order_payment_confirmed_customer' => [
                'name' => 'Customer: Payment Confirmed',
                'content' => 'Hi {{ customer_name }}, payment for order {{ order_id }} has been confirmed.',
                'variables' => ['customer_name', 'order_id', 'order_total'],
            ],
            'shipping_status_changed_customer' => [
                'name' => 'Customer: Shipping Status Changed',
                'content' => 'Hi {{ customer_name }}, shipping for order {{ order_id }} is now {{ shipping_status }}. Tracking: {{ tracking_id }}',
                'variables' => ['customer_name', 'order_id', 'shipping_status', 'tracking_id'],
            ],
            'order_status_partial_returned' => [
                'name' => 'Customer: Order Partially Returned',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} has been marked as partially returned.',
                'variables' => ['customer_name', 'order_id', 'status'],
            ],
            'order_status_returned' => [
                'name' => 'Customer: Order Returned',
                'content' => 'Hi {{ customer_name }}, your order {{ order_id }} has been marked as returned.',
                'variables' => ['customer_name', 'order_id', 'status'],
            ],
        ];
    }
}
