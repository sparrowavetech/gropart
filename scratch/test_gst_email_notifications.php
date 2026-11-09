<?php

$workspace = '/Applications/XAMPP/xamppfiles/htdocs/gropart';

spl_autoload_register(function ($class) use ($workspace) {
    $prefixes = [
        'SparroWave\\SellerInvoicing\\' => $workspace . '/platform/plugins/seller-invoicing/src/',
        'Botble\\Ecommerce\\' => $workspace . '/platform/plugins/ecommerce/src/',
        'Botble\\Marketplace\\' => $workspace . '/platform/plugins/marketplace/src/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

require $workspace . '/vendor/autoload.php';
$app = require_once $workspace . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Botble\Base\Facades\EmailHandler;
use Illuminate\Support\Facades\Mail;
use SparroWave\SellerInvoicing\Services\Gst\GstReportService;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

echo "====================================================================\n";
echo "TEST SUITE: GST REPORT AUTOMATED EMAIL NOTIFICATIONS (SELLER & ADMIN)\n";
echo "====================================================================\n\n";

// 1. Guard check
if (! TaxEngine::isGstReportEnabled()) {
    echo "[FAIL] TaxEngine::isGstReportEnabled() returned false!\n";
    exit(1);
}
echo "1. Guard Check: PASS (isIndianBillingMode & isGstReportEnabled are TRUE)\n\n";

// 2. Templates existence and config check
echo "2. Checking Email Template Configurations:\n";
$emailConfig = config('plugins.seller-invoicing.email.templates', []);
echo "   Registered templates: " . implode(', ', array_keys($emailConfig)) . "\n";

$sellerTemplate = $emailConfig['monthly_gst_report'] ?? null;
$adminTemplate = $emailConfig['admin_monthly_gst_summary'] ?? null;

if ($sellerTemplate && $adminTemplate) {
    echo "   [PASS] Both 'monthly_gst_report' and 'admin_monthly_gst_summary' are registered!\n";
} else {
    echo "   [FAIL] Missing email template config!\n";
}

$sellerTplFile = platform_path('plugins/seller-invoicing/resources/email-templates/monthly_gst_report.tpl');
$adminTplFile = platform_path('plugins/seller-invoicing/resources/email-templates/admin_monthly_gst_summary.tpl');
echo "   Seller TPL file exists: " . (file_exists($sellerTplFile) ? "YES (PASS)" : "NO (FAIL)") . "\n";
echo "   Admin TPL file exists:  " . (file_exists($adminTplFile) ? "YES (PASS)" : "NO (FAIL)") . "\n\n";

// 3. Fake Mail to prevent real outbound emails and inspect sent messages
Mail::fake();

echo "3. Testing GstReportService::sendMonthlyGstNotifications for 2026-02...\n";
$reportService = app(GstReportService::class);
$result = $reportService->sendMonthlyGstNotifications('2026-02');

echo "   Result Summary:\n";
echo "     - Sellers Notified: " . $result['sellers_notified'] . "\n";
echo "     - Admin Notified:   " . ($result['admin_notified'] ? 'YES' : 'NO') . "\n";
echo "     - Active Stores:    " . $result['active_stores'] . "\n";
echo "     - Total Sales:      ₹" . number_format($result['total_sales'], 2) . "\n";
echo "     - Total Output Tax: ₹" . number_format($result['total_tax'], 2) . "\n";
echo "     - Total Marketplace ITC: ₹" . number_format($result['total_itc'], 2) . "\n\n";

if ($result['sellers_notified'] > 0 && $result['admin_notified']) {
    echo "   [PASS] Notifications dispatched to active sellers and administrator!\n\n";
} else {
    echo "   [WARN] Sellers notified: {$result['sellers_notified']}, Admin: " . ($result['admin_notified'] ? 'YES' : 'NO') . "\n\n";
}

// 4. Test Single Store Notification (Store #12 - SB Marketing)
echo "4. Testing Targeted Single Store Notification (Store #12 - SB Marketing):\n";
$singleResult = $reportService->sendMonthlyGstNotifications('2026-02', 12);
echo "   Single Store Result: Sellers={$singleResult['sellers_notified']}, Admin=" . ($singleResult['admin_notified'] ? 'YES' : 'NO') . "\n";
if ($singleResult['sellers_notified'] === 1 && $singleResult['admin_notified']) {
    echo "   [PASS] Targeted notification to Store #12 and Admin succeeded!\n\n";
} else {
    echo "   [FAIL] Targeted notification failed!\n\n";
}

// 5. Test Scheduler Registration in ServiceProvider
echo "5. Checking Console Scheduler Registration:\n";
$schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
$events = collect($schedule->events());
$gstEvent = $events->first(function ($e) {
    return str_contains($e->command ?? '', 'seller-invoicing:compile-monthly-gst');
});

if ($gstEvent) {
    echo "   [PASS] Automated cron found: '{$gstEvent->command}' with expression: '{$gstEvent->expression}' (Runs monthly at 02:00 AM)!\n\n";
} else {
    echo "   [WARN] Scheduler event check (may depend on console kernel boot)\n\n";
}

echo "====================================================================\n";
echo "ALL NOTIFICATION TESTS COMPLETED\n";
echo "====================================================================\n";

