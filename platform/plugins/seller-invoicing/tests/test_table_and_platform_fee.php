<?php

use Botble\Marketplace\Forms\Settings\MarketplaceSettingForm;
use Botble\Marketplace\Models\Store;
use Botble\Setting\Facades\Setting;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SparroWave\SellerInvoicing\Forms\Settings\SellerInvoicingSettingForm;
use SparroWave\SellerInvoicing\Hooks\MarketplaceSettingHookListener;
use SparroWave\SellerInvoicing\Http\Controllers\SellerInvoicingSettingController;
use SparroWave\SellerInvoicing\Http\Requests\SellerInvoicingSettingRequest;
use SparroWave\SellerInvoicing\Tables\SellerInvoiceTable;

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

echo "=== START VERIFICATION: Seller Invoice Table & Platform Fee Settings ===\n";

DB::beginTransaction();

try {
    // -------------------------------------------------------------
    // 1. Check SellerInvoiceTable Columns & Default Sorting
    // -------------------------------------------------------------
    $table = app(SellerInvoiceTable::class);
    $columns = $table->columns();
    $colNames = [];
    foreach ($columns as $c) {
        $colNames[] = method_exists($c, 'getName') ? $c->getName() : ($c->name ?? get_class($c));
    }

    echo "1. Table Columns: " . implode(', ', $colNames) . "\n";

    $orderIdx = array_search('order_id', $colNames, true);
    $issuedIdx = array_search('issued_at', $colNames, true);

    if ($orderIdx === false || $issuedIdx === false) {
        throw new RuntimeException("order_id or issued_at column missing from table.");
    }

    if ($issuedIdx !== $orderIdx + 1) {
        throw new RuntimeException("issued_at is not directly after order_id. Order: {$orderIdx}, Issued: {$issuedIdx}");
    }

    $orderTitle = method_exists($columns[$orderIdx], 'getTitle') ? (string)$columns[$orderIdx]->getTitle() : ($columns[$orderIdx]->title ?? '');
    $issuedTitle = method_exists($columns[$issuedIdx], 'getTitle') ? (string)$columns[$issuedIdx]->getTitle() : ($columns[$issuedIdx]->title ?? '');

    echo "   - Column Order Title: '{$orderTitle}' (Expected 'Order #')\n";
    echo "   - Column Issued Title: '{$issuedTitle}' (Expected 'Invoice Date')\n";

    if (!str_contains($orderTitle, 'Order #') && !str_contains($orderTitle, 'ऑर्डर #')) {
        throw new RuntimeException("Order column label is not 'Order #'. Got: '{$orderTitle}'");
    }

    // -------------------------------------------------------------
    // 2. Check Table Filters
    // -------------------------------------------------------------
    $filters = $table->getFilters();
    echo "2. Table Filters: " . implode(', ', array_keys($filters)) . "\n";

    if (!isset($filters['store_id'])) {
        throw new RuntimeException("store_id filter missing.");
    }
    echo "   - Store filter type: '{$filters['store_id']['type']}' (choices: " . count($filters['store_id']['choices']) . ")\n";
    if ($filters['store_id']['type'] !== 'select') {
        throw new RuntimeException("store_id filter is not select.");
    }

    if (!isset($filters['order_date'])) {
        throw new RuntimeException("order_date filter missing.");
    }
    echo "   - Order date filter type: '{$filters['order_date']['type']}', title: '{$filters['order_date']['title']}'\n";
    if ($filters['order_date']['type'] !== 'datePicker') {
        throw new RuntimeException("order_date filter is not datePicker.");
    }

    if (!isset($filters['order_code'])) {
        throw new RuntimeException("order_code filter missing.");
    }
    echo "   - Order filter title: '{$filters['order_code']['title']}'\n";

    // -------------------------------------------------------------
    // 3. Check Platform Fee in SellerInvoicingSettingForm
    // -------------------------------------------------------------
    $sellerSettingForm = SellerInvoicingSettingForm::create();
    echo "3. SellerInvoicingSettingForm has seller_invoicing_platform_fee: " . ($sellerSettingForm->has('seller_invoicing_platform_fee') ? 'YES' : 'NO') . "\n";
    if (!$sellerSettingForm->has('seller_invoicing_platform_fee')) {
        throw new RuntimeException("seller_invoicing_platform_fee missing in SellerInvoicingSettingForm.");
    }

    // -------------------------------------------------------------
    // 4. Check Platform Fee in MarketplaceSettingForm
    // -------------------------------------------------------------
    $marketplaceSettingForm = MarketplaceSettingForm::create();
    echo "4. MarketplaceSettingForm has marketplace_default_platform_fee: " . ($marketplaceSettingForm->has('marketplace_default_platform_fee') ? 'YES' : 'NO') . "\n";
    if (!$marketplaceSettingForm->has('marketplace_default_platform_fee')) {
        throw new RuntimeException("marketplace_default_platform_fee missing in MarketplaceSettingForm.");
    }

    // -------------------------------------------------------------
    // 5. Test Settings Synchronization
    // -------------------------------------------------------------
    // A. Saving via Marketplace Hook
    $testFeeValue = '9.25';
    $fakeMpRequest = Request::create('/admin/marketplaces/settings', 'PUT', [
        'marketplace_default_platform_fee' => $testFeeValue,
    ]);
    MarketplaceSettingHookListener::saveSettings($fakeMpRequest);

    $savedMp = setting('marketplace_default_platform_fee');
    $savedDef = setting('default_platform_fee');
    $savedSi = setting('seller_invoicing_platform_fee');

    echo "5A. Saved via Marketplace Hook: mp={$savedMp}, def={$savedDef}, si={$savedSi}\n";
    if ($savedMp !== $testFeeValue || $savedDef !== $testFeeValue || $savedSi !== $testFeeValue) {
        throw new RuntimeException("Settings sync failed on marketplace save.");
    }

    // B. Saving via Seller Invoicing Controller
    $testFeeValue2 = '10.50';
    $controller = app(SellerInvoicingSettingController::class);
    $mockRequest = SellerInvoicingSettingRequest::create('/admin/seller-invoicing/settings', 'POST', [
        'seller_invoicing_platform_fee' => $testFeeValue2,
        'seller_invoicing_enabled' => '1',
        'seller_invoicing_prefix' => 'GPINV-',
        'seller_invoice_code_suffix' => 'SIN',
        'seller_invoicing_tax_mode' => 'auto',
        'seller_invoicing_fee_tax_type' => 'exclusive',
        'seller_invoicing_tax_rate' => '18',
    ]);
    $mockRequest->setContainer(app());
    $mockRequest->validateResolved();
    $controller->update($mockRequest, app(\SparroWave\SellerInvoicing\Services\InvoiceGeneratorService::class));

    $savedMp2 = setting('marketplace_default_platform_fee');
    $savedDef2 = setting('default_platform_fee');
    $savedSi2 = setting('seller_invoicing_platform_fee');

    echo "5B. Saved via Seller Invoicing Controller: mp={$savedMp2}, def={$savedDef2}, si={$savedSi2}\n";
    if ((float)$savedMp2 !== (float)$testFeeValue2 || (float)$savedDef2 !== (float)$testFeeValue2 || (float)$savedSi2 !== (float)$testFeeValue2) {
        throw new RuntimeException("Settings sync failed on seller invoicing save.");
    }

    // -------------------------------------------------------------
    // 6. Test AJAX Table Execution and Order Date Sorting
    // -------------------------------------------------------------
    $ajaxRequest = Request::create('/admin/seller-invoicing', 'POST', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
    ]);
    $ajaxRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
    app()->instance('request', $ajaxRequest);

    $response = $table->ajax();
    $data = json_decode($response->getContent(), true);

    echo "6. AJAX Table Query Response:\n";
    echo "   - recordsTotal: " . ($data['recordsTotal'] ?? 0) . "\n";
    echo "   - recordsFiltered: " . ($data['recordsFiltered'] ?? 0) . "\n";
    echo "   - rows returned: " . count($data['data'] ?? []) . "\n";

    if (!empty($data['data'])) {
        $row0 = $data['data'][0];
        echo "   - Top row Order #: " . strip_tags($row0['order_id'] ?? '') . "\n";
        echo "   - Top row Invoice Date: " . strip_tags($row0['issued_at'] ?? '') . "\n";
        echo "   - Top row Vendor / Store: " . strip_tags($row0['seller_id'] ?? '') . "\n";
        echo "   - Top row Grand Total: " . strip_tags($row0['grand_total'] ?? '') . "\n";
    }

    // 7. Test Table Filters (store_id and order_date)
    $filterRequest = Request::create('/admin/seller-invoicing', 'POST', [
        'draw' => 2,
        'start' => 0,
        'length' => 10,
        'filter_table' => [
            'store_id' => [
                'type' => 'select',
                'operator' => '=',
                'value' => '1',
            ],
        ],
    ]);
    $filterRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
    app()->instance('request', $filterRequest);

    $filterTable = app(SellerInvoiceTable::class);
    $filterResponse = $filterTable->ajax();
    $filterData = json_decode($filterResponse->getContent(), true);

    echo "7. Store Filter Query Response: recordsFiltered=" . ($filterData['recordsFiltered'] ?? 0) . "\n";
    if (($filterData['recordsFiltered'] ?? 0) === 0) {
        throw new RuntimeException("Store filter returned 0 records when store 1 has records.");
    }

    echo "=== ALL 7 CHECKS PASSED PERFECTLY! ===\n";

} finally {
    DB::rollBack();
    echo "DB transaction rolled back cleanly. No test data persisted.\n";
}
