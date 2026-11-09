<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Revenue;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

$rev = Revenue::query()->where('id', 361)->first();
$order = Order::query()->where('id', $rev->order_id)->first();
$taxEngine = app(TaxEngine::class);

echo "ORDER: Subtotal: {$order->sub_total}, Shipping: {$order->shipping_amount}, Total: {$order->amount}\n";
echo "REVENUE: Sub: {$rev->sub_amount}, Total Fee Deducted: {$rev->fee}, Shipping: {$rev->shipping_cost}, PF: {$rev->platform_fee}, Comm: {$rev->commission_fee}\n";

// 1. Exclusive
setting()->set('seller_invoicing_fee_tax_type', 'exclusive');
setting()->save();
$resExc = $taxEngine->resolve($order, $rev);

echo "\n--- EXCLUSIVE MODE ---\n";
echo "Subtotal: {$resExc['subtotal']}, Tax: {$resExc['tax_amount']}, Grand Total: {$resExc['grand_total']}\n";
foreach ($resExc['lines'] as $l) {
    echo "  [{$l['name']}]: Taxable={$l['taxable_amount']}, Tax={$l['tax_amount']}, Total={$l['total_amount']}\n";
}

// 2. Inclusive
setting()->set('seller_invoicing_fee_tax_type', 'inclusive');
setting()->save();
$resInc = $taxEngine->resolve($order, $rev);

echo "\n--- INCLUSIVE MODE ---\n";
echo "Subtotal: {$resInc['subtotal']}, Tax: {$resInc['tax_amount']}, Grand Total: {$resInc['grand_total']}\n";
foreach ($resInc['lines'] as $l) {
    echo "  [{$l['name']}]: Taxable={$l['taxable_amount']}, Tax={$l['tax_amount']}, Total={$l['total_amount']}\n";
}

