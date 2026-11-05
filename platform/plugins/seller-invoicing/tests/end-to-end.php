<?php

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Models\Revenue;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Models\OrderSnapshot;
use SparroWave\SellerInvoicing\Services\GenerateSellerInvoiceService;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Running End-to-End Invoice Generation & Storage test...\n";

DB::beginTransaction();

try {
    $order = Order::query()
        ->whereNotNull('store_id')
        ->whereHas('store')
        ->whereIn('status', ['completed', 'processing'])
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('mp_customer_revenues')
                ->whereColumn('mp_customer_revenues.order_id', 'ec_orders.id')
                ->where('type', RevenueTypeEnum::ADD_AMOUNT);
        })
        ->first();

    if (! $order) {
        throw new RuntimeException("No completed marketplace order with revenue found for test.");
    }

    echo "Found test order #{$order->id} (Store ID: {$order->store_id})\n";

    $generator = app(InvoiceGeneratorService::class);
    $document = $generator->generateForOrder($order, true);

    assert($document instanceof Document, 'Document was not created');
    assert(! empty($document->number), 'Document has no number');
    assert($document->status === 'issued', 'Document status is not issued');
    assert($document->grand_total > 0, 'Document grand total is 0');
    assert(count($document->lines) > 0, 'Document has no line items');

    echo "1. Invoice Document created: {$document->number} (Grand Total: {$document->grand_total} {$document->currency})\n";

    // Test PDF generation and storage in marketplace folder
    $pdfService = app(GenerateSellerInvoiceService::class)->document($document);
    $filePath = $pdfService->ensureSaved();

    assert(File::exists($filePath), "PDF file was not saved at {$filePath}");
    assert(str_contains($filePath, 'marketplace/seller-invoices'), "PDF path does not contain marketplace/seller-invoices: {$filePath}");

    $bytes = file_get_contents($filePath);
    assert(str_starts_with($bytes, '%PDF-'), 'Saved file is not a valid PDF binary');
    assert(strlen($bytes) > 1000, 'Saved PDF is too small');

    echo "2. PDF successfully saved to storage ({$filePath}, " . strlen($bytes) . " bytes): PASS\n";

    // Clean up created file
    if (File::exists($filePath)) {
        File::delete($filePath);
    }

    echo "ALL END-TO-END TESTS PASSED!\n";
} finally {
    DB::rollBack();
    echo "Database transaction rolled back. No permanent records written.\n";
}

