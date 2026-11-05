<?php

spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'SparroWave\\SellerInvoicing\\')) {
        $file = __DIR__ . '/../platform/plugins/seller-invoicing/src/' . str_replace('\\', '/', substr($class, 27)) . '.php';
        if (file_exists($file)) require_once $file;
    }
    if (str_starts_with($class, 'Botble\\Ecommerce\\')) {
        $file = __DIR__ . '/../platform/plugins/ecommerce/src/' . str_replace('\\', '/', substr($class, 17)) . '.php';
        if (file_exists($file)) require_once $file;
    }
    if (str_starts_with($class, 'Botble\\Marketplace\\')) {
        $file = __DIR__ . '/../platform/plugins/marketplace/src/' . str_replace('\\', '/', substr($class, 19)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

use Illuminate\Contracts\Console\Kernel;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Services\GenerateSellerInvoiceService;
use SparroWave\SellerInvoicing\Tables\VendorSellerInvoiceTable;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config([
    'database.connections.mysql.host' => '',
    'database.connections.mysql.unix_socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
]);
\Illuminate\Support\Facades\DB::purge('mysql');
if (file_exists(__DIR__ . '/../platform/plugins/ecommerce/helpers/common.php')) {
    require_once __DIR__ . '/../platform/plugins/ecommerce/helpers/common.php';
}

echo "=== 1. Testing PDF Generation with Updated seller-invoice.tpl ===\n";
$service = new GenerateSellerInvoiceService();
$preview = $service->preview();
$binary = method_exists($preview, 'getFile') ? file_get_contents($preview->getFile()->getPathname()) : $preview->getContent();

assert(str_starts_with($binary, '%PDF-'), "PDF preview is not a valid PDF");
echo "PDF generated successfully: " . strlen($binary) . " bytes\n";

echo "\n=== 2. Testing VendorSellerInvoiceTable Configuration ===\n";
$table = app(VendorSellerInvoiceTable::class);

// Verify ForVendor trait
$traits = class_uses_recursive(VendorSellerInvoiceTable::class);
assert(in_array(\Botble\Marketplace\Tables\Traits\ForVendor::class, $traits, true), "ForVendor trait missing!");
echo "- ForVendor trait present: PASS\n";

// Verify Export button
$buttons = $table->getDefaultButtons();
echo "- Default buttons: " . implode(', ', $buttons) . "\n";
assert(in_array('export', $buttons, true), "Export button missing from table!");
echo "- Export button present: PASS\n";

// Verify Columns
$columns = $table->columns();
$colNames = [];
foreach ($columns as $c) {
    $colNames[] = method_exists($c, 'getName') ? $c->getName() : ($c->name ?? get_class($c));
}
echo "- Columns: " . implode(', ', $colNames) . "\n";
assert(in_array('number', $colNames, true), "number column missing!");
assert(in_array('order_id', $colNames, true), "order_id column missing!");
assert(in_array('issued_at', $colNames, true), "issued_at column missing!");
assert(in_array('grand_total', $colNames, true), "grand_total column missing!");

// Verify Filters
$filters = $table->getFilters();
echo "- Filters: " . implode(', ', array_keys($filters)) . "\n";
assert(isset($filters['order_date']), "order_date filter missing!");
assert(isset($filters['created_at']), "created_at (invoice_date) filter missing!");
assert(isset($filters['order_code']), "order_code filter missing!");
assert(isset($filters['number']), "number filter missing!");
assert(isset($filters['status']), "status filter missing!");
echo "- All required filters present: PASS\n";

// Verify Template Content does not contain old amt-cell
$tplContent = $service->getContent();
assert(!str_contains($tplContent, 'amt-cell'), "Template still contains amt-cell!");
echo "- Template free of old amt-cell: PASS\n";

echo "\nALL LOCAL TESTS PASSED CLEANLY!\n";
