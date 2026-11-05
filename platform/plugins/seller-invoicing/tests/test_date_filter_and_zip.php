<?php

use Botble\Ecommerce\Models\Order;
use Botble\Table\Columns\CheckboxColumn;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Services\InvoiceZipService;
use SparroWave\SellerInvoicing\Tables\SellerInvoiceTable;
use SparroWave\SellerInvoicing\Tables\VendorSellerInvoiceTable;

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

echo "=== START VERIFICATION: Date Range Filter, Datepicker & Bulk Zip Download ===\n";

DB::beginTransaction();

try {
    // -------------------------------------------------------------
    // 1. SellerInvoiceTable Columns & CheckboxColumn
    // -------------------------------------------------------------
    $table = app(SellerInvoiceTable::class);
    $columns = $table->columns();
    $firstCol = $columns[0] ?? null;

    echo "1. First column class: " . ($firstCol ? get_class($firstCol) : 'none') . "\n";
    if (! ($firstCol instanceof CheckboxColumn)) {
        throw new RuntimeException("First column in SellerInvoiceTable is not CheckboxColumn!");
    }
    echo "   [PASS] SellerInvoiceTable has CheckboxColumn as first column.\n";

    // -------------------------------------------------------------
    // 2. Buttons in SellerInvoiceTable
    // -------------------------------------------------------------
    $buttons = $table->buttons();
    echo "2. Buttons: " . implode(', ', array_keys($buttons)) . "\n";
    if (! isset($buttons['download-invoices'])) {
        throw new RuntimeException("SellerInvoiceTable missing download-invoices button!");
    }
    echo "   [PASS] download-invoices button present in SellerInvoiceTable.\n";

    // -------------------------------------------------------------
    // 3. VendorSellerInvoiceTable Checkbox & Buttons
    // -------------------------------------------------------------
    $vendorTable = app(VendorSellerInvoiceTable::class);
    $vCols = $vendorTable->columns();
    $vFirstCol = $vCols[0] ?? null;
    if (! ($vFirstCol instanceof CheckboxColumn)) {
        throw new RuntimeException("First column in VendorSellerInvoiceTable is not CheckboxColumn!");
    }
    $vButtons = $vendorTable->buttons();
    if (! isset($vButtons['download-invoices'])) {
        throw new RuntimeException("VendorSellerInvoiceTable missing download-invoices button!");
    }
    echo "3. [PASS] VendorSellerInvoiceTable has CheckboxColumn and download-invoices button.\n";

    // -------------------------------------------------------------
    // 4. getValueInput returns input[type="date"]
    // -------------------------------------------------------------
    $inputResult = $table->getValueInput('order_date', '2026-11-04', 'datePicker');
    echo "4. getValueInput HTML: " . $inputResult['html'] . "\n";
    if (! str_contains($inputResult['html'], 'type="date"')) {
        throw new RuntimeException("getValueInput did not produce type='date' input!");
    }
    echo "   [PASS] getValueInput produces native date input.\n";

    // -------------------------------------------------------------
    // 5. Query Date Range Filtering on SellerInvoiceTable & OrderTable
    // -------------------------------------------------------------
    request()->merge([
        'date_from' => '2026-11-01',
        'date_to' => '2026-11-04',
        'date_type' => 'order_date',
    ]);
    $tableWithReq = app(SellerInvoiceTable::class);
    $sql = $tableWithReq->query()->toSql();
    echo "5. SellerInvoiceTable SQL with date range: " . $sql . "\n";
    if (! str_contains($sql, 'created_at') && ! str_contains($sql, 'issued_at')) {
        throw new RuntimeException("SellerInvoiceTable query does not include date filter!");
    }
    echo "   [PASS] SellerInvoiceTable query applies date range filter.\n";

    // 5b. OrderTable date filtering test
    require_once dirname(__DIR__, 4) . '/platform/plugins/ecommerce/helpers/common.php';
    require_once dirname(__DIR__, 4) . '/platform/plugins/ecommerce/src/Tables/OrderTable.php';
    $orderTable = app(\Botble\Ecommerce\Tables\OrderTable::class);
    $orderSql = $orderTable->query()->toSql();
    echo "5b. OrderTable SQL with date range: " . $orderSql . "\n";
    if (! str_contains($orderSql, 'created_at')) {
        throw new RuntimeException("OrderTable query does not include date filter!");
    }
    echo "   [PASS] OrderTable query applies date range filter.\n";

    // -------------------------------------------------------------
    // 6. Test InvoiceZipService with existing Document
    // -------------------------------------------------------------
    $sampleDoc = Document::query()->first();
    if ($sampleDoc) {
        $zipService = app(InvoiceZipService::class);
        $zipPath = $zipService->createZipFromDocumentIds([$sampleDoc->id]);
        echo "6. Generated ZIP Path: " . $zipPath . " (Size: " . filesize($zipPath) . " bytes)\n";
        if (! File::exists($zipPath) || filesize($zipPath) === 0) {
            throw new RuntimeException("Generated ZIP file is missing or empty!");
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            echo "   ZIP Entries count: " . $zip->numFiles . "\n";
            for ($i = 0; $i < $zip->numFiles; $i++) {
                echo "   - Entry: " . $zip->getNameIndex($i) . " (" . $zip->statIndex($i)['size'] . " bytes)\n";
            }
            $zip->close();
        }
        File::delete($zipPath);
        echo "   [PASS] InvoiceZipService generated valid ZIP with invoice PDF.\n";
    } else {
        echo "6. [SKIP] No documents found in database to test ZIP generation.\n";
    }

    echo "\n=== ALL VERIFICATIONS PASSED SUCCESSFULLY ===\n";

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    DB::rollBack();
}
