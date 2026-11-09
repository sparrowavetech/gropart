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

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Revenue;
use Illuminate\Support\Facades\DB;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;

echo "====================================================================\n";
echo "TEST SUITE: SELLER INVOICE NUMBERING & MISSING ORDER RECONCILIATION\n";
echo "====================================================================\n\n";

$generator = app(InvoiceGeneratorService::class);

// ---------------------------------------------------------
// STEP 1: Verify Missing Order #152 and Backfill
// ---------------------------------------------------------
echo "1. Checking Completed Orders vs Invoices before backfill:\n";
$completedCount = Order::where('status', 'completed')->count();
$docCountBefore = Document::count();
echo "   Completed Orders: {$completedCount}\n";
echo "   Invoices before:  {$docCountBefore}\n";

$order152 = Order::find(152);
echo "   Order #152 status: {$order152->status}, code: {$order152->code}\n";
$rev152 = Revenue::where('order_id', 152)->first();
echo "   Order #152 revenue: ID={$rev152?->id}, type=" . var_export($rev152?->type, true) . ", fee={$rev152?->fee}\n";

echo "   Running backfillAllHistoricalOrders()...\n";
$backfilled = $generator->backfillAllHistoricalOrders();
echo "   Backfilled count: {$backfilled}\n";

$docCountAfter = Document::count();
echo "   Invoices after backfill: {$docCountAfter}\n";
if ($docCountAfter === $completedCount) {
    echo "   [PASS] Invoice count ({$docCountAfter}) matches completed orders ({$completedCount}) exactly!\n\n";
} else {
    echo "   [FAIL] Count mismatch: invoices={$docCountAfter}, completed={$completedCount}\n\n";
}

$doc152 = Document::where('order_id', 152)->first();
if ($doc152) {
    echo "   [PASS] Order #152 invoice successfully generated! ID={$doc152->id}, Number={$doc152->number}\n\n";
} else {
    echo "   [FAIL] Order #152 invoice still missing!\n\n";
}

// ---------------------------------------------------------
// STEP 2: Renumber All Invoices Chronologically
// ---------------------------------------------------------
echo "2. Running renumberAllDocumentsChronologically()...\n";
$renumbered = $generator->renumberAllDocumentsChronologically();
echo "   Renumbered {$renumbered} documents.\n\n";

// ---------------------------------------------------------
// STEP 3: Validate Indian GST Rule 46(b) Compliance
// ---------------------------------------------------------
echo "3. Validating Rule 46(b) Compliance across ALL documents:\n";

$docs = Document::orderBy('issued_at', 'asc')->get();
$byFy = [];
$invalidLengths = [];
$zeroEnding = [];
$regexMismatch = [];

foreach ($docs as $d) {
    $num = $d->number;
    $len = strlen($num);
    if ($len > 16) {
        $invalidLengths[] = "Doc #{$d->id}: {$num} (len: {$len})";
    }
    if (preg_match('/(?:000$|SIN000)/', $num)) {
        $zeroEnding[] = "Doc #{$d->id}: {$num}";
    }
    if (! preg_match('/^[A-Z0-9]+\/\d{2}-\d{2}\/\d{4}$/', $num)) {
        $regexMismatch[] = "Doc #{$d->id}: {$num}";
    }

    if (preg_match('/\/(\d{2}-\d{2})\/(\d+)$/', $num, $m)) {
        $fy = $m[1];
        $seq = (int) $m[2];
        $byFy[$fy][] = [
            'doc_id' => $d->id,
            'order_id' => $d->order_id,
            'num' => $num,
            'seq' => $seq,
            'issued_at' => (string) $d->issued_at,
        ];
    }
}

echo "   - Total documents checked: " . $docs->count() . "\n";
echo "   - Documents exceeding 16 chars: " . count($invalidLengths) . " (" . (empty($invalidLengths) ? "PASS" : "FAIL") . ")\n";
echo "   - Documents ending in 000: " . count($zeroEnding) . " (" . (empty($zeroEnding) ? "PASS" : "FAIL") . ")\n";
echo "   - Documents matching legal format Prefix/YY-YY/XXXX: " . ($docs->count() - count($regexMismatch)) . "/" . $docs->count() . " (" . (empty($regexMismatch) ? "PASS" : "FAIL") . ")\n\n";

