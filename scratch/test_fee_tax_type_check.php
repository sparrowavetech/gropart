<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Models\Revenue;
use SparroWave\SellerInvoicing\Supports\TaxEngine;

$revenue = Revenue::query()->where('id', 362)->first();
$order = Order::query()->where('id', $revenue->order_id)->first();
$taxEngine = app(TaxEngine::class);

echo "ORDER: #{$order->id}, Subtotal: {$order->sub_total}, Amount: {$order->amount}, Shipping: {$order->shipping_amount}\n";
echo "REVENUE: Sub amount: {$revenue->sub_amount}, Fee: {$revenue->fee}, Amount: {$revenue->amount}\n";

// Test 1: Exclusive
setting()->set('seller_invoicing_fee_tax_type', 'exclusive');
setting()->save();
$resExclusive = $taxEngine->resolve($order, $revenue);

echo "\n--- EXCLUSIVE MODE ---\n";
print_r($resExclusive);

// Test 2: Inclusive
setting()->set('seller_invoicing_fee_tax_type', 'inclusive');
setting()->save();
$resInclusive = $taxEngine->resolve($order, $revenue);

echo "\n--- INCLUSIVE MODE ---\n";
print_r($resInclusive);

