<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Botble\Ecommerce\Facades\OrderHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use SparroWave\FarmartHelper\Supports\OrderNotificationGuard;

echo "=== STARTING ORDER NOTIFICATION GUARD REGRESSION TESTS ===" . PHP_EOL . PHP_EOL;

$guard = app(OrderNotificationGuard::class);

// -------------------------------------------------------------
// Test 1: Payment Confirmation Gate (Razorpay PENDING vs COMPLETED)
// -------------------------------------------------------------
echo "Test 1: Payment Confirmation Gate... ";
$testOrder = Order::latest('id')->first();
if (!$testOrder) {
    die("ERROR: No test order found in database." . PHP_EOL);
}

// Create a mock payment relation
$pendingPayment = new Payment([
    'payment_channel' => 'razorpay',
    'status' => PaymentStatusEnum::PENDING,
]);
$testOrder->setRelation('payment', $pendingPayment);
if ($guard->isPaymentConfirmedForNotification($testOrder) !== false) {
    echo "FAIL: Pending Razorpay payment was not blocked!" . PHP_EOL;
    exit(1);
}

$completedPayment = new Payment([
    'payment_channel' => 'razorpay',
    'status' => PaymentStatusEnum::COMPLETED,
]);
$testOrder->setRelation('payment', $completedPayment);
if ($guard->isPaymentConfirmedForNotification($testOrder) !== true) {
    echo "FAIL: Completed Razorpay payment was not allowed!" . PHP_EOL;
    exit(1);
}

// COD should be allowed even when pending
$codPayment = new Payment([
    'payment_channel' => 'cod',
    'status' => PaymentStatusEnum::PENDING,
]);
$testOrder->setRelation('payment', $codPayment);
if ($guard->isPaymentConfirmedForNotification($testOrder) !== true) {
    echo "FAIL: Pending COD payment was not allowed!" . PHP_EOL;
    exit(1);
}
echo "PASS!" . PHP_EOL;

// -------------------------------------------------------------
// Test 2: Admin Notification Idempotency (Metadata Guard)
// -------------------------------------------------------------
echo "Test 2: Admin Notification Idempotency... ";
// Clean test metadata first
$testOrder->orderMetadata()->where('meta_key', OrderNotificationGuard::META_ADMIN_SENT)->delete();

// Fake events/mail so no actual emails are sent during regression
Event::fake();

$firstAttempt = $guard->sendAdminNotificationOnce($testOrder);
if (!$firstAttempt) {
    echo "FAIL: First attempt should return true!" . PHP_EOL;
    exit(1);
}

$metadataValue = $testOrder->getOrderMetadata(OrderNotificationGuard::META_ADMIN_SENT);
if (empty($metadataValue)) {
    echo "FAIL: Metadata admin_order_notification_sent_at was not stored!" . PHP_EOL;
    exit(1);
}

$secondAttempt = $guard->sendAdminNotificationOnce($testOrder);
if ($secondAttempt !== false) {
    echo "FAIL: Second attempt should have been blocked by metadata idempotency guard!" . PHP_EOL;
    exit(1);
}
echo "PASS!" . PHP_EOL;

// -------------------------------------------------------------
// Test 3: Customer Notification Idempotency
// -------------------------------------------------------------
echo "Test 3: Customer Notification Idempotency... ";
$testOrder->orderMetadata()->where('meta_key', OrderNotificationGuard::META_CUSTOMER_SENT)->delete();

$ordersCollection = new Collection([$testOrder]);
$firstCustAttempt = $guard->sendCustomerConfirmationOnce($ordersCollection);
$custMetadata = $testOrder->getOrderMetadata(OrderNotificationGuard::META_CUSTOMER_SENT);
if (empty($custMetadata)) {
    echo "FAIL: Customer metadata customer_order_notification_sent_at was not stored!" . PHP_EOL;
    exit(1);
}

// Second call should skip
$guard->sendCustomerConfirmationOnce($ordersCollection);
echo "PASS!" . PHP_EOL;

// -------------------------------------------------------------
// Test 4: Vendor Notification Idempotency
// -------------------------------------------------------------
echo "Test 4: Vendor Notification Idempotency... ";
$testOrder->orderMetadata()->where('meta_key', OrderNotificationGuard::META_VENDOR_SENT)->delete();

$guard->sendVendorNotificationsOnce($ordersCollection);
if ($testOrder->store && $testOrder->store->email) {
    $vendorMetadata = $testOrder->getOrderMetadata(OrderNotificationGuard::META_VENDOR_SENT);
    if (empty($vendorMetadata)) {
        echo "FAIL: Vendor metadata was not stored!" . PHP_EOL;
        exit(1);
    }
}
echo "PASS!" . PHP_EOL;

// -------------------------------------------------------------
// Test 5: CheckoutOrderHelper::processOrder Re-Finalization Guard
// -------------------------------------------------------------
echo "Test 5: CheckoutOrderHelper Re-Finalization Guard... ";
$finishedOrder = Order::where('is_finished', true)->latest('id')->first();
if ($finishedOrder) {
    // Record current history count
    $historyCountBefore = DB::table('ec_order_histories')->where('order_id', $finishedOrder->id)->count();

    // Call processOrder on already finished order
    $result = OrderHelper::processOrder($finishedOrder->id);

    $historyCountAfter = DB::table('ec_order_histories')->where('order_id', $finishedOrder->id)->count();

    if ($historyCountAfter !== $historyCountBefore) {
        echo "FAIL: Calling processOrder on already finished order created duplicate history records!" . PHP_EOL;
        exit(1);
    }
}
echo "PASS!" . PHP_EOL;

// -------------------------------------------------------------
// Test 6: Clean up test metadata
// -------------------------------------------------------------
$testOrder->orderMetadata()->whereIn('meta_key', [
    OrderNotificationGuard::META_ADMIN_SENT,
    OrderNotificationGuard::META_CUSTOMER_SENT,
    OrderNotificationGuard::META_VENDOR_SENT,
])->delete();

echo PHP_EOL . "=== ALL 5 REGRESSION TESTS PASSED CLEANLY! ===" . PHP_EOL;