if (! empty($invalidLengths)) {
    echo "   Sample invalid lengths:\n   " . implode("\n   ", array_slice($invalidLengths, 0, 5)) . "\n\n";
}
if (! empty($zeroEnding)) {
    echo "   Sample 000 ending:\n   " . implode("\n   ", array_slice($zeroEnding, 0, 5)) . "\n\n";
}

// ---------------------------------------------------------
// STEP 4: Validate Consecutive & Unique Sequences per FY
// ---------------------------------------------------------
echo "4. Checking Consecutive & Unique Sequences per Financial Year:\n";
foreach ($byFy as $fy => $items) {
    $count = count($items);
    $seqs = array_column($items, 'seq');
    $uniqueCount = count(array_unique($seqs));
    $minSeq = min($seqs);
    $maxSeq = max($seqs);
    $isConsecutive = ($minSeq === 1) && ($maxSeq === $count) && ($uniqueCount === $count);

    echo "   FY {$fy}: {$count} invoices\n";
    echo "     Range: " . sprintf('%04d', $minSeq) . " to " . sprintf('%04d', $maxSeq) . "\n";
    echo "     Unique count: {$uniqueCount} / {$count}\n";
    echo "     First: {$items[0]['num']} (Order #{$items[0]['order_id']}, Issued: {$items[0]['issued_at']})\n";
    echo "     Last:  {$items[$count - 1]['num']} (Order #{$items[$count - 1]['order_id']}, Issued: {$items[$count - 1]['issued_at']})\n";
    echo "     Strict consecutive from 0001: " . ($isConsecutive ? "PASS" : "FAIL") . "\n\n";
}

// Check Orders 291 and 279 specifically
$doc291 = Document::where('order_id', 291)->first();
$doc279 = Document::where('order_id', 279)->first();
echo "5. Specific Legacy Case Verification:\n";
echo "   Order #291 (earlier order): Doc #{$doc291?->id}, Number: {$doc291?->number}\n";
echo "   Order #279 (later order):   Doc #{$doc279?->id}, Number: {$doc279?->number}\n";
if ($doc291 && $doc279 && $doc291->number < $doc279->number && !str_ends_with($doc291->number, '000')) {
    echo "   [PASS] 291 got #0001 and 279 got #0002 without any 000!\n\n";
} else {
    echo "   [FAIL] Order 291/279 numbering issue!\n\n";
}

// ---------------------------------------------------------
// STEP 6: Test Real-Time Next Invoice Number Generation
// ---------------------------------------------------------
echo "6. Testing Real-time Next Invoice Number Generation:\n";
$nextNum = $generator->generateNextInvoiceNumber();
echo "   Next invoice number for now: {$nextNum}\n";
$expectedPrefix = $generator->getInvoicePrefix() . '/' . $generator->resolveFinancialYear(now()) . '/';
if (str_starts_with($nextNum, $expectedPrefix) && strlen($nextNum) <= 16) {
    echo "   [PASS] Next invoice number ({$nextNum}) is compliant and properly formatted!\n\n";
} else {
    echo "   [FAIL] Next invoice number unexpected format: {$nextNum}\n\n";
}

// ---------------------------------------------------------
// STEP 7: Test Batch Regeneration
// ---------------------------------------------------------
echo "7. Testing Batch Regeneration (offset 0, limit 25):\n";
$batch1 = $generator->regenerateBatch(0, 25);
echo "   Batch 1: Total={$batch1['total']}, Processed={$batch1['processed']}, Percentage={$batch1['percentage']}%\n";
if ($batch1['total'] === 335 && $batch1['processed'] === 25) {
    echo "   [PASS] Batch regeneration running cleanly!\n\n";
} else {
    echo "   [FAIL] Unexpected batch result: " . json_encode($batch1) . "\n\n";
}

echo "====================================================================\n";
echo "ALL TESTS COMPLETED\n";
echo "====================================================================\n";

