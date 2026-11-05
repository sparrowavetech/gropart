<?php

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Store;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== TEST 1: Invoice Numbering 3-Digit Padding ===\n";
$generator = app(InvoiceGeneratorService::class);

$doc = new Document();
$doc->issued_at = now();

$num6 = $generator->formatInvoiceNumber($doc, null, 6);
echo "Sequence 6 formatted: {$num6}\n";
assert(str_ends_with($num6, '006'), "Expected number to end with 006, got {$num6}");

$num663 = $generator->formatInvoiceNumber($doc, null, 663);
echo "Sequence 663 formatted: {$num663}\n";
assert(str_ends_with($num663, '663'), "Expected number to end with 663, got {$num663}");

$num12 = $generator->formatInvoiceNumber($doc, null, 12);
echo "Sequence 12 formatted: {$num12}\n";
assert(str_ends_with($num12, '012'), "Expected number to end with 012, got {$num12}");

echo "=== TEST 2: TaxEngine Resolution for Order with 2% Commission, Missing Platform Fee, and Shipping ===\n";
$taxEngine = app(TaxEngine::class);

// Create simulated models
$mockOrder = new Order();
$mockOrder->id = 19521;
$mockOrder->amount = 309.00;
$mockOrder->sub_total = 177.97;
$mockOrder->tax_amount = 32.03;
$mockOrder->shipping_amount = 99.00;

$mockStore = new Store();
$mockStore->is_manage_shipping = 0;
$mockStore->vendor_managed_shipping = 0;
$mockStore->country = 'IN';
$mockStore->state = '30'; // Rajasthan

$mockRevenue = new Revenue();
$mockRevenue->sub_amount = 177.97;
$mockRevenue->fee = 3.56; // 2% of 177.97
$mockRevenue->platform_fee = null;
$mockRevenue->commission_fee = null;
$mockRevenue->shipping_cost = null;

$result = $taxEngine->resolve($mockOrder, $mockRevenue, $mockStore);

echo "Lines Count: " . count($result['lines']) . "\n";
foreach ($result['lines'] as $line) {
    echo sprintf(
        " - %s: Taxable = %.2f, Tax = %.2f, Total = %.2f\n",
        $line['name'],
        $line['taxable_amount'],
        $line['tax_amount'],
        $line['total_amount']
    );
}

echo "Subtotal: " . $result['subtotal'] . "\n";
echo "Tax Amount: " . $result['tax_amount'] . "\n";
echo "Grand Total: " . $result['grand_total'] . "\n";

assert(count($result['lines']) === 3, "Expected 3 lines (Platform Fee, Commission Fee, Shipping Fee)");
$lineNames = array_column($result['lines'], 'name');
assert(str_contains(implode(',', $lineNames), 'Platform Fee'), "Platform Fee line missing");
assert(str_contains(implode(',', $lineNames), 'Commission Fee'), "Marketplace Commission Fee line missing");
assert(str_contains(implode(',', $lineNames), 'Shipping Fee'), "Shipping Fee line missing");

echo "=== TEST 3: Regenerate All Invoices Number Padding & Lines ===\n";
DB::beginTransaction();
try {
    $count = $generator->regenerateAllInvoices();
    echo "Regenerated {$count} invoices.\n";

    $sampleDoc = Document::query()->where('number', 'REGEXP', 'SIN[A-Z]*[0-9]{3}')->first();
    if ($sampleDoc) {
        echo "Sample regenerated invoice number: {$sampleDoc->number}\n";
        echo "Sample lines count: " . count($sampleDoc->lines) . "\n";
    }
} finally {
    DB::rollBack();
    echo "DB transaction rolled back.\n";
}

echo "=== ALL TESTS PASSED! ===\n";
