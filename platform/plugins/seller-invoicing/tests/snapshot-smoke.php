<?php

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use SparroWave\SellerInvoicing\Models\OrderSnapshot;
use SparroWave\SellerInvoicing\Supports\SnapshotBuilder;
use SparroWave\SellerInvoicing\Supports\RevenueReconciler;
use SparroWave\SellerInvoicing\Supports\SnapshotDrift;

require dirname(__DIR__, 4) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 4) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! app()->environment('local', 'development', 'testing')) {
    throw new RuntimeException('Run only in a nonproduction environment.');
}

$revenue = Revenue::query()
    ->where('type', RevenueTypeEnum::ADD_AMOUNT)
    ->whereNotNull('order_id')
    ->whereDoesntHave('order', fn ($query) => $query->whereNull('store_id'))
    ->latest('id')->firstOrFail();
$order = Order::query()->findOrFail($revenue->order_id);

DB::beginTransaction();
try {
    $builder = app(SnapshotBuilder::class);
    $snapshot = $builder->prepare($order);
    $again = $builder->prepare($order);
    $assessment = app(RevenueReconciler::class)->assess($snapshot->amounts, $snapshot->currency_digits);
    if ($snapshot->getKey() !== $again->getKey()
        || $snapshot->status !== 'needs_review'
        || $snapshot->evidence['shipping_allocation'] !== 'unresolved'
        || $snapshot->evidence['policy']['mode'] !== MarketplaceHelper::getMode()
        || $snapshot->evidence['policy']['global_percentage'] !== (string) MarketplaceHelper::getSetting('fee_per_order', 0)
        || $snapshot->evidence['policy_capture_source'] !== 'manual_review'
        || ($snapshot->evidence['currency_precision_source'] === 'ec_currencies.decimals'
            && $snapshot->evidence['revenue_reconciliation']['status'] !== $assessment['status'])
        || (string) $snapshot->amounts['existing_marketplace_fee'] !== (string) $revenue->fee
        || OrderSnapshot::query()->where('order_id', $order->getKey())->count() !== 1) {
        throw new RuntimeException('Snapshot integrity check failed.');
    }
    $drift = app(SnapshotDrift::class);
    if ($drift->status($snapshot) !== 'unchanged') {
        throw new RuntimeException('Unexpected source revenue drift.');
    }
    Revenue::query()->whereKey($revenue->getKey())->update(['currency' => 'ZZZ']);
    if ($drift->status($snapshot) !== 'changed') {
        throw new RuntimeException('Source revenue drift was not detected.');
    }
    echo "Snapshot idempotency and recorded fee: PASS\n";
} finally {
    DB::rollBack();
}
