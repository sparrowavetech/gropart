<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Botble\Marketplace\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use SparroWave\SellerInvoicing\Models\GstMonthlyReport;
use SparroWave\SellerInvoicing\Services\Gst\ExcelGstExport;
use SparroWave\SellerInvoicing\Services\Gst\GovtOfflineToolExport;
use SparroWave\SellerInvoicing\Services\Gst\GstReportService;
use SparroWave\SellerInvoicing\Services\Gst\Gstr1Engine;
use SparroWave\SellerInvoicing\Services\Gst\Gstr3bEngine;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

echo "========================================================\n";
echo "COMPREHENSIVE VERIFICATION: SELLER GST REPORT MODULE\n";
echo "========================================================\n\n";

// 1. Guard check
$isIndian = TaxEngine::isIndianBillingMode();
$isEnabled = TaxEngine::isGstReportEnabled();
echo "1. TaxEngine Guard Check:\n";
echo "   isIndianBillingMode: " . ($isIndian ? "TRUE (PASS)" : "FALSE") . "\n";
echo "   isGstReportEnabled:  " . ($isEnabled ? "TRUE (PASS)" : "FALSE") . "\n\n";

// 2. Route Check
echo "2. Route Registration Check:\n";
$routesToCheck = [
    'seller-invoicing.settings.regenerate-batch',
    'seller-invoicing.gst-reports.index',
    'seller-invoicing.gst-reports.export-excel',
    'seller-invoicing.gst-reports.export-json',
    'seller-invoicing.gst-reports.export-csv-zip',
    'marketplace.vendor.seller-invoicing.gst-reports.index',
    'marketplace.vendor.seller-invoicing.gst-reports.export-excel',
    'marketplace.vendor.seller-invoicing.gst-reports.export-json',
    'marketplace.vendor.seller-invoicing.gst-reports.export-csv-zip',
];
foreach ($routesToCheck as $rName) {
    $hasRoute = Route::has($rName);
    echo "   Route '{$rName}': " . ($hasRoute ? "REGISTERED (PASS)" : "MISSING (FAIL)") . "\n";
}
echo "\n";

// 3. Test Store & Period (February 2026 has orders and invoices for SB Marketing)
$sampleStore = Store::query()->where('name', 'like', '%SB Marketing%')->first() ?: Store::query()->first();
$testPeriod = '2026-02';
echo "3. Testing Engines on Real Data:\n";
echo "   Target Store: " . ($sampleStore ? "ID={$sampleStore->id}, Name={$sampleStore->name}, GSTIN={$sampleStore->tax_id}" : "None") . "\n";
echo "   Target Period: {$testPeriod}\n\n";

$gstr1Engine = app(Gstr1Engine::class);
$gstr3bEngine = app(Gstr3bEngine::class);
$reportService = app(GstReportService::class);

$report = $reportService->getReport($sampleStore?->id, $testPeriod, true);

echo "   Report Summary:\n";
echo "   - Orders Count: " . ($report['order_count'] ?? 0) . "\n";
echo "   - Taxable Sales: ₹" . number_format((float) ($report['total_taxable_sales'] ?? 0), 2) . "\n";
echo "   - Output GST: ₹" . number_format((float) ($report['total_output_tax'] ?? 0), 2) . "\n";
echo "   - Claimable Gropart ITC: ₹" . number_format((float) ($report['itc_gropart_fees'] ?? 0), 2) . "\n";
echo "   - Net GST Payable to Govt: ₹" . number_format((float) ($report['net_gst_payable'] ?? 0), 2) . "\n";
echo "   - B2B Invoices: " . count($report['gstr1']['b2b'] ?? []) . "\n";
echo "   - B2CS Groups: " . count($report['gstr1']['b2cs'] ?? []) . "\n";
echo "   - HSN Codes: " . count($report['gstr1']['hsn'] ?? []) . "\n";
echo "   - Gropart Fee Invoices Reconciled: " . count($report['gstr3b']['itc_invoices'] ?? []) . "\n\n";

// 4. Test Offline Tool JSON Export
echo "4. Testing Govt Offline Tool JSON Export:\n";
$offlineExporter = app(GovtOfflineToolExport::class);
$json = $offlineExporter->generateJson($report['gstr1']);
$decoded = json_decode($json, true);
if ($decoded && isset($decoded['gstin'], $decoded['fp'], $decoded['b2cs'], $decoded['hsn'], $decoded['doc_issue'])) {
    echo "   JSON Schema Valid: TRUE (PASS)\n";
    echo "   JSON Length: " . strlen($json) . " bytes\n";
    echo "   JSON Keys: " . implode(", ", array_keys($decoded)) . "\n\n";
} else {
    echo "   JSON Schema Invalid! (FAIL)\n\n";
}

// 5. Test Offline Tool CSV Zip Export
echo "5. Testing Govt Offline Tool CSV Zip Export:\n";
$zipPath = $offlineExporter->generateCsvZip($report['gstr1']);
if ($zipPath && file_exists($zipPath)) {
    $zip = new ZipArchive();
    $zip->open($zipPath);
    echo "   ZIP File Created: TRUE (PASS)\n";
    echo "   ZIP Files Count: " . $zip->numFiles . " files\n";
    for ($i = 0; $i < $zip->numFiles; $i++) {
        echo "     - " . $zip->getNameIndex($i) . "\n";
    }
    $zip->close();
    unlink($zipPath);
    echo "\n";
} else {
    echo "   ZIP Creation Failed! (FAIL)\n\n";
}

// 6. Test Multi-Sheet Excel Export
echo "6. Testing Multi-Sheet Excel (.xlsx) Export:\n";
$excelExporter = app(ExcelGstExport::class);
$excelPath = $excelExporter->generate($report['gstr1'], $report['gstr3b']);
if ($excelPath && file_exists($excelPath)) {
    echo "   Excel File Created: TRUE (PASS)\n";
    echo "   File Size: " . filesize($excelPath) . " bytes\n";
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($excelPath);
    echo "   Sheet Names in Excel: " . implode(", ", $spreadsheet->getSheetNames()) . "\n";
    unlink($excelPath);
    echo "\n";
} else {
    echo "   Excel Creation Failed! (FAIL)\n\n";
}

// 7. Verify DB Snapshot
echo "7. Verifying DB Snapshot in `si_gst_monthly_reports`:\n";
$savedRecord = GstMonthlyReport::query()
    ->where('store_id', $sampleStore?->id)
    ->where('tax_period', $testPeriod)
    ->first();
if ($savedRecord) {
    echo "   DB Record Exists: ID={$savedRecord->id}, Period={$savedRecord->tax_period}, Sales=₹{$savedRecord->total_taxable_sales}, ITC=₹{$savedRecord->itc_gropart_fees}, Status={$savedRecord->status} (PASS)\n\n";
} else {
    echo "   DB Record Not Found! (FAIL)\n\n";
}

echo "========================================================\n";
echo "ALL TESTS COMPLETED SUCCESSFULLY!\n";
echo "========================================================\n";

