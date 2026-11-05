<?php

spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'Botble\\Ecommerce\\')) {
        $file = __DIR__ . '/../platform/plugins/ecommerce/src/' . str_replace('\\', '/', substr($class, 17)) . '.php';
        if (file_exists($file)) require_once $file;
    }
    if (str_starts_with($class, 'Botble\\Marketplace\\')) {
        $file = __DIR__ . '/../platform/plugins/marketplace/src/' . str_replace('\\', '/', substr($class, 19)) . '.php';
        if (file_exists($file)) require_once $file;
    }
    if (str_starts_with($class, 'SparroWave\\SellerInvoicing\\')) {
        $file = __DIR__ . '/../platform/plugins/seller-invoicing/src/' . str_replace('\\', '/', substr($class, 27)) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config([
    'database.connections.mysql.host' => '',
    'database.connections.mysql.unix_socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
]);
\Illuminate\Support\Facades\DB::purge('mysql');

$generator = app(SparroWave\SellerInvoicing\Services\InvoiceGeneratorService::class);
echo "Regenerating all invoices...\n";
$count = $generator->regenerateAllInvoices();
echo "Regenerated: $count documents.\n";

$sample = SparroWave\SellerInvoicing\Models\Document::query()->with('order')->take(5)->get();
foreach ($sample as $d) {
    echo "ID: {$d->id} | Number: {$d->number} | Order #{$d->order_id} placed: {$d->order?->created_at?->format('d-m-Y')} | issued_at: {$d->issued_at?->format('d-m-Y H:i')} | PDF: {$d->pdf_path}\n";
}

