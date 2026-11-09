<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Revenue;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;

$generator = app(InvoiceGeneratorService::class);

echo "=======================================================\n";
echo "TESTING ACTUAL INVOICE REGENERATION VIA SETTINGS\n";
echo "=======================================================\n";

$targetDoc = Document::query()->first();
if (!$targetDoc) {
    echo "No document found!\n";
    exit(1);
}
echo "Target Document #{$targetDoc->id}: Number={$targetDoc->number}, OrderID={$targetDoc->order_id}\n";

// 1. Set to EXCLUSIVE and regenerate
echo "\n--- 1. Switching setting to EXCLUSIVE and regenerating all invoices ---\n";
setting()->set('seller_invoicing_fee_tax_type', 'exclusive');
setting()->save();
$count = $generator->regenerateAllInvoices();
echo "Regenerated: $count documents.\n";

$docExc = Document::query()->find($targetDoc->id);
echo "Result in DB (Exclusive):\n";
echo "  Subtotal: ₹{$docExc->subtotal}\n";
echo "  Tax Amount: ₹{$docExc->tax_amount}\n";
echo "  Grand Total: ₹{$docExc->grand_total}\n";
foreach ($docExc->lines as $l) {
    echo "  [{$l['name']}]: Taxable=₹{$l['taxable_amount']}, Tax=₹{$l['tax_amount']}, Total=₹{$l['total_amount']}\n";
}

// 2. Set to INCLUSIVE and regenerate
echo "\n--- 2. Switching setting to INCLUSIVE and regenerating all invoices ---\n";
setting()->set('seller_invoicing_fee_tax_type', 'inclusive');
setting()->save();
$count = $generator->regenerateAllInvoices();
echo "Regenerated: $count documents.\n";

$docInc = Document::query()->find($targetDoc->id);
echo "Result in DB (Inclusive):\n";
echo "  Subtotal: ₹{$docInc->subtotal}\n";
echo "  Tax Amount: ₹{$docInc->tax_amount}\n";
echo "  Grand Total: ₹{$docInc->grand_total}\n";
foreach ($docInc->lines as $l) {
    echo "  [{$l['name']}]: Taxable=₹{$l['taxable_amount']}, Tax=₹{$l['tax_amount']}, Total=₹{$l['total_amount']}\n";
}

echo "\n--- COMPARISON ---\n";
echo "Exclusive Grand Total: ₹{$docExc->grand_total} vs Inclusive Grand Total: ₹{$docInc->grand_total}\n";
echo "Exclusive Subtotal:    ₹{$docExc->subtotal} vs Inclusive Subtotal:    ₹{$docInc->subtotal}\n";
echo "Exclusive Tax:         ₹{$docExc->tax_amount} vs Inclusive Tax:         ₹{$docInc->tax_amount}\n";

