<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('india_sms_templates')) {
            return;
        }

        $templates = [
            'checkout_otp' => ['Checkout OTP', 'Your {{ site_name }} checkout verification code is {{ code }}. It expires in {{ expires_in }} minutes.', ['site_name', 'code', 'expires_in']],
            'order_created_customer' => ['Customer: New Order Received', 'Hi {{ customer_name }}, your order {{ order_id }} has been received. Total: {{ order_total }}. We will contact you shortly.', ['customer_name', 'order_id', 'order_total']],
            'order_created_admin' => ['Admin: New Order Alert', 'New order {{ order_id }} from {{ customer_name }}. Total: {{ order_total }}. Phone: {{ customer_phone }}.', ['order_id', 'customer_name', 'order_total', 'customer_phone']],
            'order_status_changed_customer' => ['Customer: Order Status Changed', 'Hi {{ customer_name }}, your order {{ order_id }} status is now {{ status }}.', ['customer_name', 'order_id', 'status', 'previous_status']],
            'order_status_pending' => ['Customer: Order Pending', 'Hi {{ customer_name }}, your order {{ order_id }} is pending confirmation.', ['customer_name', 'order_id', 'status']],
            'order_status_processing' => ['Customer: Order Processing', 'Hi {{ customer_name }}, your order {{ order_id }} is now being processed.', ['customer_name', 'order_id', 'status']],
            'order_status_confirmed' => ['Customer: Order Confirmed', 'Hi {{ customer_name }}, your order {{ order_id }} has been confirmed.', ['customer_name', 'order_id', 'status']],
            'order_status_completed' => ['Customer: Order Completed', 'Hi {{ customer_name }}, your order {{ order_id }} has been completed. Thank you for shopping with us.', ['customer_name', 'order_id', 'status']],
            'order_status_delivered' => ['Customer: Order Delivered', 'Hi {{ customer_name }}, your order {{ order_id }} has been delivered. Thank you for shopping with us.', ['customer_name', 'order_id', 'status']],
            'order_status_canceled' => ['Customer: Order Canceled', 'Hi {{ customer_name }}, your order {{ order_id }} has been canceled. Please contact us if you need help.', ['customer_name', 'order_id', 'status']],
            'order_status_cancelled' => ['Customer: Order Cancelled', 'Hi {{ customer_name }}, your order {{ order_id }} has been cancelled. Please contact us if you need help.', ['customer_name', 'order_id', 'status']],
            'order_status_returned' => ['Customer: Order Returned', 'Hi {{ customer_name }}, your order {{ order_id }} has been marked as returned.', ['customer_name', 'order_id', 'status']],
            'order_status_partial_returned' => ['Customer: Order Partially Returned', 'Hi {{ customer_name }}, your order {{ order_id }} has been marked as partially returned.', ['customer_name', 'order_id', 'status']],
            'order_payment_confirmed_customer' => ['Customer: Payment Confirmed', 'Hi {{ customer_name }}, payment for order {{ order_id }} has been confirmed.', ['customer_name', 'order_id', 'order_total']],
            'shipping_status_changed_customer' => ['Customer: Shipping Status Changed', 'Hi {{ customer_name }}, shipping for order {{ order_id }} is now {{ shipping_status }}. Tracking: {{ tracking_id }}', ['customer_name', 'order_id', 'shipping_status', 'tracking_id']],
        ];

        $now = now();

        foreach ($templates as $key => [$name, $content, $variables]) {
            if (! DB::table('india_sms_templates')->where('key', $key)->exists()) {
                DB::table('india_sms_templates')->insert([
                    'key' => $key,
                    'name' => $name,
                    'language' => 'en',
                    'content' => $content,
                    'is_active' => 1,
                    'variables' => json_encode($variables, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // User-edited templates are intentionally retained during downgrade.
    }
};
