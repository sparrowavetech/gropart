<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== GHOST INCOMPLETE ORDERS CLEANUP ===\n";

$startTime = microtime(true);

// 1. Audit before
$totalOrdersBefore = DB::table('ec_orders')->count();
$finishedOrdersBefore = DB::table('ec_orders')->where('is_finished', 1)->count();
$incompleteOrdersBefore = DB::table('ec_orders')->where('is_finished', 0)->count();

echo "Total orders before: {$totalOrdersBefore}\n";
echo "Finished orders before: {$finishedOrdersBefore}\n";
echo "Incomplete orders before: {$incompleteOrdersBefore}\n\n";

// 2. Identify ghost IDs
$ghostOrdersQuery = DB::table('ec_orders')
    ->where('is_finished', 0)
    ->where(function ($q) {
        $q->whereNull('user_id')->orWhere('user_id', 0);
    })
    ->whereNotExists(function ($q) {
        $q->select(DB::raw(1))
            ->from('ec_order_addresses')
            ->whereColumn('ec_order_addresses.order_id', 'ec_orders.id')
            ->whereNotNull('name')
            ->where('name', '!=', '');
    });

$ghostCount = $ghostOrdersQuery->count();
echo "Identified ghost incomplete orders to delete: {$ghostCount}\n";

if ($ghostCount === 0) {
    echo "No ghost incomplete orders found. Nothing to do.\n";
    exit(0);
}

// 3. Create backup tables
echo "\nCreating backup tables...\n";
DB::statement("DROP TABLE IF EXISTS backup_ghost_incomplete_orders_20261108");
DB::statement("
    CREATE TABLE backup_ghost_incomplete_orders_20261108 AS
    SELECT * FROM ec_orders
    WHERE is_finished = 0
      AND (user_id IS NULL OR user_id = 0)
      AND NOT EXISTS (
          SELECT 1 FROM ec_order_addresses
          WHERE ec_order_addresses.order_id = ec_orders.id
            AND name IS NOT NULL AND name != ''
      )
");

$backupOrdersCount = DB::table('backup_ghost_incomplete_orders_20261108')->count();
echo "Backed up orders count: {$backupOrdersCount}\n";

if ($backupOrdersCount !== $ghostCount) {
    throw new Exception("Mismatch in backup count ({$backupOrdersCount} vs {$ghostCount}). Aborting!");
}

DB::statement("DROP TABLE IF EXISTS backup_ghost_incomplete_order_products_20261108");
DB::statement("
    CREATE TABLE backup_ghost_incomplete_order_products_20261108 AS
    SELECT * FROM ec_order_product
    WHERE order_id IN (SELECT id FROM backup_ghost_incomplete_orders_20261108)
");

$backupProductsCount = DB::table('backup_ghost_incomplete_order_products_20261108')->count();
echo "Backed up order products count: {$backupProductsCount}\n";

// 4. Safe Deletion in chunks to avoid lock contention
echo "\nExecuting safe deletion in batches of 2000...\n";

$deletedProductsTotal = 0;
$deletedHistoriesTotal = 0;
$deletedOrdersTotal = 0;

$chunkSize = 2000;
$offset = 0;

while (true) {
    $ids = DB::table('backup_ghost_incomplete_orders_20261108')
        ->select('id')
        ->offset($offset)
        ->limit($chunkSize)
        ->pluck('id')
        ->toArray();

    if (empty($ids)) {
        break;
    }

    DB::transaction(function () use ($ids, &$deletedProductsTotal, &$deletedHistoriesTotal, &$deletedOrdersTotal) {
        $p = DB::table('ec_order_product')->whereIn('order_id', $ids)->delete();
        $h = DB::table('ec_order_histories')->whereIn('order_id', $ids)->delete();
        $o = DB::table('ec_orders')->whereIn('id', $ids)->delete();

        $deletedProductsTotal += $p;
        $deletedHistoriesTotal += $h;
        $deletedOrdersTotal += $o;
    });

    $offset += $chunkSize;
    echo "Progress: deleted {$deletedOrdersTotal} / {$ghostCount} orders...\n";
}

// 5. Verification after
echo "\n=== AUDIT AFTER DELETION ===\n";
$totalOrdersAfter = DB::table('ec_orders')->count();
$finishedOrdersAfter = DB::table('ec_orders')->where('is_finished', 1)->count();
$incompleteOrdersAfter = DB::table('ec_orders')->where('is_finished', 0)->count();

echo "Total orders after: {$totalOrdersAfter}\n";
echo "Finished orders after: {$finishedOrdersAfter} (diff: " . ($finishedOrdersAfter - $finishedOrdersBefore) . ")\n";
echo "Incomplete orders after: {$incompleteOrdersAfter}\n";
echo "Deleted ghost orders: {$deletedOrdersTotal}\n";
echo "Deleted child products: {$deletedProductsTotal}\n";
echo "Deleted child histories: {$deletedHistoriesTotal}\n";
echo "Backup table: backup_ghost_incomplete_orders_20261108 ({$backupOrdersCount} records)\n";

$elapsed = round(microtime(true) - $startTime, 2);
echo "\nCLEANUP COMPLETED SUCCESSFULLY in {$elapsed}s!\n";
